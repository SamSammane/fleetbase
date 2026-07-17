<?php

/**
 * Demo route simulator — moves a handful of LM vans along looping circuits
 * around Dallas so the live map shows real vehicle movement.
 * Runs forever; managed by supervisor (program:simulator).
 */

use Fleetbase\FleetOps\Models\Vehicle;
use Fleetbase\LaravelMysqlSpatial\Types\Point;

$C = '967490f8-9bbb-4b26-9167-566d1f4dda28';

// Elliptical circuits around the DAL3 area, one per simulated van
$centers = [
    [32.6486, -96.7776], // DAL3
    [32.7000, -96.8100],
    [32.6800, -96.7300],
    [32.7300, -96.7700],
    [32.6600, -96.8400],
];

$vans = Vehicle::where('company_uuid', $C)
    ->where('type', 'vehicle')
    ->orderBy('name')
    ->limit(count($centers))
    ->get();

if ($vans->isEmpty()) {
    echo "no vans to simulate\n";
    exit(0);
}

echo 'simulating ' . $vans->count() . " vans\n";
$steps = 60; // points per loop
$tick  = 0;

while (true) {
    foreach ($vans as $ix => $v) {
        [$clat, $clng] = $centers[$ix % count($centers)];
        $phase = (($tick + $ix * 12) % $steps) / $steps * 2 * M_PI;
        $lat   = $clat + 0.020 * sin($phase);
        $lng   = $clng + 0.028 * cos($phase);
        $nextPhase = ((($tick + 1) + $ix * 12) % $steps) / $steps * 2 * M_PI;
        $heading   = rad2deg(atan2(
            0.028 * cos($nextPhase) - 0.028 * cos($phase),
            0.020 * sin($nextPhase) - 0.020 * sin($phase)
        ));

        try {
            $v->update([
                'location' => new Point($lat, $lng),
                'heading'  => (string) round(fmod($heading + 360, 360)),
                'speed'    => (string) rand(28, 54),
                'online'   => 1,
            ]);
        } catch (\Throwable $e) {
            echo 'tick error: ' . $e->getMessage() . "\n";
        }
    }
    $tick++;
    if ($tick % 20 === 0) {
        echo 'tick ' . $tick . "\n";
    }
    sleep(4);
}
