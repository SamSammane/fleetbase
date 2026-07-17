#!/usr/bin/env bash
# Finalize: cloudflared tunnel, queue/scheduler under supervisor, boot persistence.
set -x

# ─── cloudflared config ──────────────────────────────────────────
mkdir -p /etc/cloudflared
cp /opt/deploy/13c8c786-f17c-47f1-b2b2-fdc175980e76.json /etc/cloudflared/credentials.json
cat > /etc/cloudflared/config.yml <<'YML'
tunnel: 13c8c786-f17c-47f1-b2b2-fdc175980e76
credentials-file: /etc/cloudflared/credentials.json
protocol: http2

ingress:
  - hostname: fleet-app.qgi.dev
    service: http://localhost:4200
  - hostname: fleet-api.qgi.dev
    service: http://localhost:8000
  - service: http_status:404
YML

# ─── supervisor programs ─────────────────────────────────────────
cat > /etc/supervisor/conf.d/fleet.conf <<'SUP'
[program:cloudflared]
command=/usr/local/bin/cloudflared tunnel --config /etc/cloudflared/config.yml run
autostart=true
autorestart=true
stdout_logfile=/var/log/cloudflared.log
redirect_stderr=true

[program:queue-worker]
command=/usr/bin/php artisan queue:work --sleep=3 --tries=3
directory=/opt/fleetbase/api
autostart=true
autorestart=true
stdout_logfile=/var/log/queue-worker.log
redirect_stderr=true

[program:scheduler]
command=/bin/bash -c "while true; do /usr/bin/php /opt/fleetbase/api/artisan schedule:run --no-interaction; sleep 60; done"
autostart=true
autorestart=true
stdout_logfile=/var/log/scheduler.log
redirect_stderr=true
SUP

service supervisor start || supervisord -c /etc/supervisor/supervisord.conf
sleep 3
supervisorctl reread
supervisorctl update
supervisorctl status

# ─── boot persistence (vast runs /root/onstart.sh on container start) ──
BOOT_MARKER="# fleet-stack-boot"
if ! grep -q "$BOOT_MARKER" /root/onstart.sh 2>/dev/null; then
  cat >> /root/onstart.sh <<'BOOT'
# fleet-stack-boot
service mysql start
service redis-server start
service php8.2-fpm start
service nginx start
service supervisor start
BOOT
fi

sleep 5
tail -3 /var/log/cloudflared.log
echo FINALIZE_DONE
