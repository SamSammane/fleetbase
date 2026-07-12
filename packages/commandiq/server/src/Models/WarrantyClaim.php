<?php

namespace IFS\CommandIQ\Models;

use Fleetbase\Casts\Json;
use Fleetbase\Casts\Money;
use Fleetbase\Models\Model;
use Fleetbase\Traits\HasApiModelBehavior;
use Fleetbase\Traits\HasMetaAttributes;
use Fleetbase\Traits\HasPublicId;
use Fleetbase\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * A warranty claim raised against covered work, tracked through submission,
 * adjudication, and recovery. Coverage itself lives on the FleetOps
 * `Warranty` model; this tracks the claim workflow and recovered amount.
 *
 * Spec: Section 8.10 — FR-36, FR-37.
 */
class WarrantyClaim extends Model
{
    use HasUuid;
    use HasPublicId;
    use HasApiModelBehavior;
    use HasMetaAttributes;
    use LogsActivity;

    protected $table = 'commandiq_warranty_claims';

    protected $publicIdType = 'warranty_claim';

    protected $fillable = [
        'company_uuid',
        'warranty_uuid',
        'work_order_uuid',
        'claim_number',
        'status',
        'submitted_at',
        'resolved_at',
        'claimed_amount',
        'recovered_amount',
        'currency',
        'notes',
        'meta',
    ];

    protected $casts = [
        'submitted_at'     => 'datetime',
        'resolved_at'      => 'datetime',
        'claimed_amount'   => Money::class,
        'recovered_amount' => Money::class,
        'meta'             => Json::class,
    ];

    public function warranty(): BelongsTo
    {
        return $this->belongsTo(\Fleetbase\FleetOps\Models\Warranty::class, 'warranty_uuid', 'uuid');
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(\Fleetbase\FleetOps\Models\WorkOrder::class, 'work_order_uuid', 'uuid');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['status', 'claim_number', 'recovered_amount']);
    }
}
