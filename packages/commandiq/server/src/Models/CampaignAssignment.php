<?php

namespace IFS\CommandIQ\Models;

use Fleetbase\Casts\Json;
use Fleetbase\Models\Model;
use Fleetbase\Traits\HasApiModelBehavior;
use Fleetbase\Traits\HasMetaAttributes;
use Fleetbase\Traits\HasPublicId;
use Fleetbase\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * One asset (VIN) in a campaign population, with its completion state.
 * Burn-down reporting (FR-44) aggregates over these rows.
 *
 * Spec: Section 8.13 — FR-43, FR-44, FR-45.
 */
class CampaignAssignment extends Model
{
    use HasUuid;
    use HasPublicId;
    use HasApiModelBehavior;
    use HasMetaAttributes;

    protected $table = 'commandiq_campaign_assignments';

    protected $publicIdType = 'campaign_assignment';

    protected $fillable = [
        'company_uuid',
        'campaign_uuid',
        'subject_type',
        'subject_uuid',
        'vin',
        'status',
        'work_order_uuid',
        'scheduled_at',
        'completed_at',
        'meta',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
        'meta'         => Json::class,
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'campaign_uuid', 'uuid');
    }

    public function subject(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'subject_type', 'subject_uuid');
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(\Fleetbase\FleetOps\Models\WorkOrder::class, 'work_order_uuid', 'uuid');
    }
}
