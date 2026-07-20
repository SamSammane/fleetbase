<?php
// Top up LM van availability windows (seed-mega's exact-name station lookup
// misses; this uses prefix matching). Idempotent.
use Fleetbase\FleetOps\Models\Place;
use Fleetbase\FleetOps\Models\Vehicle;
use IFS\CommandIQ\Models\AvailabilityWindow;

$C = '967490f8-9bbb-4b26-9167-566d1f4dda28';
$vans = Vehicle::where('company_uuid', $C)->where('type', 'vehicle')->get();
$made = 0;
foreach ($vans as $v) {
    $meta = is_array($v->meta) ? $v->meta : [];
    $prefix = substr($meta['home_station'] ?? 'DAL3', 0, 4);
    $p = Place::where('company_uuid', $C)->where('name', 'like', $prefix . '%')->first()
        ?? Place::where('company_uuid', $C)->where('type', 'lm-station')->first();
    if (!$p) { continue; }
    for ($d = 0; $d < 4; $d++) {
        $start = now()->addDays($d)->setTime(rand(17, 19), [0, 15, 30, 45][rand(0, 3)]);
        $w = AvailabilityWindow::firstOrCreate(
            ['company_uuid' => $C, 'subject_uuid' => $v->uuid, 'starts_at' => $start->format('Y-m-d H:i:s')],
            [
                'subject_type' => $v->getMorphClass(), 'segment' => 'lm', 'place_uuid' => $p->uuid,
                'location_code' => substr($p->name, 0, 5),
                'ends_at' => $start->copy()->addHours(rand(10, 12)),
                'confidence' => rand(74, 96) / 100, 'source' => 'dsp-return-pattern', 'status' => 'forecast',
            ]
        );
        if ($w->wasRecentlyCreated) { $made++; }
    }
}
echo 'windows created: ' . $made . ' | total: ' . AvailabilityWindow::where('company_uuid', $C)->count() . PHP_EOL;
