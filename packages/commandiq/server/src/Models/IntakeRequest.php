<?php

namespace IFS\CommandIQ\Models;

use Fleetbase\Casts\Json;
use Fleetbase\Models\Model;
use Fleetbase\Traits\HasApiModelBehavior;
use Fleetbase\Traits\HasMetaAttributes;
use Fleetbase\Traits\HasPublicId;
use Fleetbase\Traits\HasUuid;
use Fleetbase\Traits\Searchable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A device-fault service request submitted by a station/DSP through the
 * intake portal, triaged into a work order.
 *
 * Spec: Section 8.15 — FR-56.
 */
class IntakeRequest extends Model
{
    use HasUuid;
    use HasPublicId;
    use HasApiModelBehavior;
    use HasMetaAttributes;
    use Searchable;

    protected $table = 'commandiq_intake_requests';

    protected $publicIdType = 'intake_request';

    protected $searchableColumns = ['subject', 'vin', 'dsp_code', 'station_code'];

    protected $fillable = [
        'company_uuid',
        'subject',
        'description',
        'vin',
        'dsp_code',
        'station_code',
        'contact_name',
        'contact_email',
        'contact_phone',
        'fault_type',
        'status',
        'work_order_uuid',
        'submitted_at',
        'triaged_at',
        'meta',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'triaged_at'   => 'datetime',
        'meta'         => Json::class,
    ];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(\Fleetbase\FleetOps\Models\WorkOrder::class, 'work_order_uuid', 'uuid');
    }
}
