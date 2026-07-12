<?php

namespace IFS\CommandIQ\Models;

use Fleetbase\Casts\Json;
use Fleetbase\Models\Model;
use Fleetbase\Traits\HasApiModelBehavior;
use Fleetbase\Traits\HasMetaAttributes;
use Fleetbase\Traits\HasPublicId;
use Fleetbase\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * A QC review of a completed work order. Only the QC role may approve
 * (close) a work order; rejection routes the work order back to the
 * originating technician and supervisor with rejection detail, and
 * rejected/rework cases roll up per technician for performance analysis.
 *
 * Spec: Section 8.8 — FR-27, FR-28, FR-29, FR-30, FR-58.
 */
class QcReview extends Model
{
    use HasUuid;
    use HasPublicId;
    use HasApiModelBehavior;
    use HasMetaAttributes;
    use LogsActivity;

    protected $table = 'commandiq_qc_reviews';

    protected $publicIdType = 'qc_review';

    protected $fillable = [
        'company_uuid',
        'work_order_uuid',
        'reviewer_uuid',
        'technician_uuid',
        'status',
        'checklist_results',
        'rejection_reason',
        'photo_evidence',
        'reviewed_at',
        'meta',
    ];

    protected $casts = [
        'checklist_results' => Json::class,
        'photo_evidence'    => Json::class,
        'reviewed_at'       => 'datetime',
        'meta'              => Json::class,
    ];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(\Fleetbase\FleetOps\Models\WorkOrder::class, 'work_order_uuid', 'uuid');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(\Fleetbase\Models\User::class, 'reviewer_uuid', 'uuid');
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(\Fleetbase\Models\User::class, 'technician_uuid', 'uuid');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['status', 'rejection_reason']);
    }
}
