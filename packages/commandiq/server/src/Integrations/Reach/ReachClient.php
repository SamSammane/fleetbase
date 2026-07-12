<?php

namespace IFS\CommandIQ\Integrations\Reach;

use Illuminate\Support\Facades\Http;

/**
 * Client for the sanctioned REACH API (INT-3/INT-5) — maintenance history,
 * PM activity, device-replacement cases, and mobile field app sync.
 *
 * AC-1/AC-3 apply: sanctioned access, approved authentication only.
 */
class ReachClient
{
    public function isConfigured(): bool
    {
        return !empty(config('commandiq.integrations.reach.host'))
            && !empty(config('commandiq.integrations.reach.api_key'));
    }

    protected function request()
    {
        return Http::baseUrl(config('commandiq.integrations.reach.host'))
            ->withToken(config('commandiq.integrations.reach.api_key'))
            ->acceptJson();
    }

    /**
     * Maintenance history for an asset (Section 7.1).
     */
    public function getMaintenanceHistory(string $vin, array $query = []): array
    {
        // TODO(Phase 1): confirm endpoint contract with sanctioned API docs.
        return $this->request()->get("/assets/{$vin}/maintenance-history", $query)->throw()->json();
    }

    /**
     * Device-replacement cases — the input population for failure-rate
     * forecasting (FR-35).
     */
    public function getDeviceReplacementCases(array $query = []): array
    {
        // TODO(Phase 4): confirm endpoint contract with sanctioned API docs.
        return $this->request()->get('/cases/device-replacements', $query)->throw()->json();
    }
}
