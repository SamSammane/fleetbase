#!/usr/bin/env node
/**
 * Fleet MCP server (stdio) — governed data + web tools for the CBRE Fleet AI agent.
 *
 * Tools:
 *   fleet_schema     — table/column card for the queryable fleet dataset
 *   fleet_sql        — read-only SELECT against allowlisted tables (LIMIT-capped)
 *   web_search       — Firecrawl search (top results w/ snippets)
 *   web_fetch        — Firecrawl scrape of a URL to markdown
 *
 * Guardrails: dedicated read-only MySQL user (SELECT-only grants), single-statement
 * SELECT validation, automatic row cap, statement timeout, table allowlist enforced
 * both here and at the MySQL grant layer.
 */
import { McpServer } from '@modelcontextprotocol/sdk/server/mcp.js';
import { StdioServerTransport } from '@modelcontextprotocol/sdk/server/stdio.js';
import { z } from 'zod';
import mysql from 'mysql2/promise';
import fs from 'node:fs';
import { METRICS } from './catalog.mjs';

const DB = {
    host: '127.0.0.1',
    user: process.env.FLEET_SQL_USER || 'ai_ro',
    password: process.env.FLEET_SQL_PASSWORD || '',
    database: 'fleetbase',
    connectionLimit: 4,
    rowsAsArray: false,
};
const pool = mysql.createPool(DB);
const COMPANY = process.env.FLEET_COMPANY_UUID || '';
const FIRECRAWL_KEY = process.env.FIRECRAWL_API_KEY || '';

const ALLOWED_TABLES = [
    'vehicles', 'drivers', 'work_orders', 'maintenances', 'maintenance_schedules',
    'orders', 'payloads', 'places', 'parts', 'devices', 'sensors', 'telematics',
    'warranties', 'contacts', 'transactions', 'fuel_reports', 'issues', 'equipment',
    'commandiq_availability_windows', 'commandiq_return_patterns', 'commandiq_campaigns',
    'commandiq_campaign_assignments', 'commandiq_warranty_claims', 'commandiq_rma_cases',
    'commandiq_qc_reviews', 'commandiq_intake_requests', 'invoices', 'invoice_items',
];

const SCHEMA_CARD = fs.existsSync('/opt/fleet-agent/schema-card.md')
    ? fs.readFileSync('/opt/fleet-agent/schema-card.md', 'utf-8')
    : 'Schema card not generated yet — call fleet_sql with SHOW COLUMNS style SELECTs against information_schema is not permitted; ask the operator to regenerate.';

