<?php
use Fleetbase\FleetOps\Models\Order;
use Fleetbase\Models\Transaction;
use Illuminate\Support\Facades\DB;

$C = '967490f8-9bbb-4b26-9167-566d1f4dda28';

// Backdated orders with transactions -> completed
$completed = Order::where('company_uuid', $C)->whereNotNull('transaction_uuid')
    ->whereDate('created_at', '<', now()->toDateString())->get();
foreach ($completed as $o) { $o->status = 'completed'; $o->save(); }

// Today's orders with a dispatch time -> active
$active = Order::where('company_uuid', $C)->whereNotNull('dispatched_at')
    ->whereDate('created_at', '>=', now()->toDateString())->whereNotNull('transaction_uuid')->get();
foreach ($active as $ix => $o) { $o->status = $ix % 2 ? 'dispatched' : 'driver_enroute'; $o->save(); }

// Orphaned transactions from the aborted first run
$used = Order::where('company_uuid', $C)->whereNotNull('transaction_uuid')->pluck('transaction_uuid');
$orphans = Transaction::where('company_uuid', $C)->whereNotIn('uuid', $used)->delete();

echo 'completed: ' . Order::where('company_uuid', $C)->where('status', 'completed')->count() . PHP_EOL;
echo 'active: ' . Order::where('company_uuid', $C)->whereIn('status', ['dispatched', 'driver_enroute'])->count() . PHP_EOL;
echo 'scheduled(created): ' . Order::where('company_uuid', $C)->where('status', 'created')->count() . PHP_EOL;
echo 'orphan txns removed: ' . $orphans . PHP_EOL;
echo 'txn sum: $' . number_format(Transaction::where('company_uuid', $C)->sum('amount') / 100, 2) . PHP_EOL;
