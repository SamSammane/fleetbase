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
 * A returned (failed) device moving through the RMA workflow: repair,
 * replace, or advance-replacement disposition, with core credit tracking
 * across depot/return locations.
 *
 * Spec: Section 8.12 — FR-41, FR-42.
 */
class RmaCase extends Model
{
    use HasUuid;
    use HasPublicId;
    use HasApiModelBehavior;
    use HasMetaAttributes;
    use LogsActivity;

    protected $table = 'commandiq_rma_cases';

    protected $publicIdType = 'rma_case';

    protected $fillable = [
        'company_uuid',
        'rma_number',
        'device_serial',
        'vehicle_device_uuid',
        'part_uuid',
        'status',
        'disposition',
        'depot_place_uuid',
        'shipped_at',
        'received_at',
        'closed_at',
        'core_status',
        'core_credit_amount',
        'currency',
        'notes',
        'meta',
    ];

    protected $casts = [
        'shipped_at'         => 'datetime',
        'received_at'        => 'datetime',
        'closed_at'          => 'datetime',
        'core_credit_amount' => Money::class,
        'meta'               => Json::class,
    ];

    public function vehicleDevice(): BelongsTo
    {
        return $this->belongsTo(\Fleetbase\FleetOps\Models\VehicleDevice::class, 'vehicle_device_uuid', 'uuid');
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(\Fleetbase\FleetOps\Models\Part::class, 'part_uuid', 'uuid');
    }

    public function depot(): BelongsTo
    {
        return $this->belongsTo(\Fleetbase\FleetOps\Models\Place::class, 'depot_place_uuid', 'uuid');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['status', 'disposition', 'core_status']);
    }
}
