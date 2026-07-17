<?php

use Fleetbase\FleetOps\Models\Device;
use Fleetbase\FleetOps\Models\Driver;
use Fleetbase\FleetOps\Models\FuelReport;
use Fleetbase\FleetOps\Models\Issue;
use Fleetbase\FleetOps\Models\Maintenance;
use Fleetbase\FleetOps\Models\MaintenanceSchedule;
use Fleetbase\FleetOps\Models\Place;
use Fleetbase\FleetOps\Models\Vehicle;
use Fleetbase\FleetOps\Models\WorkOrder;
use Fleetbase\LaravelMysqlSpatial\Types\Point;
use Fleetbase\Models\User;
use IFS\CommandIQ\Models\AvailabilityWindow;
use IFS\CommandIQ\Models\Campaign;
use IFS\CommandIQ\Models\CampaignAssignment;
use IFS\CommandIQ\Models\IntakeRequest;
use IFS\CommandIQ\Models\QcReview;
use IFS\CommandIQ\Models\ReturnPattern;
use IFS\CommandIQ\Models\RmaCase;
use IFS\CommandIQ\Models\WarrantyClaim;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

$C = '967490f8-9bbb-4b26-9167-566d1f4dda28';

function step($label, $fn)
{
    try {
        $r = $fn();
        echo "OK  {$label}\n";

        return $r;
    } catch (\Throwable $e) {
        echo 'ERR ' . $label . ': ' . Str::limit($e->getMessage(), 140) . "\n";

        return null;
    }
}

// ─── A. More places ─────────────────────────────────────────────
$placeDefs = [
    ['DHOU2 Delivery Station', 'lm-station', '10550 Ella Blvd', 'Houston', 'TX', '77038', 29.9209, -95.4422],
    ['ELP1 Sortation Hub', 'mm-hub', '9750 Gateway Blvd W', 'El Paso', 'TX', '79925', 31.7619, -106.3308],
];
foreach ($placeDefs as [$name, $type, $street, $city, $prov, $zip, $lat, $lng]) {
    step("place {$name}", fn () => Place::firstOrCreate(
        ['company_uuid' => $C, 'name' => $name],
        ['type' => $type, 'street1' => $street, 'city' => $city, 'province' => $prov, 'postal_code' => $zip, 'country' => 'US', 'location' => new Point($lat, $lng), 'meta' => ['segment' => str_starts_with($type, 'lm') ? 'lm' : 'mm']]
    ));
}

$stations = ['DAL3 Delivery Station' => [32.6486, -96.7776], 'DAU5 Delivery Station' => [30.4383, -97.6789], 'DHOU2 Delivery Station' => [29.9209, -95.4422]];
$hubs     = ['FTW1 Fulfillment Hub' => [32.9857, -97.3308], 'SAT2 Sortation Hub' => [29.4241, -98.6394], 'ELP1 Sortation Hub' => [31.7619, -106.3308]];
$dsps     = ['RRLG', 'BLZE', 'SWFT', 'NOVA'];

