<?php

use Fleetbase\FleetOps\Models\Device;
use Fleetbase\FleetOps\Models\Vehicle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

$byName = fn ($name) => Vehicle::where('company_uuid', $companyUuid)->where('name', $name)->first();

// ─── Serialized devices via Device model (polymorphic attachable) ───
$deviceDefs = [
    ['LM Van 101', 'camera', 'NF-CAM-4411', 'Netradyne', 'SN-D4411-0071'],
    ['LM Van 102', 'camera', 'NF-CAM-4412', 'Netradyne', 'SN-D4412-0114'],
    ['LM Van 103', 'gps', 'GEO-GO9-8821', 'Geotab', 'SN-GO9-88213'],
    ['MM Trailer 501', 'gps', 'GEO-GO9-9932', 'Geotab', 'SN-GO9-99327'],
];
foreach ($deviceDefs as [$vehicleName, $type, $deviceId, $mfr, $serial]) {
    $v = $byName($vehicleName);
    if (!$v) {
        continue;
    }
    seedStep("device: {$serial} on {$vehicleName}", function () use ($companyUuid, $type, $deviceId, $mfr, $serial, $v) {
        $existing = Device::where('company_uuid', $companyUuid)->where('serial_number', $serial)->first();
        if ($existing) {
            return $existing;
        }
        $d = new Device([
            'company_uuid'      => $companyUuid,
            'last_position'     => new \Fleetbase\LaravelMysqlSpatial\Types\Point($v->location->getLat(), $v->location->getLng()),
            'type'              => $type,
            'device_id'         => $deviceId,
            'provider'          => strtolower($mfr),
            'name'              => "{$mfr} " . ucfirst($type) . " — {$serial}",
            'manufacturer'      => $mfr,
            'serial_number'     => $serial,
            'installation_date' => now()->subMonths(rand(3, 14)),
            'status'            => 'installed',
            'online'            => 1,
        ]);
        $d->attachable()->associate($v);
        $d->save();

        return $d;
    });
}

// ─── Warranties via raw insert (table has no public_id column) ───
$warrantyDefs = [
    ['LM Van 101', 'Netradyne', 'NW-2024-0071', 'Parts and labor — device replacement covered in full.'],
    ['LM Van 105', 'Ford ESP', 'FE-2024-8842', 'Powertrain and electrical — 5yr/100k mi extended service plan.'],
];
foreach ($warrantyDefs as [$vehicleName, $provider, $policy, $coverage]) {
    $v = $byName($vehicleName);
    if (!$v) {
        continue;
    }
    seedStep("warranty: {$policy} on {$vehicleName}", function () use ($companyUuid, $provider, $policy, $coverage, $v) {
        $exists = DB::table('warranties')->where('policy_number', $policy)->exists();
        if ($exists) {
            return true;
        }

        return DB::table('warranties')->insert([
            'uuid'          => (string) Str::uuid(),
            'company_uuid'  => $companyUuid,
            'subject_type'  => $v->getMorphClass(),
            'subject_uuid'  => $v->uuid,
            'provider'      => $provider,
            'policy_number' => $policy,
            'type'          => 'extended',
            'start_date'    => now()->subYear(),
            'end_date'      => now()->addYears(2),
            'coverage'      => json_encode(['description' => $coverage]),
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    });
}

echo "\nCounts:\n";
echo 'Devices: ' . Device::where('company_uuid', $companyUuid)->count() . "\n";
echo 'Warranties: ' . DB::table('warranties')->where('company_uuid', $companyUuid)->count() . "\n";
