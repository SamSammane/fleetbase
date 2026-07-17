<?php

use Illuminate\Support\Facades\DB;

$correct = \Fleetbase\FleetOps\Models\Vehicle::class;
$targets = [
    ['work_orders', 'target_type'],
    ['warranties', 'subject_type'],
    ['maintenance_schedules', 'subject_type'],
    ['maintenances', 'maintainable_type'],
    ['commandiq_availability_windows', 'subject_type'],
    ['commandiq_campaign_assignments', 'subject_type'],
    ['devices', 'attachable_type'],
];
foreach ($targets as $pair) {
    $t = $pair[0];
    $c = $pair[1];
    $n = DB::table($t)->where($c, 'like', '%Vehicle')->where($c, '!=', $correct)->update([$c => $correct]);
    echo $t . ' fixed: ' . $n . PHP_EOL;
}
echo 'wo distinct: ' . json_encode(DB::table('work_orders')->distinct()->pluck('target_type')) . PHP_EOL;
