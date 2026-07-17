<?php

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

// QC reviews via raw work order rows (avoids legacy morph-class resolution)
$wos = DB::table('work_orders')->where('company_uuid', $C)->whereIn('status', ['approved', 'closed', 'repaired'])->whereNull('deleted_at')->limit(6)->get();
$admin = User::where('email', 'sam@qgi.dev')->first();
foreach ($wos as $ix => $wo) {
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
            'contact_name' => 'Station Ops', 'contact_email' => strtolower($dsp) . '@example.com',
            'submitted_at' => now()->subDays(rand(0, 7)), 'triaged_at' => $status !== 'new' ? now()->subDays(rand(0, 3)) : null,
        ]
    ));
}

echo "\n=== SEED SUMMARY ===\n";
foreach ([
    'Vehicles' => 'vehicles', 'Drivers' => 'drivers', 'Devices' => 'devices',
    'Work orders' => 'work_orders', 'Maintenance' => 'maintenances', 'PM schedules' => 'maintenance_schedules',
    'Issues' => 'issues', 'Fuel reports' => 'fuel_reports',
] as $label => $table) {
    try { echo $label . ': ' . DB::table($table)->where('company_uuid', $C)->whereNull('deleted_at')->count() . "\n"; } catch (\Throwable $e) { echo $label . ": ?\n"; }
}
echo 'Return patterns: ' . ReturnPattern::where('company_uuid', $C)->count() . "\n";
echo 'Availability windows: ' . AvailabilityWindow::where('company_uuid', $C)->count() . "\n";
echo 'Campaigns: ' . Campaign::where('company_uuid', $C)->count() . ' / assignments: ' . CampaignAssignment::where('company_uuid', $C)->count() . "\n";
echo 'Claims: ' . WarrantyClaim::where('company_uuid', $C)->count() . ' | RMA: ' . RmaCase::where('company_uuid', $C)->count() . ' | QC: ' . QcReview::where('company_uuid', $C)->count() . ' | Intake: ' . IntakeRequest::where('company_uuid', $C)->count() . "\n";
echo "SEED_FINISH_DONE\n";
