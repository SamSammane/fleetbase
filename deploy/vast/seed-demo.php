<?php

use Fleetbase\FleetOps\Models\MaintenanceSchedule;
use Fleetbase\FleetOps\Models\Part;
use Fleetbase\FleetOps\Models\Place;
use Fleetbase\FleetOps\Models\Vehicle;
use Fleetbase\FleetOps\Models\VehicleDevice;
use Fleetbase\FleetOps\Models\Warranty;
use Fleetbase\FleetOps\Models\WorkOrder;
use Fleetbase\LaravelMysqlSpatial\Types\Point;

$companyUuid = '967490f8-9bbb-4b26-9167-566d1f4dda28';

function seedStep($label, $fn)
{
    try {
        $result = $fn();
        echo "OK  {$label}\n";

        return $result;
    } catch (\Throwable $e) {
        echo 'ERR ' . $label . ': ' . $e->getMessage() . "\n";

        return null;
    }
}

// ─── Places: LM stations + MM hubs ──────────────────────────────
$places = [];
$placeDefs = [
    ['DAL3 Delivery Station', 'lm-station', '4835 Langdon Rd', 'Dallas', 'TX', '75241', 32.6486, -96.7776],
    ['DAU5 Delivery Station', 'lm-station', '2000 E Howard Ln', 'Austin', 'TX', '78728', 30.4383, -97.6789],
    ['FTW1 Fulfillment Hub', 'mm-hub', '15201 Heritage Pkwy', 'Fort Worth', 'TX', '76177', 32.9857, -97.3308],
    ['SAT2 Sortation Hub', 'mm-hub', '1401 S Callaghan Rd', 'San Antonio', 'TX', '78227', 29.4241, -98.6394],
];
foreach ($placeDefs as [$name, $type, $street, $city, $province, $zip, $lat, $lng]) {
    $places[$name] = seedStep("place: {$name}", fn () => Place::firstOrCreate(
        ['company_uuid' => $companyUuid, 'name' => $name],
        [
            'type'        => $type,
            'street1'     => $street,
            'city'        => $city,
            'province'    => $province,
            'postal_code' => $zip,
            'country'     => 'US',
            'location'    => new Point($lat, $lng),
            'meta'        => ['segment' => str_starts_with($type, 'lm') ? 'lm' : 'mm'],
        ]
    ));
}

// ─── Vehicles: 6 LM vans + 2 MM trailers ────────────────────────
$vehicleDefs = [
    // name, make, model, year, vin, plate, segment, dsp, station, odometer
    ['LM Van 101', 'Ford', 'Transit 350', '2023', '1FTBW3XM5PKA10141', 'TX-DL4821', 'lm', 'RRLG', 'DAL3 Delivery Station', 48210],
    ['LM Van 102', 'Ford', 'Transit 350', '2023', '1FTBW3XM7PKA10238', 'TX-DL4822', 'lm', 'RRLG', 'DAL3 Delivery Station', 51877],
    ['LM Van 103', 'Ram', 'ProMaster 2500', '2022', '3C6LRVDG8NE130455', 'TX-DL3310', 'lm', 'BLZE', 'DAL3 Delivery Station', 67042],
    ['LM Van 104', 'Ram', 'ProMaster 2500', '2022', '3C6LRVDG1NE130512', 'TX-AU2201', 'lm', 'BLZE', 'DAU5 Delivery Station', 63988],
    ['LM Van 105', 'Ford', 'Transit 350', '2024', '1FTBW3XM2RKA20077', 'TX-AU2202', 'lm', 'SWFT', 'DAU5 Delivery Station', 21455],
    ['LM Van 106', 'Ford', 'Transit 350', '2024', '1FTBW3XM4RKA20164', 'TX-AU2203', 'lm', 'SWFT', 'DAU5 Delivery Station', 19230],
    ['MM Trailer 501', 'Utility', '3000R Dry Van', '2021', '1UYVS2538M6532101', 'TX-TR8811', 'mm', null, 'FTW1 Fulfillment Hub', 284339],
    ['MM Trailer 502', 'Wabash', 'DuraPlate 53', '2020', '1JJV532D8LL219477', 'TX-TR8812', 'mm', null, 'SAT2 Sortation Hub', 341002],
];
$vehicles = [];
foreach ($vehicleDefs as [$name, $make, $model, $year, $vin, $plate, $segment, $dsp, $station, $odometer]) {
    $vehicles[$name] = seedStep("vehicle: {$name} ({$vin})", fn () => Vehicle::firstOrCreate(
        ['company_uuid' => $companyUuid, 'vin' => $vin],
        [
            'name'          => $name,
            'make'          => $make,
            'model'         => $model,
            'year'          => $year,
            'plate_number'  => $plate,
            'type'          => $segment === 'mm' ? 'trailer' : 'vehicle',
            'status'        => 'active',
            'online'        => (int) ($segment === 'lm'),
            'odometer'      => $odometer,
            'odometer_unit' => 'mi',
            'location'      => new Point(32.6486 + rand(-100, 100) / 1000, -96.7776 + rand(-100, 100) / 1000),
            'meta'          => array_filter([
                'segment'      => $segment,
                'dsp_code'     => $dsp,
                'home_station' => $station,
            ]),
        ]
    ));
}

