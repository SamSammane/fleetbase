<?php

namespace IFS\CommandIQ\Integrations\RelayGarage;

use Illuminate\Support\Facades\Http;

/**
 * Client for the sanctioned Relay Garage API (INT-1/INT-3).
 *
 * AC-1: sanctioned channels only. AC-3: API-key auth from config; no user
 * portal credentials are ever stored or replayed.
 */
class RelayGarageClient
{
    public function isConfigured(): bool
    {
        return !empty(config('commandiq.integrations.relay_garage.host'))
            && !empty(config('commandiq.integrations.relay_garage.api_key'));
    }

    protected function request()
    {
        return Http::baseUrl(config('commandiq.integrations.relay_garage.host'))
            ->withToken(config('commandiq.integrations.relay_garage.api_key'))
            ->acceptJson();
    }

    /**
     * Vehicle/trailer registry (FR-1).
     */
    public function getVehicles(array $query = []): array
    {
        // TODO(Phase 1): confirm endpoint contract with sanctioned API docs.
        return $this->request()->get('/vehicles', $query)->throw()->json();
    }

    /**
     * Route / upcoming stop forecasts (FR-3).
     */
    public function getRouteForecasts(array $query = []): array
    {
        // TODO(Phase 2): confirm endpoint contract with sanctioned API docs.
        return $this->request()->get('/routes/forecast', $query)->throw()->json();
    }

    /**
     * Push a maintenance status update back to Relay Garage (FR-2).
     */
    public function updateMaintenanceStatus(string $vin, string $status, array $payload = []): array
    {
        // TODO(Phase 1): confirm endpoint contract with sanctioned API docs.
        return $this->request()->post("/vehicles/{$vin}/maintenance-status", array_merge(['status' => $status], $payload))->throw()->json();
    }
}