// ─── B. Fleet expansion to 24 LM vans + 6 MM trailers ───────────
$makes = [['Ford', 'Transit 350', '1FTBW3XM'], ['Ram', 'ProMaster 2500', '3C6LRVDG'], ['Mercedes-Benz', 'Sprinter 2500', 'W1Y4EBHY']];
$stationNames = array_keys($stations);
for ($i = 7; $i <= 24; $i++) {
    $n       = sprintf('LM Van %d', 100 + $i);
    [$mk, $md, $vp] = $makes[$i % 3];
    $vin     = $vp . strtoupper(substr(md5('van' . $i), 0, 1)) . 'PK' . str_pad((string) (30000 + $i * 7), 6, '0', STR_PAD_LEFT);
    $vin     = substr($vin, 0, 17);
    $station = $stationNames[$i % 3];
    [$slat, $slng] = $stations[$station];
    step("vehicle {$n}", fn () => Vehicle::firstOrCreate(
        ['company_uuid' => $C, 'name' => $n],
        [
            'make' => $mk, 'model' => $md, 'year' => (string) (2021 + ($i % 4)), 'vin' => $vin,
            'plate_number' => 'TX-' . strtoupper(substr($station, 1, 2)) . (4000 + $i),
            'type' => 'vehicle', 'status' => 'active', 'online' => 1,
            'odometer' => rand(14000, 88000), 'odometer_unit' => 'mi',
            'location' => new Point($slat + rand(-80, 80) / 1000, $slng + rand(-80, 80) / 1000),
            'meta' => ['segment' => 'lm', 'dsp_code' => $dsps[$i % 4], 'home_station' => $station],
        ]
    ));
}
$hubNames = array_keys($hubs);
for ($i = 3; $i <= 6; $i++) {
    $n   = sprintf('MM Trailer %d', 500 + $i);
    $vin = substr('1UYVS253' . strtoupper(substr(md5('trl' . $i), 0, 2)) . '65321' . (30 + $i) . '00', 0, 17);
    $hub = $hubNames[$i % 3];
    [$hlat, $hlng] = $hubs[$hub];
    step("vehicle {$n}", fn () => Vehicle::firstOrCreate(
        ['company_uuid' => $C, 'name' => $n],
        [
            'make' => $i % 2 ? 'Utility' : 'Wabash', 'model' => $i % 2 ? '3000R Dry Van' : 'DuraPlate 53',
            'year' => (string) (2019 + ($i % 3)), 'vin' => $vin, 'plate_number' => 'TX-TR' . (8800 + $i),
            'type' => 'trailer', 'status' => 'active', 'online' => 1,
            'odometer' => rand(180000, 420000), 'odometer_unit' => 'mi',
            'location' => new Point($hlat + rand(-50, 50) / 1000, $hlng + rand(-50, 50) / 1000),
            'meta' => ['segment' => 'mm', 'home_station' => $hub],
        ]
    ));
}

$vans     = Vehicle::where('company_uuid', $C)->where('type', 'vehicle')->orderBy('name')->get();
$trailers = Vehicle::where('company_uuid', $C)->where('type', 'trailer')->orderBy('name')->get();
echo 'Fleet: ' . $vans->count() . ' vans, ' . $trailers->count() . " trailers\n";

// ─── C. Drivers ─────────────────────────────────────────────────
$driverNames = [['Marcus Reed', 'marcus.reed'], ['Alicia Gomez', 'alicia.gomez'], ['Trent Walker', 'trent.walker'], ['Dana Kim', 'dana.kim'], ['Jorge Fuentes', 'jorge.fuentes'], ['Sasha Bell', 'sasha.bell']];
foreach ($driverNames as $ix => [$name, $slug]) {
    step("driver {$name}", function () use ($C, $name, $slug, $ix, $vans) {
        $u = User::firstOrCreate(
            ['email' => $slug . '@ifs-demo.com'],
            ['company_uuid' => $C, 'name' => $name, 'username' => str_replace('.', '_', $slug) . '_' . rand(10, 99), 'status' => 'active', 'type' => 'user', 'phone' => '+1512555' . str_pad((string) (200 + $ix), 4, '0', STR_PAD_LEFT)]
        );
        if (method_exists($u, 'assignCompany')) {
            try { $u->assignCompany(\Fleetbase\Models\Company::where('uuid', $C)->first()); } catch (\Throwable $e) {}
        }
        $v = $vans[$ix] ?? null;

        return Driver::firstOrCreate(
            ['company_uuid' => $C, 'user_uuid' => $u->uuid],
            ['vehicle_uuid' => $v?->uuid, 'drivers_license_number' => 'TX' . rand(10000000, 99999999), 'status' => 'active', 'online' => 1, 'location' => $v?->location ?? new Point(32.7767, -96.7970), 'country' => 'US', 'city' => 'Dallas']
        );
    });
}

