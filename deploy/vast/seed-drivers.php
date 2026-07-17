<?php
use Fleetbase\FleetOps\Models\Driver;
use Fleetbase\FleetOps\Models\Vehicle;
use Fleetbase\LaravelMysqlSpatial\Types\Point;
use Fleetbase\Models\User;

$C = '967490f8-9bbb-4b26-9167-566d1f4dda28';
$names = [
    ['Elena Vasquez','elena.vasquez'],['Derrick Cole','derrick.cole'],['Priya Nair','priya.nair'],
    ['Tom Okafor','tom.okafor'],['Rachel Lindstrom','rachel.lindstrom'],['Miguel Santos','miguel.santos'],
    ['Keisha Brown','keisha.brown'],['Andre Dupont','andre.dupont'],['Lily Chen','lily.chen'],
    ['Omar Haddad','omar.haddad'],['Grace Kim','grace.kim'],['Victor Reyes','victor.reyes'],
    ['Nina Petrova','nina.petrova'],['Sam Whitfield','sam.whitfield'],['Tara Nguyen','tara.nguyen'],
    ['Cole Bennett','cole.bennett'],['Ava Thompson','ava.thompson'],['Luis Herrera','luis.herrera'],
    ['Jade Wilson','jade.wilson'],['Marcus Oyelaran','marcus.oyelaran'],
];
$vans = Vehicle::where('company_uuid', $C)->where('type', 'vehicle')->orderBy('name')->get();
$made = 0;
foreach ($names as $ix => [$name, $slug]) {
    try {
        $u = User::firstOrCreate(
            ['email' => $slug . '@ifs-demo.com'],
            ['company_uuid' => $C, 'name' => $name, 'username' => str_replace('.', '_', $slug) . '_' . rand(10, 99), 'status' => 'active', 'type' => 'user', 'phone' => '+1214555' . str_pad((string) (300 + $ix), 4, '0', STR_PAD_LEFT)]
        );
        $v = $vans[($ix + 6) % $vans->count()] ?? null;
        $d = Driver::firstOrCreate(
            ['company_uuid' => $C, 'user_uuid' => $u->uuid],
            ['vehicle_uuid' => $v?->uuid, 'drivers_license_number' => 'TX' . rand(10000000, 99999999), 'status' => 'active', 'online' => $ix < 17 ? 1 : 0, 'location' => $v?->location ?? new Point(32.7767, -96.797), 'country' => 'US', 'city' => 'Dallas']
        );
        if ($d->wasRecentlyCreated) { $made++; }
    } catch (\Throwable $e) { echo 'ERR ' . $name . ': ' . substr($e->getMessage(), 0, 100) . PHP_EOL; }
}
echo 'new drivers: ' . $made . ' | total: ' . Driver::where('company_uuid', $C)->count() . ' | online: ' . Driver::where('company_uuid', $C)->where('online', 1)->count() . PHP_EOL;
