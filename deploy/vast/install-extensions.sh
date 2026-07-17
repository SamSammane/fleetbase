#!/usr/bin/env bash
# Install CommandIQ + Pallet API packages, run migrations, configure AI provider.
# Usage: OPENROUTER_KEY=... bash install-extensions.sh
set -x
cd /opt/fleetbase/api

composer config repositories.commandiq path /opt/packages/commandiq
composer config repositories.pallet path /opt/packages/pallet

composer require "ifs/commandiq-api:*" --no-interaction --ignore-platform-req=php 2>&1 | tail -3
composer require "fleetbase/pallet-api:*" --no-interaction --ignore-platform-req=php 2>&1 | tail -3

php artisan migrate --force 2>&1 | tail -6

# ─── AI provider: OpenRouter (OpenAI-compatible) + Claude Sonnet 5 ───
cat > /tmp/set-ai.php <<PHP
<?php
use Fleetbase\Models\Setting;
Setting::configure('system.ai', [
    'enabled'       => true,
    'provider'      => 'openai',
    'default_model' => 'anthropic/claude-sonnet-5',
    'providers'     => [
        'openai' => [
            'api_key'  => '${OPENROUTER_KEY}',
            'base_url' => 'https://openrouter.ai/api/v1',
        ],
        'anthropic' => ['api_key' => '', 'base_url' => 'https://api.anthropic.com/v1'],
    ],
]);
cache()->forget('system_settings.ai');
print_r(Setting::system('ai')['provider'] ?? 'none');
echo "\n";
\$mgr = app(\Fleetbase\Ai\Services\AiProviderManager::class);
print_r(\$mgr->test(Setting::system('ai')));
PHP
php artisan tinker --execute="require '/tmp/set-ai.php';"

php artisan config:cache | tail -1
php artisan route:clear && php artisan route:cache 2>&1 | tail -1
service php8.2-fpm restart
supervisorctl restart queue-worker scheduler
curl -s http://localhost:8000 | head -c 120
echo
echo EXT_INSTALL_DONE