// ─── D. Devices on every van ────────────────────────────────────
foreach ($vans as $ix => $v) {
    foreach ([['camera', 'Netradyne', 'NF-CAM'], ['gps', 'Geotab', 'GEO-GO9']] as [$type, $mfr, $pfx]) {
        $serial = 'SN-' . strtoupper(substr(md5($v->uuid . $type), 0, 8));
        step("device {$serial} ({$v->name})", function () use ($C, $v, $type, $mfr, $pfx, $serial, $ix) {
            $existing = Device::where('company_uuid', $C)->where('serial_number', $serial)->first();
            if ($existing) { return $existing; }
            $d = new Device([
                'company_uuid' => $C,
                'last_position' => new Point($v->location->getLat(), $v->location->getLng()),
                'type' => $type, 'device_id' => $pfx . '-' . (4000 + $ix), 'provider' => strtolower($mfr),
                'name' => "{$mfr} " . ucfirst($type) . " — {$serial}", 'manufacturer' => $mfr, 'serial_number' => $serial,
                'installation_date' => now()->subMonths(rand(2, 16)), 'status' => 'installed', 'online' => 1,
            ]);
            $d->attachable()->associate($v);
            $d->save();

            return $d;
        });
    }
}

// ─── E. More work orders ────────────────────────────────────────
$woDefs = [
    ['GO9 retrofit — TELEM-24 wave 3', 'retrofit-campaign', 'scheduled', 'normal', 3],
    ['GO9 retrofit — TELEM-24 wave 3', 'retrofit-campaign', 'scheduled', 'normal', 4],
    ['Camera lens replacement — vibration fault', 'device-replacement', 'in-progress', 'high', 1],
    ['Rear door latch repair — DSP reported', 'corrective', 'submitted', 'high', 2],
    ['PM service — 5,000 mi interval', 'preventive', 'scheduled', 'normal', 1],
    ['PM service — 5,000 mi interval', 'preventive', 'closed', 'normal', -8],
    ['Tire rotation + brake inspection', 'preventive', 'approved', 'normal', -1],
    ['Conspicuity tape refresh — CONSP-26', 'retrofit-campaign', 'closed', 'low', -12],
    ['GPS unit swap — unit offline 72h', 'device-replacement', 'repaired', 'high', 0],
    ['Windshield replacement', 'corrective', 'closed', 'normal', -15],
    ['Battery replacement — no-start at station', 'corrective', 'approved', 'critical', -2],
    ['Backup camera recalibration', 'device-replacement', 'submitted', 'normal', 5],
    ['Liftgate hydraulic service', 'corrective', 'in-progress', 'high', 1],
    ['DOT annual inspection', 'preventive', 'scheduled', 'normal', 6],
];
foreach ($woDefs as $ix => [$subject, $cat, $status, $prio, $dueOff]) {
    $v = $ix < 10 ? ($vans[($ix * 2 + 6) % $vans->count()] ?? null) : ($trailers[$ix % max(1, $trailers->count())] ?? null);
    if (!$v) { continue; }
    $uniqueSubject = $subject . ' [' . $v->name . ']';
    step("wo {$uniqueSubject} [{$status}]", function () use ($C, $uniqueSubject, $cat, $status, $prio, $dueOff, $v) {
        $existing = WorkOrder::where('company_uuid', $C)->where('subject', $uniqueSubject)->first();
        if ($existing) { return $existing; }
        $wo = new WorkOrder([
            'company_uuid' => $C, 'subject' => $uniqueSubject, 'category' => $cat, 'status' => $status, 'priority' => $prio,
            'opened_at' => now()->subDays(abs($dueOff) + rand(1, 4)), 'due_at' => now()->addDays($dueOff),
            'estimated_cost' => rand(90, 1200) * 100, 'currency' => 'USD',
            'instructions' => "VIN: {$v->vin} | Station: " . ($v->meta['home_station'] ?? 'n/a') . ' | DSP: ' . ($v->meta['dsp_code'] ?? 'n/a') . '. Before/after photos required.',
        ]);
        $wo->target()->associate($v);
        $wo->save();
        if (in_array($status, ['repaired', 'approved', 'closed'])) {
            $wo->update(['closed_at' => $status === 'closed' ? now()->subDays(rand(1, 10)) : null, 'actual_cost' => rand(80, 1100) * 100]);
        }

        return $wo;
    });
}

