#!/usr/bin/env bash
# SocketCluster v17.4.0 (same as official docker image) + tunnel ingress + API broadcast config
set -x

# ─── SocketCluster app ───────────────────────────────────────────
if [ ! -d /opt/socketcluster ]; then
  git clone --depth 1 --branch v17.3.1 https://github.com/SocketCluster/socketcluster.git /opt/socketcluster
fi
cd /opt/socketcluster/app
npm install --omit=dev 2>&1 | tail -2

cat > /etc/supervisor/conf.d/socketcluster.conf <<'SUP'
[program:socketcluster]
command=/usr/bin/node server.js
directory=/opt/socketcluster/app
environment=SOCKETCLUSTER_PORT="38000",SOCKETCLUSTER_WORKERS="4",SOCKETCLUSTER_BROKERS="4"
autostart=true
autorestart=true
stdout_logfile=/var/log/socketcluster.log
redirect_stderr=true
SUP

supervisorctl reread && supervisorctl update
sleep 5
curl -s "http://localhost:38000/health-check" && echo " SOCKET_HEALTH_OK"

# ─── Tunnel ingress: route /socketcluster/ path on fleet-app to 38000 ──
cat > /etc/cloudflared/config.yml <<'YML'
tunnel: 13c8c786-f17c-47f1-b2b2-fdc175980e76
credentials-file: /etc/cloudflared/credentials.json
protocol: http2

ingress:
  - hostname: fleet-app.qgi.dev
    path: ^/socketcluster/.*
    service: http://localhost:38000
  - hostname: fleet-app.qgi.dev
    service: http://localhost:4200
  - hostname: fleet-api.qgi.dev
    service: http://localhost:8000
  - service: http_status:404
YML
supervisorctl restart cloudflared

# ─── API broadcast config ────────────────────────────────────────
cd /opt/fleetbase/api
grep -q '^BROADCAST_DRIVER=socketcluster' .env || sed -i 's/^BROADCAST_DRIVER=.*/BROADCAST_DRIVER=socketcluster/' .env
grep -q '^SOCKETCLUSTER_HOST=' .env || cat >> .env <<'ENV'
SOCKETCLUSTER_HOST=localhost
SOCKETCLUSTER_PORT=38000
SOCKETCLUSTER_SECURE=false
ENV
php artisan config:cache | tail -1
supervisorctl restart queue-worker

echo SOCKET_SETUP_DONE