function validateSql(sql) {
    const s = sql.trim().replace(/;+\s*$/, '');
    if (!/^(select|with)\s/i.test(s)) {
        throw new Error('Only SELECT statements (optionally with CTEs) are allowed.');
    }
    if (/(insert|update|delete|drop|alter|create|replace|grant|revoke|truncate|call|set|lock)/i.test(s)) {
        throw new Error('Only read-only queries are allowed.');
    }
    if (/;/.test(s)) {
        throw new Error('Only a single statement is allowed.');
    }
    if (/\b(into\s+(outfile|dumpfile)|load_file|information_schema|mysql\.|performance_schema|sleep\s*\(|benchmark\s*\()/i.test(s)) {
        throw new Error('Query uses a forbidden construct.');
    }
    // every referenced table must be allowlisted
    const tables = [...s.matchAll(/\b(?:from|join)\s+`?([a-z0-9_]+)`?/gi)].map((m) => m[1].toLowerCase());
    for (const t of tables) {
        if (!ALLOWED_TABLES.includes(t)) {
            throw new Error(`Table '${t}' is not in the queryable allowlist.`);
        }
    }
    return /\blimit\s+\d+/i.test(s) ? s : s + ' LIMIT 200';
}

const server = new McpServer({ name: 'fleet-tools', version: '1.0.0' });


async function runCanned(sqlTemplate, params) {
    let sql = sqlTemplate.replaceAll(':company', mysql.escape(COMPANY));
    if (params.days !== undefined) sql = sql.replaceAll(':days', String(Math.max(1, Math.min(365, parseInt(params.days, 10) || 30))));
    if (params.vehicle !== undefined) sql = sql.replaceAll(':vehicle', mysql.escape('%' + params.vehicle + '%'));
    const conn = await pool.getConnection();
    try {
        await conn.query('SET SESSION MAX_EXECUTION_TIME=3000');
        const [rows] = await conn.query(sql);
        return rows;
    } finally {
        conn.release();
    }
}

server.tool(
    'fleet_insights',
    'Preferred tool for fleet questions. Runs one or more canned, deterministic metrics IN PARALLEL and returns them keyed by name. '
    + 'Metrics: ' + Object.keys(METRICS).join(', ') + '. '
    + 'Pass every metric the question needs in ONE call. params: {days} for *_window metrics, {vehicle} for vehicle_profile.',
    {
        metrics: z.array(z.string()).min(1).max(8).describe('Metric names from the catalog'),
        params: z.object({ days: z.number().optional(), vehicle: z.string().optional() }).optional(),
    },
    async ({ metrics, params = {} }) => {
        const out = {};
        await Promise.all(metrics.map(async (name) => {
            const def = METRICS[name];
            if (!def) { out[name] = { error: 'unknown metric' }; return; }
            try {
                if (def.multi) {
                    const sub = {};
                    await Promise.all(Object.entries(def.multi).map(async ([k, tmpl]) => { sub[k] = await runCanned(tmpl, params); }));
                    out[name] = sub;
                } else {
                    out[name] = await runCanned(def.sql, params);
                }
            } catch (e) {
                out[name] = { error: e.message };
            }
        }));
        const text = JSON.stringify(out, (k, v) => (typeof v === 'bigint' ? v.toString() : v));
        return { content: [{ type: 'text', text: text.length > 30000 ? text.slice(0, 30000) + '...(truncated)' : text }] };
    }
);


const PRODUCT_DOCS = fs.existsSync('/opt/fleet-agent/product-docs.md')
    ? fs.readFileSync('/opt/fleet-agent/product-docs.md', 'utf-8')
    : 'Product documentation not installed.';

server.tool(
    'product_docs',
    'The official IFS CommandIQ product documentation: signing in, console navigation, work orders and their lifecycle, PM schedules, parts, the AI assistant, roles and permissions, FAQ. Use this for ANY how-to or product-usage question. Never web-search for product questions.',
    {},
    async () => ({ content: [{ type: 'text', text: PRODUCT_DOCS }] })
);

server.tool(
    'fleet_schema',
    'Returns the queryable fleet database schema (tables, key columns, conventions). Call this before writing SQL.',
    {},
    async () => ({ content: [{ type: 'text', text: SCHEMA_CARD }] })
);

server.tool(
    'fleet_sql',
    'Run a read-only SQL SELECT against the live fleet database. Single statement only; rows are capped at 200. '
    + 'Always scope company-owned tables with company_uuid = \'' + COMPANY + '\'. '
    + 'Money columns are integer cents. Soft deletes: add deleted_at IS NULL.',
    { sql: z.string().describe('A single SELECT statement') },
    async ({ sql }) => {
        try {
            const safe = validateSql(sql);
            const conn = await pool.getConnection();
            try {
                await conn.query('SET SESSION MAX_EXECUTION_TIME=3000');
                const [rows] = await conn.query(safe);
                const out = JSON.stringify(rows, (k, v) => (typeof v === 'bigint' ? v.toString() : v));
                return { content: [{ type: 'text', text: out.length > 24000 ? out.slice(0, 24000) + '…(truncated)' : out }] };
            } finally {
                conn.release();
            }
        } catch (e) {
            return { content: [{ type: 'text', text: 'SQL_ERROR: ' + e.message }], isError: true };
        }
    }
);

server.tool(
    'web_search',
    'Search the public web. Returns top results with titles, URLs, and snippets. Use for external facts (part specs, OEM bulletins, regulations, market info).',
    { query: z.string() },
    async ({ query }) => {
        try {
            const r = await fetch('https://api.firecrawl.dev/v1/search', {
                method: 'POST',
                headers: { Authorization: `Bearer ${FIRECRAWL_KEY}`, 'Content-Type': 'application/json' },
                body: JSON.stringify({ query, limit: 5 }),
            });
            const j = await r.json();
            const items = (j.data || []).map((d) => `- ${d.title || ''}\n  ${d.url || ''}\n  ${(d.description || '').slice(0, 200)}`);
            return { content: [{ type: 'text', text: items.join('\n') || 'no results' }] };
        } catch (e) {
            return { content: [{ type: 'text', text: 'SEARCH_ERROR: ' + e.message }], isError: true };
        }
    }
);

server.tool(
    'web_fetch',
    'Fetch a specific URL and return its content as markdown.',
    { url: z.string().url() },
    async ({ url }) => {
        try {
            const r = await fetch('https://api.firecrawl.dev/v1/scrape', {
                method: 'POST',
                headers: { Authorization: `Bearer ${FIRECRAWL_KEY}`, 'Content-Type': 'application/json' },
                body: JSON.stringify({ url, formats: ['markdown'] }),
            });
            const j = await r.json();
            const md = j?.data?.markdown || 'no content';
            return { content: [{ type: 'text', text: md.slice(0, 12000) }] };
        } catch (e) {
            return { content: [{ type: 'text', text: 'FETCH_ERROR: ' + e.message }], isError: true };
        }
    }
);

const transport = new StdioServerTransport();
await server.connect(transport);
