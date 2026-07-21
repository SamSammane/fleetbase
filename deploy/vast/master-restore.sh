#!/usr/bin/env bash
# Full demo restore on a fresh instance. Assets expected in /opt/deploy.
# Usage: OPENROUTER_KEY=.. CURSOR_API_KEY=.. FIRECRAWL_API_KEY=.. DBPASS=.. APP_KEY=.. bash master-restore.sh
set -x
: "${OPENROUTER_KEY:?}" ; : "${CURSOR_API_KEY:?}" ; : "${FIRECRAWL_API_KEY:?}" ; : "${DBPASS:?}" ; : "${APP_KEY:?}"
cd /opt/deploy

phase() { echo "===== PHASE: $1 ====="; }

phase provision;      bash provision.sh
phase deploy-stack;   DBPASS="$DBPASS" APP_KEY="$APP_KEY" bash deploy-stack.sh
phase commandiq;      mkdir -p /opt/packages && tar -xzf commandiq.tgz -C /tmp/ && rm -rf /opt/packages/commandiq && cp -r /tmp/packages/commandiq /opt/packages/
phase extensions;     OPENROUTER_KEY="$OPENROUTER_KEY" bash install-extensions.sh
phase seeds
cd /opt/fleetbase/api
for s in fix-morphs seed-mega seed-finish fix-windows seed-drivers seed-business fix-orders seed-extensions fix-morphs; do
  php artisan tinker --execute="require '/opt/deploy/${s}.php';" 2>&1 | tail -3
done
cd /opt/deploy
phase vendor-patches; bash patch-vendor.sh
phase docs;           mkdir -p /opt/docs && cp docs-index.html /opt/docs/index.html
phase console;        bash build-console.sh
phase finalize;       bash finalize.sh
phase socket;         bash setup-socket.sh
phase simulator
cp simulator.php /opt/fleetbase/simulator.php
cat > /etc/supervisor/conf.d/simulator.conf <<'SUP'
[program:simulator]
user=www-data
command=/usr/bin/php artisan tinker --execute="require '/opt/fleetbase/simulator.php';"
directory=/opt/fleetbase/api
autostart=true
autorestart=true
stdout_logfile=/var/log/simulator.log
redirect_stderr=true
SUP
supervisorctl reread && supervisorctl update
phase perms;          chmod -R 777 /opt/fleetbase/api/storage && chown -R www-data:www-data /opt/fleetbase/api/storage /opt/fleetbase/api/bootstrap/cache
phase fleet-agent;    CURSOR_API_KEY="$CURSOR_API_KEY" FIRECRAWL_API_KEY="$FIRECRAWL_API_KEY" bash fleet-agent/setup-fleet-agent.sh
bash fleet-agent/gen-schema-card.sh
phase ai-provider
cd /opt/fleetbase/api && php artisan tinker --execute="require '/opt/deploy/fleet-agent/switch-provider.php';" 2>&1 | tail -2
phase verify
supervisorctl status | awk '{print $1, $2}'
curl -s -o /dev/null -w "api: %{http_code}\n" http://localhost:8000
curl -s -o /dev/null -w "console: %{http_code}\n" http://localhost:4200
curl -s http://127.0.0.1:8055/health; echo
echo MASTER_RESTORE_DONE