// ─── F. Maintenance history w/ costs (MTD spend) ────────────────
for ($i = 0; $i < 10; $i++) {
    $v = $vans[($i * 3) % $vans->count()];
    step("maintenance record {$i} ({$v->name})", function () use ($C, $v, $i) {
        $key = 'Completed service #' . $i . ' — ' . $v->name;
        $existing = Maintenance::where('company_uuid', $C)->where('summary', $key)->first();
        if ($existing) { return $existing; }
        $m = new Maintenance([
            'company_uuid' => $C, 'type' => $i % 3 ? 'corrective' : 'preventive', 'status' => 'completed',
            'scheduled_at' => now()->subDays(rand(3, 28)), 'started_at' => now()->subDays(rand(2, 20)), 'completed_at' => now()->subDays(rand(0, 18)),
            'odometer' => (int) $v->odometer - rand(200, 2000),
            'summary' => $key, 'labor_cost' => rand(80, 420) * 100, 'parts_cost' => rand(40, 600) * 100,
            'total_cost' => 0, 'currency' => 'USD',
        ]);
        $m->maintainable()->associate($v);
        $m->total_cost = $m->labor_cost + $m->parts_cost;
        $m->save();

        return $m;
    });
}

// ─── G. PM schedules for all assets without one ─────────────────
foreach ($vans->concat($trailers) as $v) {
    $has = MaintenanceSchedule::where('company_uuid', $C)->where('subject_uuid', $v->uuid)->exists();
    if ($has) { continue; }
    $interval = $v->type === 'trailer' ? 25000 : 5000;
    step("pm {$v->name}", fn () => MaintenanceSchedule::create([
        'company_uuid' => $C, 'subject_type' => get_class($v), 'subject_uuid' => $v->uuid,
        'name' => "PM — every {$interval} mi", 'type' => 'preventive', 'status' => 'active',
        'interval_method' => 'distance', 'interval_distance' => $interval,
        'last_service_odometer' => max(0, (int) $v->odometer - rand(1000, 4500)),
        'next_due_odometer' => (int) $v->odometer + rand(400, 3200), 'default_priority' => 'normal',
    ]));
}

// ─── H. Issues + fuel reports ───────────────────────────────────
$issueDefs = [
    ['Grinding noise front-left on braking', 'mechanical', 'high'],
    ['Driver camera LED blinking red', 'device', 'medium'],
    ['AC intermittent — cabin overheating', 'comfort', 'low'],
    ['Check engine light on', 'mechanical', 'high'],
    ['Cargo door seal torn', 'body', 'medium'],
];
foreach ($issueDefs as $ix => [$report, $cat, $prio]) {
    $v = $vans[($ix * 5 + 2) % $vans->count()];
    step("issue: {$report}", function () use ($C, $report, $cat, $prio, $v) {
        $existing = Issue::where('company_uuid', $C)->where('report', $report)->first();
        if ($existing) { return $existing; }
        $i = new Issue([
            'company_uuid' => $C, 'report' => $report, 'category' => $cat, 'priority' => $prio,
            'type' => 'vehicle', 'status' => 'pending', 'location' => new Point($v->location->getLat(), $v->location->getLng()),
            'vehicle_uuid' => $v->uuid,
        ]);
        $i->save();

        return $i;
    });
}
for ($i = 0; $i < 6; $i++) {
    $v = $vans[($i * 4 + 1) % $vans->count()];
    step("fuel report {$i}", function () use ($C, $v, $i) {
        $key = (float) ('4' . $i . '.2');
        $existing = FuelReport::where('company_uuid', $C)->where('vehicle_uuid', $v->uuid)->first();
        if ($existing) { return $existing; }
        $f = new FuelReport([
            'company_uuid' => $C, 'vehicle_uuid' => $v->uuid, 'status' => 'confirmed',
            'volume' => $key, 'metric_unit' => 'gal', 'amount' => rand(9000, 16000), 'currency' => 'USD',
            'odometer' => (string) $v->odometer, 'location' => new Point($v->location->getLat(), $v->location->getLng()),
        ]);
        $f->save();

        return $f;
    });
}

// ─── I. CommandIQ: return patterns + availability windows ───────
$stationPlaces = Place::where('company_uuid', $C)->where('type', 'lm-station')->get()->keyBy('name');
$hubPlaces     = Place::where('company_uuid', $C)->where('type', 'mm-hub')->get()->keyBy('name');

