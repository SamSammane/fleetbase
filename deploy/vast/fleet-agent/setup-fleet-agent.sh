#!/usr/bin/env bash
# Provision the Cursor-powered fleet agent (MCP tools + bridge) on the instance.
# Usage: CURSOR_API_KEY=... FIRECRAWL_API_KEY=... bash setup-fleet-agent.sh
set -x
: "${CURSOR_API_KEY:?Set CURSOR_API_KEY}"
: "${FIRECRAWL_API_KEY:?Set FIRECRAWL_API_KEY}"

COMPANY_UUID="967490f8-9bbb-4b26-9167-566d1f4dda28"
SQL_PASS="$(openssl rand -hex 16)"

mkdir -p /opt/fleet-agent/workspace
cp /opt/deploy/fleet-agent/mcp-server.mjs /opt/deploy/fleet-agent/bridge.mjs /opt/deploy/fleet-agent/catalog.mjs /opt/deploy/fleet-agent/product-docs.md /opt/deploy/fleet-agent/platform-docs.md /opt/deploy/fleet-agent/howto-cheatsheet.md /opt/deploy/fleet-agent/quick-answers.json /opt/fleet-agent/ 2>/dev/null || true

# ─── Read-only MySQL user with allowlisted SELECT grants ─────────
TABLES="vehicles drivers work_orders maintenances maintenance_schedules orders payloads places parts devices sensors telematics warranties contacts transactions fuel_reports issues equipment commandiq_availability_windows commandiq_return_patterns commandiq_campaigns commandiq_campaign_assignments commandiq_warranty_claims commandiq_rma_cases commandiq_qc_reviews commandiq_intake_requests ledger_invoices ledger_invoice_items"
mysql -e "DROP USER IF EXISTS 'ai_ro'@'localhost'; CREATE USER 'ai_ro'@'localhost' IDENTIFIED BY '${SQL_PASS}';"
for t in $TABLES; do
  mysql -e "GRANT SELECT ON fleetbase.\`$t\` TO 'ai_ro'@'localhost';" 2>/dev/null || true
done
mysql -e "FLUSH PRIVILEGES;"

# ─── Schema card for the model ───────────────────────────────────
python3 - <<PYEOF
import subprocess
tables = "$TABLES".split()
lines = ["# Fleet database schema (read-only)", "",
         "Conventions: primary keys are uuid strings; company-owned rows have company_uuid = '$COMPANY_UUID'.",
         "Money columns are integer cents. Timestamps are UTC datetimes. Soft deletes: filter deleted_at IS NULL.",
         "vehicles.meta is JSON with keys segment ('lm'|'mm'), dsp_code, home_station.", ""]
for t in tables:
    try:
        out = subprocess.check_output(['mysql', '-N', 'fleetbase', '-e', f'DESCRIBE `{t}`'], text=True)
        cols = [l.split('\t')[0] + ' ' + l.split('\t')[1] for l in out.strip().split('\n') if l]
        lines.append(f"## {t}")
        lines.append(', '.join(cols))
        lines.append('')
    except Exception as e:
        pass
open('/opt/fleet-agent/schema-card.md', 'w').write('\n'.join(lines))
print('schema card:', len(lines), 'lines')
PYEOF

# ─── Node deps ───────────────────────────────────────────────────
cd /opt/fleet-agent
cat > package.json <<'JSON'
{
  "name": "fleet-agent",
  "private": true,
  "type": "module",
  "dependencies": {
    "@cursor/sdk": "^1.0.23",
    "@modelcontextprotocol/sdk": "^1.12.0",
    "mysql2": "^3.11.0",
    "socketcluster-client": "^17.0.0",
    "zod": "^3.23.0"
  }
}
JSON
npm install --omit=dev 2>&1 | tail -2

# ─── Deny shell/edit tools via workspace hooks (best-effort) ─────
mkdir -p /opt/fleet-agent/workspace/.cursor
cat > /opt/fleet-agent/workspace/.cursor/hooks.json <<'JSON'
{
  "version": 1,
  "hooks": {
    "beforeShellExecution": [{ "command": "/opt/fleet-agent/deny-hook.sh" }],
    "preToolUse": []
  }
}
JSON
cat > /opt/fleet-agent/deny-hook.sh <<'SH'
#!/bin/sh
echo '{"permission":"deny","userMessage":"Shell access is disabled for this agent. Use the fleet MCP tools."}'
SH
chmod +x /opt/fleet-agent/deny-hook.sh

# ─── Unprivileged service user ───────────────────────────────────
id fleetai >/dev/null 2>&1 || useradd -r -s /usr/sbin/nologin -d /opt/fleet-agent fleetai
chown -R fleetai:fleetai /opt/fleet-agent

# ─── Supervisor program ──────────────────────────────────────────
# Secrets live in /opt/fleet-agent/.env (mode 600, service user only) —
# injected from the deployer's environment at deploy time, never in configs.
cat > /opt/fleet-agent/.env <<ENV
CURSOR_API_KEY=${CURSOR_API_KEY}
FIRECRAWL_API_KEY=${FIRECRAWL_API_KEY}
FLEET_SQL_USER=ai_ro
FLEET_SQL_PASSWORD=${SQL_PASS}
FLEET_COMPANY_UUID=${COMPANY_UUID}
AGENT_WORKSPACE=/opt/fleet-agent/workspace
CURSOR_MODEL=composer-2.5
CURSOR_MODEL_FAST=true
HOME=/opt/fleet-agent
ENV
chmod 600 /opt/fleet-agent/.env
chown fleetai:fleetai /opt/fleet-agent/.env

cat > /etc/supervisor/conf.d/fleet-agent.conf <<'SUP'
[program:fleet-agent]
command=/usr/bin/node --env-file=/opt/fleet-agent/.env /opt/fleet-agent/bridge.mjs
directory=/opt/fleet-agent
user=fleetai
autostart=true
autorestart=true
stdout_logfile=/var/log/fleet-agent.log
redirect_stderr=true
SUP

supervisorctl reread && supervisorctl update && supervisorctl restart fleet-agent 2>/dev/null || true
sleep 6
supervisorctl status fleet-agent
curl -s http://127.0.0.1:8055/health
echo

# Hourly watchdog: wedged bridge self-exits on double-empty runs; cron probe
# triggers that outside user-facing time so supervisor restarts it silently.
cat > /etc/cron.d/fleet-ai-watchdog <<'CRON'
17 * * * * root curl -s -m 180 -X POST http://127.0.0.1:8055/responses -H 'Content-Type: application/json' -d '{"input":"health check: reply with the word OK only","quiet":true}' >> /var/log/fleet-ai-watchdog.log 2>&1; echo >> /var/log/fleet-ai-watchdog.log
CRON
chmod 644 /etc/cron.d/fleet-ai-watchdog

echo FLEET_AGENT_SETUP_DONE
