<?php

namespace IFS\CommandIQ\Console\Commands;

use Illuminate\Console\Command;

/**
 * Matches open work orders (corrective, preventive, and bundled campaign
 * work) to forecasted availability windows, proposing technician
 * assignments constrained by skill/certification and shop capacity.
 *
 * Spec: Section 8.3 — FR-6, FR-8, FR-23, FR-45, FR-50, FR-51.
 */
class MatchWorkOrdersToWindows extends Command
{
    protected $signature = 'commandiq:match-work-orders {--dry-run : Propose without persisting}';

    protected $description = 'Match open work orders to forecasted availability windows';

    public function handle(): int
    {
        // TODO(Phase 2/3):
        // 1. Load open FleetOps WorkOrders + due MaintenanceSchedules.
        // 2. Join against active AvailabilityWindows for each subject asset.
        // 3. Filter by technician skill (FR-50) and shop capacity (FR-51).
        // 4. FR-45: bundle eligible campaign assignments into matched visits.
        // 5. FR-8: surface conflicts (capacity, overlap, missed windows).
        // 6. Persist proposals for scheduler confirm/override (FR-7).
        $this->info('Match pass complete.');

        return Command::SUCCESS;
    }
}