// ─── Serialized telematics devices (FR-38) ──────────────────────
$deviceDefs = [
    ['LM Van 101', 'camera', 'NF-CAM-4411', 'Netradyne', 'SN-D4411-0071'],
    ['LM Van 102', 'camera', 'NF-CAM-4412', 'Netradyne', 'SN-D4412-0114'],
    ['LM Van 103', 'gps', 'GEO-GO9-8821', 'Geotab', 'SN-GO9-88213'],
    ['MM Trailer 501', 'gps', 'GEO-GO9-9932', 'Geotab', 'SN-GO9-99327'],
];
foreach ($deviceDefs as [$vehicleName, $type, $deviceId, $mfr, $serial]) {
    $v = $vehicles[$vehicleName] ?? null;
    if (!$v) {
        continue;
    }
    seedStep("device: {$serial} on {$vehicleName}", fn () => VehicleDevice::firstOrCreate(
        ['vehicle_uuid' => $v->uuid, 'serial_number' => $serial],
        [
            'device_type'       => $type,
            'device_id'         => $deviceId,
            'device_provider'   => strtolower($mfr),
            'device_name'       => "{$mfr} {$type}",
            'manufacturer'      => $mfr,
            'installation_date' => now()->subMonths(rand(3, 14)),
            'status'            => 'installed',
            'online'            => 1,
        ]
    ));
}

// ─── Parts inventory (FR-31/32) ─────────────────────────────────
$partDefs = [
    ['CAM-KIT-01', 'Netradyne Camera Kit', 'Netradyne', 24, 8500, 'device'],
    ['GO9-UNIT', 'Geotab GO9 Telematics Unit', 'Geotab', 41, 9900, 'device'],
    ['CONSP-TAPE', 'Conspicuity Tape Roll DOT-C2', '3M', 116, 4200, 'material'],
    ['BRK-PAD-F', 'Front Brake Pad Set — Transit 350', 'Motorcraft', 12, 8900, 'part'],
    ['MIR-ASSY-L', 'Left Mirror Assembly — ProMaster', 'Mopar', 3, 21400, 'part'],
];
foreach ($partDefs as [$sku, $name, $mfr, $qty, $cost, $type]) {
    seedStep("part: {$sku}", fn () => Part::firstOrCreate(
        ['company_uuid' => $companyUuid, 'sku' => $sku],
        [
            'name'             => $name,
            'manufacturer'     => $mfr,
            'quantity_on_hand' => $qty,
            'unit_cost'        => $cost,
            'currency'         => 'USD',
            'type'             => $type,
            'status'           => $qty < 5 ? 'reorder' : 'in-stock',
            'meta'             => ['reorder_point' => 5, 'max_quantity' => $qty * 2],
        ]
    ));
}

// ─── Warranties (FR-36) ─────────────────────────────────────────
foreach ([['LM Van 101', 'Netradyne', 'NW-2024-0071'], ['LM Van 105', 'Ford ESP', 'FE-2024-8842']] as [$vehicleName, $provider, $policy]) {
    $v = $vehicles[$vehicleName] ?? null;
    if (!$v) {
        continue;
    }
    seedStep("warranty: {$policy} on {$vehicleName}", function () use ($companyUuid, $provider, $policy, $v) {
        $w = Warranty::firstOrCreate(
            ['company_uuid' => $companyUuid, 'policy_number' => $policy],
            [
                'provider'   => $provider,
                'start_date' => now()->subYear(),
                'end_date'   => now()->addYears(2),
                'coverage'   => 'Parts and labor — device replacement covered in full.',
            ]
        );
        $w->subject()->associate($v);
        $w->save();

        return $w;
    });
}

