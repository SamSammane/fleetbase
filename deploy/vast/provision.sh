#!/usr/bin/env bash
# Provision Vast instance for native Fleetbase stack (no docker available)
set -x
export DEBIAN_FRONTEND=noninteractive

apt-get update -y
apt-get install -y --no-install-recommends \
  mysql-server redis-server nginx supervisor \
  php8.3-fpm php8.3-cli php8.3-mysql php8.3-redis php8.3-gd php8.3-zip \
  php8.3-bcmath php8.3-intl php8.3-mbstring php8.3-xml php8.3-curl php8.3-gmp php8.3-sqlite3 \
  git curl unzip ca-certificates

# Composer
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Node 22 + pnpm
curl -fsSL https://deb.nodesource.com/setup_22.x | bash -
apt-get install -y nodejs
npm install -g pnpm ember-cli

# cloudflared
curl -fsSL -o /usr/local/bin/cloudflared https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64
chmod +x /usr/local/bin/cloudflared

php -v | head -1
node -v
mysql --version
composer --version
echo PROVISION_DONE