foreach ($dsps as $dsp) {
    foreach ($stationPlaces as $sname => $p) {
        for ($wd = 1; $wd <= 5; $wd++) {
            step("pattern {$dsp}@{$sname} wd{$wd}", fn () => ReturnPattern::firstOrCreate(
                ['company_uuid' => $C, 'dsp_code' => $dsp, 'station_code' => substr($sname, 0, 5), 'weekday' => $wd],
                [
                    'place_uuid' => $p->uuid,
                    'avg_return_time' => sprintf('%02d:%02d:00', rand(17, 19), rand(0, 59)),
                    'stddev_minutes' => rand(9, 38) + 0.5, 'sample_size' => rand(18, 60),
                    'lookback_days' => 28, 'computed_at' => now(),
                ]
            ));
        }
    }
}

foreach ($vans as $v) {
    $stationName = $v->meta['home_station'] ?? 'DAL3 Delivery Station';
    $p = $stationPlaces->get($stationName);
    if (!$p) { continue; }
    for ($d = 0; $d < 4; $d++) {
        $start = now()->addDays($d)->setTime(rand(17, 19), [0, 15, 30, 45][rand(0, 3)]);
        step("window {$v->name} +{$d}d", fn () => AvailabilityWindow::firstOrCreate(
            ['company_uuid' => $C, 'subject_uuid' => $v->uuid, 'starts_at' => $start],
            [
                'subject_type' => $v->getMorphClass(), 'segment' => 'lm', 'place_uuid' => $p->uuid,
                'location_code' => substr($stationName, 0, 5),
                'ends_at' => $start->copy()->addHours(rand(10, 12)),
                'confidence' => rand(74, 96) / 100, 'source' => 'dsp-return-pattern', 'status' => 'forecast',
            ]
        ));
    }
}
foreach ($trailers as $tix => $t) {
    foreach ([1, 4] as $d) {
        $hubName = $t->meta['home_station'] ?? 'FTW1 Fulfillment Hub';
        $p = $hubPlaces->get($hubName) ?? $hubPlaces->first();
        $start = now()->addDays($d + ($tix % 2))->setTime(rand(4, 21), 0);
        step("window {$t->name} +{$d}d", fn () => AvailabilityWindow::firstOrCreate(
            ['company_uuid' => $C, 'subject_uuid' => $t->uuid, 'starts_at' => $start],
            [
                'subject_type' => $t->getMorphClass(), 'segment' => 'mm', 'place_uuid' => $p->uuid,
                'location_code' => substr($p->name, 0, 4),
                'ends_at' => $start->copy()->addHours(rand(3, 7)),
                'confidence' => rand(58, 88) / 100, 'source' => 'hub-arrival-forecast', 'status' => 'forecast',
            ]
        ));
    }
}

// ─── J. Campaigns + assignments ─────────────────────────────────
$telem = step('campaign TELEM-24', fn () => Campaign::firstOrCreate(
    ['company_uuid' => $C, 'code' => 'TELEM-24'],
    ['name' => 'Telematics Retrofit — GO9', 'type' => 'retrofit', 'status' => 'active', 'priority' => 'normal', 'work_order_category' => 'retrofit-campaign', 'starts_at' => now()->subMonths(2), 'ends_at' => now()->addMonths(1), 'bundling_enabled' => true, 'description' => 'Install Geotab GO9 units across the LM van population.']
));
$consp = step('campaign CONSP-26', fn () => Campaign::firstOrCreate(
    ['company_uuid' => $C, 'code' => 'CONSP-26'],
    ['name' => 'Conspicuity Tape Refresh', 'type' => 'remediation', 'status' => 'active', 'priority' => 'low', 'work_order_category' => 'retrofit-campaign', 'starts_at' => now()->subMonth(), 'ends_at' => now()->addMonths(2), 'bundling_enabled' => true, 'description' => 'DOT-C2 tape refresh across the MM trailer population.']
));
if ($telem) {
    foreach ($vans as $ix => $v) {
        $status = $ix < 14 ? 'completed' : ($ix < 18 ? 'scheduled' : 'pending');
        step("telem assign {$v->name}", fn () => CampaignAssignment::firstOrCreate(
            ['company_uuid' => $C, 'campaign_uuid' => $telem->uuid, 'subject_uuid' => $v->uuid],
            ['subject_type' => $v->getMorphClass(), 'vin' => $v->vin, 'status' => $status, 'scheduled_at' => $status !== 'pending' ? now()->addDays(rand(1, 9)) : null, 'completed_at' => $status === 'completed' ? now()->subDays(rand(2, 40)) : null]
        ));
    }
}
if ($consp) {
    foreach ($trailers as $ix => $t) {
        $status = $ix < 2 ? 'completed' : 'pending';
        step("consp assign {$t->name}", fn () => CampaignAssignment::firstOrCreate(
            ['company_uuid' => $C, 'campaign_uuid' => $consp->uuid, 'subject_uuid' => $t->uuid],
            ['subject_type' => $t->getMorphClass(), 'vin' => $t->vin, 'status' => $status, 'completed_at' => $status === 'completed' ? now()->subDays(rand(3, 20)) : null]
        ));
    }
}

