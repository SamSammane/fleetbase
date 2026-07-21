<?php

/**
 * AI capability regression test — run before every demo:
 *   php artisan tinker --execute="require '/opt/deploy/test-ai-caps.php';"
 *
 * Asserts that natural demo phrasings activate the right capabilities and
 * return non-trivial data. Exits with FAIL lines when expectations break.
 */

$C = '967490f8-9bbb-4b26-9167-566d1f4dda28';
$admin = \Fleetbase\Models\User::where('email', 'sam@qgi.dev')->first();
auth()->login($admin);
session(['company' => $C, 'user' => $admin->uuid]);

$cases = [
    [
        'prompt'  => 'Give me a full fleet status summary — vehicles, drivers, and devices.',
        'expect'  => ['AssetStatusCapability' => 100],
    ],
    [
        'prompt'  => 'Which work orders are critical or high priority right now, and which vehicles are they on?',
        'expect'  => ['SearchResourcesCapability' => 800],
    ],
    [
        'prompt'  => 'Tell me everything about LM Van 103 — status, open work orders, and installed devices.',
        'expect'  => ['SearchResourcesCapability' => 1500],
    ],
    [
        'prompt'  => 'How did we do this month — how many deliveries completed and what revenue?',
        'expect'  => ['OrderInsightsCapability' => 100],
    ],
];

$capFiles = glob('/opt/fleetbase/api/vendor/fleetbase/fleetops-api/server/src/Support/Ai/Capabilities/*Capability.php');
$fails = 0;
foreach ($cases as $case) {
    $task = new \Fleetbase\Ai\Models\AiTask(['prompt' => $case['prompt']]);
    echo '== ' . substr($case['prompt'], 0, 60) . PHP_EOL;
    foreach ($case['expect'] as $capName => $minBytes) {
        $cls = 'Fleetbase\\FleetOps\\Support\\Ai\\Capabilities\\' . $capName;
        try {
            $cap = new $cls();
            if (!$cap->shouldResolve($task)) {
                echo "   FAIL {$capName}: did not activate (matchesPrompt false)" . PHP_EOL;
                $fails++;
                continue;
            }
            $out  = $cap->resolve($task);
            $size = strlen(json_encode($out));
            if ($size < $minBytes) {
                echo "   FAIL {$capName}: thin result ({$size}b < {$minBytes}b)" . PHP_EOL;
                $fails++;
            } else {
                echo "   PASS {$capName}: {$size}b" . PHP_EOL;
            }
        } catch (\Throwable $e) {
            echo "   FAIL {$capName}: threw " . substr($e->getMessage(), 0, 120) . PHP_EOL;
            $fails++;
        }
    }
}

// Data sanity floor — the demo dataset must exist
$checks = [
    'vehicles >= 30'      => \Fleetbase\FleetOps\Models\Vehicle::where('company_uuid', $C)->count() >= 30,
    'drivers >= 20'       => \Fleetbase\FleetOps\Models\Driver::where('company_uuid', $C)->count() >= 20,
    'work orders >= 15'   => \Fleetbase\FleetOps\Models\WorkOrder::where('company_uuid', $C)->count() >= 15,
    'completed orders>30' => \Fleetbase\FleetOps\Models\Order::where('company_uuid', $C)->where('status', 'completed')->count() >= 30,
    'active orders >= 3'  => \Fleetbase\FleetOps\Models\Order::where('company_uuid', $C)->whereIn('status', ['dispatched', 'driver_enroute'])->count() >= 3,
    'txn revenue > 0'     => \Fleetbase\Models\Transaction::where('company_uuid', $C)->sum('amount') > 0,
    'ai enabled'          => (bool) (\Fleetbase\Models\Setting::system('ai')['enabled'] ?? false),
    'paid invoices dated' => \Illuminate\Support\Facades\DB::table('ledger_invoices')->where('company_uuid', $C)->where('status', 'paid')->whereNotNull('paid_at')->count() >= 4,
];
foreach ($checks as $label => $ok) {
    echo($ok ? 'PASS ' : 'FAIL ') . $label . PHP_EOL;
    if (!$ok) { $fails++; }
}

echo $fails === 0 ? "ALL_CHECKS_PASSED\n" : "CHECKS_FAILED: {$fails}\n";
