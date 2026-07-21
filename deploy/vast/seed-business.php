<?php

/**
 * Business-layer seed: customers, delivery orders (completed month + active +
 * scheduled), transactions, and ledger invoices — one coherent story.
 */

use Fleetbase\FleetOps\Models\Contact;
use Fleetbase\FleetOps\Models\Driver;
use Fleetbase\FleetOps\Models\Order;
use Fleetbase\FleetOps\Models\OrderConfig;
use Fleetbase\FleetOps\Models\Payload;
use Fleetbase\FleetOps\Models\Place;
use Fleetbase\FleetOps\Models\Vehicle;
use Fleetbase\LaravelMysqlSpatial\Types\Point;
use Fleetbase\Models\Transaction;
use Illuminate\Support\Str;

$C = '967490f8-9bbb-4b26-9167-566d1f4dda28';

function step($label, $fn)
{
    try {
        $r = $fn();
        echo "OK  {$label}\n";

        return $r;
    } catch (\Throwable $e) {
        echo 'ERR ' . $label . ': ' . Str::limit($e->getMessage(), 130) . "\n";

        return null;
    }
}

$config = OrderConfig::where('company_uuid', $C)->where('name', 'Transport')->first()
    ?? OrderConfig::where('name', 'Transport')->first();
echo 'order config: ' . ($config?->public_id ?? 'NONE') . "\n";

// ─── Customer contacts + delivery destinations ──────────────────
$customerDefs = [
    ['Lakeside Retail Group', 'ops@lakesideretail.example.com'],
    ['Trinity Medical Supply', 'logistics@trinitymed.example.com'],
    ['Bluebonnet Foods', 'shipping@bluebonnetfoods.example.com'],
    ['Lone Star Electronics', 'fulfillment@lonestarelec.example.com'],
    ['Prairie Home Furnishings', 'freight@prairiehome.example.com'],
];
$customers = [];
foreach ($customerDefs as [$name, $email]) {
    $customers[] = step("customer {$name}", fn () => Contact::firstOrCreate(
        ['company_uuid' => $C, 'name' => $name],
        ['type' => 'customer', 'email' => $email, 'phone' => '+1972555' . rand(1000, 9999)]
    ));
}
$customers = array_values(array_filter($customers));

$destDefs = [
    ['Lakeside Retail DC', 32.8140, -96.9489], ['Trinity Medical Hub', 32.7876, -96.8045],
    ['Bluebonnet Cold Storage', 32.9483, -96.7299], ['Lone Star Depot', 32.6781, -96.6100],
    ['Prairie Showroom', 33.0198, -96.6989], ['Uptown Retail Point', 32.8021, -96.7695],
    ['Garland Distribution', 32.9126, -96.6389], ['Irving Logistics Park', 32.8577, -96.9700],
    ['Austin Northside DC', 30.4014, -97.7248], ['Round Rock Retail', 30.5083, -97.6789],
    ['Houston Eastgate', 29.7355, -95.2622], ['Katy Freight Stop', 29.7858, -95.8245],
];
$dests = [];
foreach ($destDefs as [$name, $lat, $lng]) {
    $dests[] = step("dest {$name}", fn () => Place::firstOrCreate(
        ['company_uuid' => $C, 'name' => $name],
        ['type' => 'delivery-destination', 'street1' => rand(100, 9900) . ' Commerce Dr', 'city' => 'Dallas', 'province' => 'TX', 'country' => 'US', 'location' => new Point($lat, $lng)]
    ));
}
$dests = array_values(array_filter($dests));

$stations = Place::where('company_uuid', $C)->where('type', 'lm-station')->get()->values();
$drivers  = Driver::where('company_uuid', $C)->whereNotNull('vehicle_uuid')->get()->values();
$vans     = Vehicle::where('company_uuid', $C)->where('type', 'vehicle')->get()->keyBy('uuid');

if ($stations->isEmpty() || $drivers->isEmpty() || !$config) {
    echo "missing prerequisites, aborting\n";

    return;
}

$mkOrder = function (array $opts) use ($C, $config, $stations, $dests, $customers, $vans) {
    $driver  = $opts['driver'];
    $pickup  = $stations[array_rand($stations->all())];
    $dropoff = $dests[array_rand($dests)];
    $cust    = $customers[array_rand($customers)];

    $payload = Payload::create([
        'company_uuid' => $C,
        'pickup_uuid'  => $pickup->uuid,
        'dropoff_uuid' => $dropoff->uuid,
        'type'         => 'transport',
        'payment_method' => 'invoice',
    ]);

    $amount = rand(80, 460) * 100;
    $txn    = null;
    if ($opts['with_txn']) {
        $txn = Transaction::create([
            'company_uuid' => $C,
            'customer_uuid' => $cust?->uuid,
            'customer_type' => $cust ? get_class($cust) : null,
            'amount'       => $amount,
            'currency'     => 'USD',
            'status'       => 'success',
            'type'         => 'transfer',
            'description'  => 'Delivery charge — ' . $dropoff->name,
        ]);
    }

    $order = new Order([
        'company_uuid'         => $C,
        'order_config_uuid'    => $config->uuid,
        'payload_uuid'         => $payload->uuid,
        'customer_uuid'        => $cust?->uuid,
        'customer_type'        => $cust ? get_class($cust) : null,
        'transaction_uuid'     => $txn?->uuid,
        'driver_assigned_uuid' => $driver?->uuid,
        'vehicle_assigned_uuid' => $driver?->vehicle_uuid,
        'status'               => $opts['status'],
        'type'                 => 'transport',
        'adhoc'                => false,
        'dispatched'           => in_array($opts['status'], ['completed', 'dispatched', 'driver_enroute']) ? 1 : 0,
        'scheduled_at'         => $opts['scheduled_at'] ?? null,
        'dispatched_at'        => $opts['dispatched_at'] ?? null,
    ]);
    $order->save();

    if (isset($opts['created_at'])) {
        $order->created_at = $opts['created_at'];
        $order->updated_at = $opts['created_at']->copy()->addHours(rand(2, 8));
        $order->save();
        if ($txn) {
            $txn->created_at = $order->updated_at;
            $txn->save();
        }
    }

    return [$order, $txn, $cust];
};