// ─── K. Warranty claims, RMA, QC, intake ────────────────────────
$claimDefs = [
    ['NW-2024-0071', 'CLM-2026-0114', 'approved', 85000, 85000],
    ['NW-2024-0071', 'CLM-2026-0139', 'submitted', 99000, null],
    ['FE-2024-8842', 'CLM-2026-0142', 'denied', 145000, 0],
];
foreach ($claimDefs as [$policy, $claimNo, $status, $claimed, $recovered]) {
    step("claim {$claimNo}", function () use ($C, $policy, $claimNo, $status, $claimed, $recovered) {
        $w = DB::table('warranties')->where('policy_number', $policy)->first();
        $existing = WarrantyClaim::where('company_uuid', $C)->where('claim_number', $claimNo)->first();
        if ($existing) { return $existing; }

        return WarrantyClaim::create([
            'company_uuid' => $C, 'warranty_uuid' => $w?->uuid, 'claim_number' => $claimNo, 'status' => $status,
            'submitted_at' => now()->subDays(rand(4, 30)), 'resolved_at' => in_array($status, ['approved', 'denied']) ? now()->subDays(rand(0, 3)) : null,
            'claimed_amount' => $claimed, 'recovered_amount' => $recovered, 'currency' => 'USD',
            'notes' => 'Device replacement under coverage — ' . $policy,
        ]);
    });
}

$rmaDefs = [
    ['RMA-26-1001', 'awaiting-shipment', 'replace', null],
    ['RMA-26-1002', 'in-transit', 'replace', null],
    ['RMA-26-1003', 'received', 'repair', null],
    ['RMA-26-1004', 'closed', 'advance-replacement', 12500],
    ['RMA-26-1005', 'closed', 'replace', 9800],
];
$depot = $hubPlaces->first();
foreach ($rmaDefs as $ix => [$rmaNo, $status, $disp, $credit]) {
    step("rma {$rmaNo}", function () use ($C, $rmaNo, $status, $disp, $credit, $depot, $ix) {
        $existing = RmaCase::where('company_uuid', $C)->where('rma_number', $rmaNo)->first();
        if ($existing) { return $existing; }
        $device = Device::where('company_uuid', $C)->skip($ix * 3)->first();

        return RmaCase::create([
            'company_uuid' => $C, 'rma_number' => $rmaNo, 'device_serial' => $device?->serial_number ?? ('SN-' . strtoupper(Str::random(8))),
            'status' => $status, 'disposition' => $disp, 'depot_place_uuid' => $depot?->uuid,
            'shipped_at' => $status !== 'awaiting-shipment' ? now()->subDays(rand(3, 15)) : null,
            'received_at' => in_array($status, ['received', 'closed']) ? now()->subDays(rand(1, 8)) : null,
            'closed_at' => $status === 'closed' ? now()->subDays(rand(0, 4)) : null,
            'core_status' => $credit ? 'credited' : 'pending', 'core_credit_amount' => $credit, 'currency' => 'USD',
            'notes' => 'Failed unit return — ' . $disp,
        ]);
    });
}

