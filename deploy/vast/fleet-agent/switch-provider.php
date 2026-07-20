<?php

use Fleetbase\Models\Setting;

Setting::configure('system.ai', [
    'enabled'       => true,
    'provider'      => 'openai',
    'default_model' => 'composer-2.5',
    'providers'     => [
        'openai' => [
            'api_key'  => 'bridge-local',
            'base_url' => 'http://127.0.0.1:8055',
        ],
        'anthropic' => ['api_key' => '', 'base_url' => 'https://api.anthropic.com/v1'],
    ],
    // Fallback: set base_url to https://openrouter.ai/api/v1 with the OpenRouter
    // key and default_model anthropic/claude-sonnet-5 to revert to direct Claude.
]);
cache()->forget('system_settings.ai');
echo 'provider: ' . (Setting::system('ai')['provider'] ?? '?') . ' model: ' . (Setting::system('ai')['default_model'] ?? '?') . PHP_EOL;

$mgr = app(\Fleetbase\Ai\Services\AiProviderManager::class);
$t = $mgr->test(Setting::system('ai'));
echo 'test: ' . ($t['status'] ?? '?') . ' | response: ' . substr((string) ($t['response'] ?? ''), 0, 120) . PHP_EOL;
