<?php

namespace IFS\CommandIQ\Integrations\Geotab;

use Illuminate\Support\Facades\Http;

/**
 * Client for the Geotab MyGeotab API (INT-2) — location, odometer, and DTC
 * fault codes. Feeds FR-20 location validation, FR-9 predictive triggers,
 * and FR-53 DTC↔warranty/TSB/recall matching.
 *
 * Note: FleetOps ships a telematics registry (`Telematic`/`Sensor` models
 * and the fleetops:sync-telematics command); prefer registering Geotab as
 * a telematics provider there in Phase 2 rather than a parallel pipeline.
 */
class GeotabClient
{
    public function isConfigured(): bool
    {
        return !empty(config('commandiq.integrations.geotab.host'))
            && !empty(config('commandiq.integrations.geotab.database'));
    }

    /**
     * Authenticate and call a MyGeotab API method.
     */
    public function call(string $method, array $params = []): array
    {
        // TODO(Phase 2): implement MyGeotab JSON-RPC session auth + call.
        return Http::baseUrl(config('commandiq.integrations.geotab.host'))
            ->post('/apiv1', ['method' => $method, 'params' => $params])
            ->throw()
            ->json();
    }
}