// ─── 42 completed orders across the last 30 days ────────────────
$completedOrders = [];
for ($i = 0; $i < 42; $i++) {
    $driver = $drivers[$i % $drivers->count()];
    $day    = now()->subDays(rand(0, 29))->setTime(rand(8, 17), [0, 15, 30, 45][rand(0, 3)]);
    $r = step("completed order {$i}", fn () => $mkOrder([
        'driver' => $driver, 'status' => 'completed', 'with_txn' => true,
        'created_at' => $day, 'dispatched_at' => $day->copy()->addMinutes(20),
    ]));
    if ($r) { $completedOrders[] = $r; }
}

// ─── 6 active orders right now (live map) ───────────────────────
for ($i = 0; $i < 6; $i++) {
    $driver = $drivers[($i * 3) % $drivers->count()];
    step("active order {$i}", fn () => $mkOrder([
        'driver' => $driver, 'status' => $i % 2 ? 'dispatched' : 'driver_enroute', 'with_txn' => true,
        'dispatched_at' => now()->subMinutes(rand(15, 120)),
    ]));
}

// ─── 8 scheduled orders over the next week ──────────────────────
for ($i = 0; $i < 8; $i++) {
    $driver = $drivers[($i * 2 + 1) % $drivers->count()];
    step("scheduled order {$i}", fn () => $mkOrder([
        'driver' => $driver, 'status' => 'created', 'with_txn' => false,
        'scheduled_at' => now()->addDays(1 + ($i % 7))->setTime(rand(8, 16), 0),
    ]));
}

// ─── Ledger invoices for recent completed orders ────────────────
$invoiceable = array_slice($completedOrders, 0, 10);
foreach ($invoiceable as $ix => [$order, $txn, $cust]) {
    step("invoice {$ix}", function () use ($C, $order, $txn, $cust, $ix) {
        $amount = $txn?->amount ?? rand(8000, 46000);
        $paid   = $ix < 7;

        return \Fleetbase\Ledger\Models\Invoice::firstOrCreate(
            ['company_uuid' => $C, 'order_uuid' => $order->uuid],
            [
                'customer_uuid'    => $cust?->uuid,
                'customer_type'    => $cust ? get_class($cust) : null,
                'transaction_uuid' => $txn?->uuid,
                'number'           => 'INV-2026-' . str_pad((string) (1140 + $ix), 4, '0', STR_PAD_LEFT),
                'date'             => $order->created_at?->toDateString() ?? now()->toDateString(),
                'due_date'         => now()->addDays(30 - $ix)->toDateString(),
                'subtotal'         => $amount,
                'tax'              => (int) round($amount * 0.0825),
                'total_amount'     => (int) round($amount * 1.0825),
                'amount_paid'      => $paid ? (int) round($amount * 1.0825) : 0,
                'balance'          => $paid ? 0 : (int) round($amount * 1.0825),
                'status'           => $paid ? 'paid' : 'pending',
                'currency'         => 'USD',
            ]
        );
    });
}

// ─── All vehicles online for a full map ─────────────────────────
Vehicle::where('company_uuid', $C)->update(['online' => 1]);

echo "\n=== BUSINESS SEED SUMMARY ===\n";
echo 'Orders: ' . Order::where('company_uuid', $C)->count()
    . ' (completed: ' . Order::where('company_uuid', $C)->where('status', 'completed')->count()
    . ', active: ' . Order::where('company_uuid', $C)->whereIn('status', ['dispatched', 'driver_enroute'])->count()
    . ', scheduled: ' . Order::where('company_uuid', $C)->where('status', 'created')->count() . ")\n";
echo 'Transactions: ' . Transaction::where('company_uuid', $C)->count() . ' | sum $' . number_format(Transaction::where('company_uuid', $C)->sum('amount') / 100, 2) . "\n";
try { DB::statement("UPDATE ledger_invoices SET paid_at = DATE_SUB(NOW(), INTERVAL FLOOR(5 + RAND() * 40) DAY) WHERE status = 'paid' AND paid_at IS NULL");
DB::statement("UPDATE ledger_invoices SET sent_at = DATE_SUB(paid_at, INTERVAL FLOOR(3 + RAND() * 10) DAY) WHERE status = 'paid' AND sent_at IS NULL");
echo 'Invoices: ' . \Fleetbase\Ledger\Models\Invoice::where('company_uuid', $C)->count() . "\n"; } catch (\Throwable $e) { echo "Invoices: err\n"; }
echo 'Customers: ' . Contact::where('company_uuid', $C)->where('type', 'customer')->count() . "\n";
echo "BUSINESS_SEED_DONE\n";