$approvedWos = WorkOrder::where('company_uuid', $C)->whereIn('status', ['approved', 'closed', 'repaired'])->limit(6)->get();
$admin = User::where('email', 'sam@qgi.dev')->first();
foreach ($approvedWos as $ix => $wo) {
    $status = $ix < 4 ? 'approved' : 'rejected';
    step("qc review {$wo->code}", fn () => QcReview::firstOrCreate(
        ['company_uuid' => $C, 'work_order_uuid' => $wo->uuid],
        [
            'reviewer_uuid' => $admin?->uuid, 'status' => $status,
            'checklist_results' => ['before_photo' => true, 'after_photo' => $status === 'approved', 'torque_spec' => true],
            'rejection_reason' => $status === 'rejected' ? 'After photo missing — work not verifiable. Returned to technician.' : null,
            'photo_evidence' => ['before' => 'photo_' . strtolower(Str::random(6)) . '.jpg'],
            'reviewed_at' => now()->subDays(rand(0, 6)),
        ]
    ));
}

$intakeDefs = [
    ['Camera offline — LM Van 108', 'camera-fault', 'RRLG', 'DAL3', 'new'],
    ['GPS ghosting — location jumps', 'gps-fault', 'BLZE', 'DAU5', 'new'],
    ['Driver reports camera obstruction alert daily', 'camera-fault', 'SWFT', 'DAU5', 'triaged'],
    ['Telematics unit no heartbeat 5 days', 'gps-fault', 'NOVA', 'DHOU2', 'triaged'],
    ['Request: install request for new van batch', 'install-request', 'RRLG', 'DAL3', 'new'],
    ['Camera SD card error code E14', 'camera-fault', 'BLZE', 'DAL3', 'converted'],
    ['Cabin camera loose mount', 'camera-fault', 'SWFT', 'DHOU2', 'new'],
];
foreach ($intakeDefs as [$subject, $fault, $dsp, $station, $status]) {
    step("intake {$subject}", fn () => IntakeRequest::firstOrCreate(
        ['company_uuid' => $C, 'subject' => $subject],
        [
            'description' => $subject . ' — submitted via station intake portal.', 'fault_type' => $fault,
            'dsp_code' => $dsp, 'station_code' => $station, 'status' => $status,
            'contact_name' => 'Station Ops', 'contact_email' => strtolower($dsp) . '@' . strtolower($station) . '.example.com',
            'submitted_at' => now()->subDays(rand(0, 7)), 'triaged_at' => $status !== 'new' ? now()->subDays(rand(0, 3)) : null,
        ]
    ));
}

// ─── Summary ────────────────────────────────────────────────────
echo "\n=== SEED SUMMARY ===\n";
echo 'Vehicles: ' . Vehicle::where('company_uuid', $C)->count() . "\n";
echo 'Drivers: ' . Driver::where('company_uuid', $C)->count() . "\n";
echo 'Devices: ' . Device::where('company_uuid', $C)->count() . "\n";
echo 'Work orders: ' . WorkOrder::where('company_uuid', $C)->count() . "\n";
echo 'Maintenance records: ' . Maintenance::where('company_uuid', $C)->count() . "\n";
echo 'PM schedules: ' . MaintenanceSchedule::where('company_uuid', $C)->count() . "\n";
echo 'Issues: ' . Issue::where('company_uuid', $C)->count() . "\n";
echo 'Fuel reports: ' . FuelReport::where('company_uuid', $C)->count() . "\n";
echo 'Return patterns: ' . ReturnPattern::where('company_uuid', $C)->count() . "\n";
echo 'Availability windows: ' . AvailabilityWindow::where('company_uuid', $C)->count() . "\n";
echo 'Campaigns: ' . Campaign::where('company_uuid', $C)->count() . ' / assignments: ' . CampaignAssignment::where('company_uuid', $C)->count() . "\n";
echo 'Claims: ' . WarrantyClaim::where('company_uuid', $C)->count() . ' | RMA: ' . RmaCase::where('company_uuid', $C)->count() . ' | QC: ' . QcReview::where('company_uuid', $C)->count() . ' | Intake: ' . IntakeRequest::where('company_uuid', $C)->count() . "\n";
echo "MEGA_SEED_DONE\n";
