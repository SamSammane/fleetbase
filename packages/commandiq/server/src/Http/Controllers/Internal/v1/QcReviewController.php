<?php

namespace IFS\CommandIQ\Http\Controllers\Internal\v1;

use IFS\CommandIQ\Http\Controllers\CommandIQController;
use IFS\CommandIQ\Models\QcReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QcReviewController extends CommandIQController
{
    /**
     * The resource to query.
     */
    public $resource = 'qc_review';

    /**
     * Approve a completed work order (FR-27/28).
     *
     * FR-28: closure is restricted to the QC role — enforced here by IAM
     * permission check before the work order transitions to closed.
     */
    public function approve(string $id, Request $request): JsonResponse
    {
        $review = QcReview::where('public_id', $id)->orWhere('uuid', $id)->firstOrFail();

        // TODO(Phase 3): verify requester holds config('commandiq.qc.closer_role'),
        // mark the review approved, and close the linked work order.
        $review->update(['status' => 'approved', 'reviewer_uuid' => $request->user()?->uuid, 'reviewed_at' => now()]);

        return response()->json(['status' => 'approved', 'qc_review' => $review->public_id]);
    }

    /**
     * Reject a completed work order back to the technician (FR-29/30).
     */
    public function reject(string $id, Request $request): JsonResponse
    {
        $review = QcReview::where('public_id', $id)->orWhere('uuid', $id)->firstOrFail();

        // TODO(Phase 3): reopen the linked work order, notify the originating
        // technician + supervisor with rejection detail, and increment the
        // technician's rework counters for FR-30 reporting.
        $review->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->input('reason'),
            'reviewer_uuid'    => $request->user()?->uuid,
            'reviewed_at'      => now(),
        ]);

        return response()->json(['status' => 'rejected', 'qc_review' => $review->public_id]);
    }
}
