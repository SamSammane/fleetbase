<?php

namespace IFS\CommandIQ\Http\Controllers\Internal\v1;

use IFS\CommandIQ\Http\Controllers\CommandIQController;
use IFS\CommandIQ\Models\Campaign;
use IFS\CommandIQ\Models\CampaignAssignment;
use Illuminate\Http\JsonResponse;

class CampaignController extends CommandIQController
{
    /**
     * The resource to query.
     */
    public $resource = 'campaign';

    /**
     * Campaign completion / burn-down against the assigned population (FR-44).
     */
    public function burnDown(string $id): JsonResponse
    {
        $campaign = Campaign::where('public_id', $id)->orWhere('uuid', $id)->firstOrFail();

        $counts = CampaignAssignment::where('campaign_uuid', $campaign->uuid)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $population = (int) $counts->sum();
        $completed  = (int) $counts->get('completed', 0);

        return response()->json([
            'campaign'   => $campaign->public_id,
            'population' => $population,
            'completed'  => $completed,
            'remaining'  => $population - $completed,
            'percent'    => $population > 0 ? round(($completed / $population) * 100, 1) : 0,
            'by_status'  => $counts,
        ]);
    }
}
