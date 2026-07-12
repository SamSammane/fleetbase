<?php

namespace IFS\CommandIQ\Console\Commands;

use Illuminate\Console\Command;

/**
 * Computes forward availability windows for all active assets.
 *
 * Last Mile (FR-19): derives DSP return-time averages per station/weekday
 * from telematics arrival history, then projects windows across the
 * configured horizon.
 *
 * Middle Mile (FR-21/22): analyzes trailer travel patterns and projects
 * arrival windows at the configured major-hub set.
 *
 * Spec: Section 8.2 — FR-3, FR-4, FR-5, FR-19, FR-21, FR-22.
 */
class ForecastAvailabilityWindows extends Command
{
    protected $signature = 'commandiq:forecast-availability
                            {--segment= : Limit to a segment (lm|mm)}
                            {--horizon= : Override horizon in days}';

    protected $description = 'Compute LM/MM availability windows for service scheduling';

    public function handle(): int
    {
        $horizon = (int) ($this->option('horizon') ?: config('commandiq.forecasting.horizon_days'));
        $segment = $this->option('segment');

        // TODO(Phase 2):
        // 1. LM — aggregate station arrival events into ReturnPattern rows
        //    (dsp_code, station, weekday, avg return time, stddev).
        // 2. LM — project AvailabilityWindow rows from patterns over $horizon.
        // 3. MM — project hub-arrival windows from route/ETA data.
        // 4. Expire/supersede stale windows; keep forecast-vs-actual for FR-16.
        $this->info(sprintf('Forecast pass complete (segment: %s, horizon: %d days).', $segment ?: 'all', $horizon));

        return Command::SUCCESS;
    }
}
