#!/usr/bin/env node
/**
 * Cursor agent bridge — OpenAI Responses-compatible shim in front of @cursor/sdk.
 *
 * The platform's AI provider POSTs {model, input, max_output_tokens} to /responses
 * and reads {output_text}. This bridge runs each request through a Cursor local
 * agent (Composer 2.5 fast) equipped with the fleet MCP tools (governed SQL,
 * schema, web search/fetch), and returns the final assistant text.
 *
 * The agent workspace is an empty directory; the service runs as an unprivileged
 * user. Tool power comes from MCP, not the filesystem.
 */
import http from 'node:http';
import { Agent } from '@cursor/sdk';

const PORT = Number(process.env.BRIDGE_PORT || 8055);
const WORKSPACE = process.env.AGENT_WORKSPACE || '/opt/fleet-agent/workspace';
const MODEL_ID = process.env.CURSOR_MODEL || 'composer-2.5';
const FAST = (process.env.CURSOR_MODEL_FAST || 'true') === 'true';

const MCP_SERVERS = {
    fleet: {
        type: 'stdio',
        command: 'node',
        args: ['/opt/fleet-agent/mcp-server.mjs'],
        cwd: '/opt/fleet-agent',
        env: {
            FLEET_SQL_USER: process.env.FLEET_SQL_USER || 'ai_ro',
            FLEET_SQL_PASSWORD: process.env.FLEET_SQL_PASSWORD || '',
            FLEET_COMPANY_UUID: process.env.FLEET_COMPANY_UUID || '',
            FIRECRAWL_API_KEY: process.env.FIRECRAWL_API_KEY || '',
        },
    },
};

import fs from 'node:fs';
import { catalogPromptBlock } from './catalog.mjs';
const CHEATSHEET = (() => {
    try { return fs.readFileSync('/opt/fleet-agent/howto-cheatsheet.md', 'utf-8'); } catch { return ''; }
})();

const SCHEMA_CARD = (() => {
    try { return fs.readFileSync('/opt/fleet-agent/schema-card.md', 'utf-8'); } catch { return ''; }
})();

const TOOL_POLICY = [
    'Tool policy: PREFER fleet_insights — one call with ALL needed metrics; it runs them in parallel with canonical definitions. Use fleet_sql only for questions the catalog does not cover; use web_search/web_fetch for external facts.',
    'The full database schema is provided below — write SQL directly from it; only call fleet_schema if something seems missing.',
    'For how-to questions: answer DIRECTLY from the cheatsheet below (no tool call) when it covers the ask; quick_answers for indexed one-liners; product_docs only for depth. Never web_search product questions.',
    'Never use shell, file editing, or file writing tools.',
    'Prefer one well-formed SQL query over many; aggregate in SQL. Answer immediately after the data returns.',
].join(' ') + '\n\n' + catalogPromptBlock() + String.fromCharCode(10, 10) + CHEATSHEET + String.fromCharCode(10, 10) + SCHEMA_CARD;

function extractText(event, acc) {
    // Defensive extraction across SDKMessage shapes: collect assistant text deltas
    // and full messages; last complete text wins.
    try {
        if (!event || typeof event !== 'object') return;
        const t = event.type || '';
        if (t === 'assistant' && event.message?.content) {
            // assistant events stream text fragments — concatenate them
            const text = event.message.content
                .filter((c) => c.type === 'text' || c.type === 'output_text')
                .map((c) => c.text)
                .join('');
            if (text) acc.delta += text;
        }
    } catch { /* ignore malformed events */ }
}

const POOL_SIZE = Number(process.env.AGENT_POOL_SIZE || 2);
const warmPool = [];

function makeAgent() {
    return Agent.create({
        apiKey: process.env.CURSOR_API_KEY,
        model: { id: MODEL_ID, params: FAST ? [{ id: 'fast', value: 'true' }] : undefined },
        local: { cwd: WORKSPACE },
        mcpServers: MCP_SERVERS,
    });
}

function replenishPool() {
    while (warmPool.length < POOL_SIZE) {
        const slot = makeAgent()
            .then((a) => { const i = warmPool.indexOf(slot); if (i >= 0) warmPool[i] = a; return a; })
            .catch((e) => { const i = warmPool.indexOf(slot); if (i >= 0) warmPool.splice(i, 1); console.error('prewarm failed:', e.message); });
        warmPool.push(slot);
    }
}

async function acquireAgent() {
    const candidate = warmPool.shift();
    replenishPool();
    if (candidate) {
        try { return await candidate; } catch { /* fall through */ }
    }
    return makeAgent();
}

async function runAgent(input, maxTokens) {
    const agent = await acquireAgent();
    try {
        const run = await agent.send(TOOL_POLICY + '\n\n' + input);
        const acc = { full: '', delta: '' };
        let usage = {};
        for await (const event of run.stream()) {
            extractText(event, acc);
            if (event?.usage) usage = event.usage;
        }
        const text = acc.full || acc.delta || '';
        return { text, usage };
    } finally {
        try { await agent.dispose?.(); } catch { /* best effort */ }
    }
}

const serverHttp = http.createServer(async (req, res) => {
    if (req.method === 'GET' && req.url === '/health') {
        res.writeHead(200, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({ ok: true, model: MODEL_ID, fast: FAST }));
        return;
    }
    if (req.method !== 'POST' || !req.url.startsWith('/responses')) {
        res.writeHead(404); res.end('{}'); return;
    }
    let body = '';
    req.on('data', (c) => { body += c; if (body.length > 2_000_000) req.destroy(); });
    req.on('end', async () => {
        try {
            const payload = JSON.parse(body || '{}');
            const input = [payload.instructions, typeof payload.input === 'string' ? payload.input : JSON.stringify(payload.input)]
                .filter(Boolean).join('\n\n');
            const started = Date.now();
            const { text, usage } = await runAgent(input, payload.max_output_tokens);
            const out = {
                id: 'bridge-' + started,
                object: 'response',
                model: MODEL_ID,
                status: 'completed',
                output_text: text,
                output: [{ type: 'message', role: 'assistant', content: [{ type: 'output_text', text }] }],
                usage,
                bridge_ms: Date.now() - started,
            };
            res.writeHead(200, { 'Content-Type': 'application/json' });
            res.end(JSON.stringify(out));
        } catch (e) {
            res.writeHead(500, { 'Content-Type': 'application/json' });
            res.end(JSON.stringify({ error: { message: String(e && e.message || e) } }));
        }
    });
});

replenishPool();
serverHttp.listen(PORT, '127.0.0.1', () => console.log(`fleet-agent bridge on 127.0.0.1:${PORT} model=${MODEL_ID} fast=${FAST} pool=${POOL_SIZE}`));
