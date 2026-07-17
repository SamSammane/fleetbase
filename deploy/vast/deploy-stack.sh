#!/usr/bin/env bash
# Deploy Fleetbase (IFS CommandIQ / CBRE Fleet) natively on the Vast instance.
set -x
export DEBIAN_FRONTEND=noninteractive
DBPASS="${DBPASS:?Set DBPASS env var}"
APP_KEY="${APP_KEY:?Set APP_KEY env var (base64:...)}"

# ─── MySQL ───────────────────────────────────────────────────────
service mysql start || mysqld_safe --skip-grant-tables=0 &
sleep 5
mysql -e "CREATE USER IF NOT EXISTS 'fleetbase'@'127.0.0.1' IDENTIFIED BY '${DBPASS}'; CREATE USER IF NOT EXISTS 'fleetbase'@'localhost' IDENTIFIED BY '${DBPASS}'; GRANT ALL PRIVILEGES ON *.* TO 'fleetbase'@'127.0.0.1' WITH GRANT OPTION; GRANT ALL PRIVILEGES ON *.* TO 'fleetbase'@'localhost' WITH GRANT OPTION; FLUSH PRIVILEGES;"
mysql < /opt/deploy/fleetbase-dump.sql
mysql < /opt/deploy/fleetbase-extra.sql
mysql -e "SHOW DATABASES;"

# ─── Redis ───────────────────────────────────────────────────────
service redis-server start || redis-server --daemonize yes
redis-cli ping

# ─── API code ────────────────────────────────────────────────────
if [ ! -d /opt/fleetbase ]; then
  git clone --depth 1 --branch v0.7.51 https://github.com/fleetbase/fleetbase.git /opt/fleetbase
fi
cd /opt/fleetbase/api

cat > .env <<'ENV'
APP_NAME="IFS CommandIQ"
ENVIRONMENT=production
APP_ENV=production
APP_KEY=__APP_KEY__
APP_DEBUG=false
APP_URL=https://fleet-api.qgi.dev
CONSOLE_HOST=https://fleet-app.qgi.dev
LOG_CHANNEL=daily

DATABASE_URL=mysql://fleetbase:__DBPASS__@127.0.0.1:3306/fleetbase
DB_CONNECTION=mysql

QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
BROADCAST_DRIVER=log

SESSION_DRIVER=file
SESSION_DOMAIN=.qgi.dev

MAIL_MAILER=log
MAIL_FROM_ADDRESS=demo@qgi.dev
MAIL_FROM_NAME="IFS CommandIQ"

FILESYSTEM_DRIVER=public

REGISTRY_HOST=https://registry.fleetbase.io
REGISTRY_PREINSTALLED_EXTENSIONS=true
OSRM_HOST=https://router.project-osrm.org
ENV
sed -i "s|__APP_KEY__|${APP_KEY}|; s|__DBPASS__|${DBPASS}|" .env

composer install --no-dev --no-interaction --optimize-autoloader 2>&1 | tail -5

php artisan storage:link || true
mkdir -p storage/app/public/branding
cp /opt/deploy/branding/* storage/app/public/branding/
chmod -R 777 storage bootstrap/cache

php artisan config:cache
php artisan route:cache || php artisan route:clear

# ─── PHP-FPM + nginx ─────────────────────────────────────────────
service php8.3-fpm start

cat > /etc/nginx/sites-available/fleet-api <<'NGINX'
server {
    listen 8000;
    server_name _;
    root /opt/fleetbase/api/public;
    index index.php;
    client_max_body_size 100M;
    location / { try_files $uri $uri/ /index.php?$query_string; }
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_read_timeout 300;
    }
}
NGINX

cat > /etc/nginx/sites-available/fleet-console <<'NGINX'
server {
    listen 4200;
    server_name _;
    root /opt/console/dist;
    index index.html;
    location / { try_files $uri $uri/ /index.html; }
}
NGINX

ln -sf /etc/nginx/sites-available/fleet-api /etc/nginx/sites-enabled/fleet-api
ln -sf /etc/nginx/sites-available/fleet-console /etc/nginx/sites-enabled/fleet-console
rm -f /etc/nginx/sites-enabled/default
nginx -t && service nginx start && service nginx reload

curl -s http://localhost:8000 | head -c 200
echo
echo STACK_PHASE1_DONE