// ─── Preventive maintenance schedules (FR-9) ────────────────────
foreach ([['LM Van 101', 5000], ['LM Van 103', 5000], ['MM Trailer 501', 25000]] as [$vehicleName, $interval]) {
    $v = $vehicles[$vehicleName] ?? null;
    if (!$v) {
        continue;
    }
    seedStep("pm schedule: {$vehicleName} every {$interval} mi", function () use ($companyUuid, $v, $interval) {
        $ms = MaintenanceSchedule::firstOrCreate(
            ['company_uuid' => $companyUuid, 'subject_uuid' => $v->uuid, 'name' => "PM — every {$interval} mi"],
            [
                'subject_type'          => get_class($v),
                'type'                  => 'preventive',
                'status'                => 'active',
                'interval_method'       => 'distance',
                'interval_distance'     => $interval,
                'last_service_odometer' => max(0, (int) $v->odometer - rand(1000, 4500)),
                'next_due_odometer'     => (int) $v->odometer + rand(500, 3000),
                'default_priority'      => 'normal',
                'instructions'          => 'Standard PM: oil/filter, brake inspection, tire rotation, DVIC review.',
            ]
        );

        return $ms;
    });
}

// ─── Work orders across the lifecycle (FR-11/12/24) ─────────────
$woDefs = [
    ['Camera replacement — driver-facing lens fault', 'device-replacement', 'submitted', 'high', 'LM Van 102', 1],
    ['Telematics retrofit — GO9 install (Campaign TELEM-24)', 'retrofit-campaign', 'scheduled', 'normal', 'LM Van 104', 2],
    ['Brake pads front — grinding reported by DSP', 'corrective', 'in-progress', 'critical', 'LM Van 103', 0],
    ['Conspicuity tape refresh (Campaign CONSP-26)', 'retrofit-campaign', 'repaired', 'low', 'MM Trailer 501', -1],
    ['PM service — 5,000 mi interval', 'preventive', 'approved', 'normal', 'LM Van 101', -2],
    ['Mirror assembly replacement — left side', 'corrective', 'closed', 'normal', 'LM Van 105', -5],
];
foreach ($woDefs as [$subject, $category, $status, $priority, $vehicleName, $dueOffsetDays]) {
    $v = $vehicles[$vehicleName] ?? null;
    if (!$v) {
        continue;
    }
    seedStep("work order: {$subject} [{$status}]", function () use ($companyUuid, $subject, $category, $status, $priority, $v, $dueOffsetDays) {
        $existing = WorkOrder::where('company_uuid', $companyUuid)->where('subject', $subject)->first();
        if ($existing) {
            return $existing;
        }
        $wo = new WorkOrder([
            'company_uuid'   => $companyUuid,
            'subject'        => $subject,
            'category'       => $category,
            'status'         => $status,
            'priority'       => $priority,
            'opened_at'      => now()->subDays(abs($dueOffsetDays) + 2),
            'due_at'         => now()->addDays($dueOffsetDays),
            'estimated_cost' => rand(120, 900) * 100,
            'currency'       => 'USD',
            'instructions'   => "VIN: {$v->vin} | Station: " . ($v->meta['home_station'] ?? 'n/a') . ' | DSP: ' . ($v->meta['dsp_code'] ?? 'n/a') . '. Before/after photos required.',
        ]);
        $wo->target()->associate($v);
        $wo->save();
        if (in_array($status, ['repaired', 'approved', 'closed'])) {
            $wo->update(['closed_at' => $status === 'closed' ? now()->subDay() : null, 'actual_cost' => rand(100, 850) * 100]);
        }

        return $wo;
    });
}

echo "\nSeed complete.\n";
echo 'Places: ' . Place::where('company_uuid', $companyUuid)->count() . "\n";
echo 'Vehicles: ' . Vehicle::where('company_uuid', $companyUuid)->count() . "\n";
echo 'Devices: ' . VehicleDevice::count() . "\n";
echo 'Parts: ' . Part::where('company_uuid', $companyUuid)->count() . "\n";
echo 'Warranties: ' . Warranty::where('company_uuid', $companyUuid)->count() . "\n";
echo 'PM Schedules: ' . MaintenanceSchedule::where('company_uuid', $companyUuid)->count() . "\n";
echo 'Work Orders: ' . WorkOrder::where('company_uuid', $companyUuid)->count() . "\n";
