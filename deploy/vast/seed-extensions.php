<?php
// Populate the Extensions marketplace for the demo: every capability CBRE
// requested in the functional spec appears in Explore (v1 shipped, v2 roadmap),
// and the modules actually running in this deployment show as Installed.
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

$C = '967490f8-9bbb-4b26-9167-566d1f4dda28';

$catalog = [
    // ── v1 — live in this deployment ───────────────────────────────
    ['name' => 'IFS CommandIQ', 'installed' => true, 'version' => '1.0.0', 'fa_icon' => 'rocket',
     'subtitle' => 'Activation, availability forecasting & retrofit operations',
     'description' => 'The core IFS CommandIQ suite: LM/MM fleet segmentation, DSP return-pattern learning, service availability forecasting with confidence scoring, and VIN-keyed work order activation flows.',
     'tags' => ['commandiq', 'forecasting', 'activation', 'v1']],
    ['name' => 'CBRE Fleet AI', 'installed' => true, 'version' => '1.2.0', 'fa_icon' => 'brain',
     'subtitle' => 'Agentic assistant grounded in live fleet data',
     'description' => 'Conversational operations copilot with governed read-only SQL, deterministic metric catalog, product documentation retrieval, and live token streaming in a docked console panel.',
     'tags' => ['ai', 'assistant', 'analytics', 'v1']],
    ['name' => 'Realtime Fleet Tracking', 'installed' => true, 'version' => '1.0.0', 'fa_icon' => 'satellite-dish',
     'subtitle' => 'Live vehicle telemetry & moving map',
     'description' => 'SocketCluster-backed realtime channel streaming vehicle positions, driver status, and order events onto the operations map with no page refresh.',
     'tags' => ['telemetry', 'realtime', 'map', 'v1']],
    ['name' => 'Fleet Telemetry Simulator', 'installed' => true, 'version' => '0.9.0', 'fa_icon' => 'route',
     'subtitle' => 'Route-accurate GPS & sensor playback',
     'description' => 'Drives the demo fleet along real road geometry with heading, speed, and status changes — ideal for demos, training, and integration testing without physical hardware.',
     'tags' => ['simulator', 'telemetry', 'demo', 'v1']],
    ['name' => 'Ledger & Invoicing', 'installed' => true, 'version' => '1.1.0', 'fa_icon' => 'file-invoice-dollar',
     'subtitle' => 'Billing, invoices & settlement',
     'description' => 'Customer invoicing tied to completed orders: invoice lifecycle (draft, sent, paid), balances, due dates, and settlement reporting.',
     'tags' => ['billing', 'invoices', 'finance', 'v1']],
    ['name' => 'Customer Portal', 'installed' => true, 'version' => '1.0.0', 'fa_icon' => 'users',
     'subtitle' => 'Self-service order tracking for customers',
     'description' => 'Branded portal where customers place orders, follow live tracking links, review billing, and raise support requests.',
     'tags' => ['portal', 'customers', 'v1']],

    // ── v2 — requested roadmap, visible in Explore ─────────────────
    ['name' => 'Warranty & RMA Manager', 'installed' => false, 'version' => '2.0.0', 'fa_icon' => 'shield-halved',
     'subtitle' => 'OEM warranty claims & return flows (v2)',
     'description' => 'End-to-end warranty administration: claim intake against VIN-keyed install records, OEM adjudication tracking, RMA case management with advance-exchange support.',
     'tags' => ['warranty', 'rma', 'v2']],
    ['name' => 'Retrofit Campaign Manager', 'installed' => false, 'version' => '2.0.0', 'fa_icon' => 'bullhorn',
     'subtitle' => 'Fleet-wide retrofit campaigns with burn-down (v2)',
     'description' => 'Plan device retrofit campaigns across fleet populations, auto-generate work orders against forecast availability windows, and track completion burn-down per station and DSP.',
     'tags' => ['campaigns', 'retrofit', 'v2']],
    ['name' => 'Quality Control & Intake', 'installed' => false, 'version' => '2.0.0', 'fa_icon' => 'clipboard-check',
     'subtitle' => 'QC gates, inspections & evidence capture (v2)',
     'description' => 'Structured intake inspections and post-repair QC reviews with photo evidence, checklists, and a QC Reviewer role that gates work order closure.',
     'tags' => ['quality', 'inspections', 'v2']],
    ['name' => 'SLA Monitor & Alerts', 'installed' => false, 'version' => '2.0.0', 'fa_icon' => 'stopwatch',
     'subtitle' => 'Service-level tracking with breach alerts (v2)',
     'description' => 'Defines response and resolution SLAs per work order priority, tracks clock state across the lifecycle, and raises alerts before breach thresholds are hit.',
     'tags' => ['sla', 'alerts', 'v2']],
    ['name' => 'Parts Forecasting', 'installed' => false, 'version' => '2.0.0', 'fa_icon' => 'boxes-stacked',
     'subtitle' => 'Inventory reorder prediction (v2)',
     'description' => 'Predicts part consumption from campaign schedules and preventive-maintenance demand, recommending reorder points and purchase timing per warehouse.',
     'tags' => ['parts', 'inventory', 'forecasting', 'v2']],
    ['name' => 'DSP Scorecards', 'installed' => false, 'version' => '2.0.0', 'fa_icon' => 'ranking-star',
     'subtitle' => 'Per-DSP performance league tables (v2)',
     'description' => 'Ranks delivery service partners on completion rate, on-time percentage, revenue contribution, and vehicle availability compliance with weekly trend reporting.',
     'tags' => ['dsp', 'reporting', 'v2']],
];

$created = $installs = 0;
foreach ($catalog as $ext) {
    $slug = Str::slug($ext['name']);
    $existing = DB::table('registry_extensions')->where('company_uuid', $C)->where('slug', $slug)->first();
    if (!$existing) {
        $uuid = (string) Str::uuid();
        DB::table('registry_extensions')->insert([
            'uuid'             => $uuid,
            'public_id'        => 'extension_' . Str::lower(Str::random(9)),
            'company_uuid'     => $C,
            'name'             => $ext['name'],
            'slug'             => $slug,
            'subtitle'         => $ext['subtitle'],
            'description'      => $ext['description'],
            'fa_icon'          => $ext['fa_icon'],
            'version'          => $ext['version'],
            'tags'             => json_encode($ext['tags']),
            'status'           => 'published',
            'published_at'     => now()->subDays(rand(30, 200)),
            'accepted_at'      => now()->subDays(rand(200, 300)),
            'payment_required' => 0,
            'price'            => 0,
            'currency'         => 'USD',
            'self_managed'     => 1,
            'core_extension'   => 0,
            'primary_language' => 'en-us',
            'website_url'      => 'https://fleet-app.qgi.dev/docs/',
            'support_url'      => 'https://fleet-app.qgi.dev/docs/',
            'copyright'        => 'Integrated Fleet Solutions',
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
        $created++;
    } else {
        $uuid = $existing->uuid;
    }

    if ($ext['installed'] && !DB::table('registry_extension_installs')->where('company_uuid', $C)->where('extension_uuid', $uuid)->exists()) {
        DB::table('registry_extension_installs')->insert([
            'uuid' => (string) Str::uuid(), 'company_uuid' => $C, 'extension_uuid' => $uuid,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $installs++;
    }
}

Cache::forget('public-extensions-list');
echo 'extensions created: ' . $created . ' | installs: ' . $installs
    . ' | published total: ' . DB::table('registry_extensions')->where('status', 'published')->count() . PHP_EOL;
