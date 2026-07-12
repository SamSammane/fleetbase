<?php

namespace IFS\CommandIQ\Models;

use Fleetbase\Casts\Json;
use Fleetbase\Models\Model;
use Fleetbase\Traits\HasApiModelBehavior;
use Fleetbase\Traits\HasMetaAttributes;
use Fleetbase\Traits\HasPublicId;
use Fleetbase\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A learned DSP/station return-time pattern used to forecast Last Mile
 * availability windows (average return time and variance per weekday).
 *
 * Spec: Section 8.2 — FR-19, FR-20.
 */
class ReturnPattern extends Model
{
    use HasUuid;
    use HasPublicId;
    use HasApiModelBehavior;
    use HasMetaAttributes;

    protected $table = 'commandiq_return_patterns';

    protected $publicIdType = 'return_pattern';

    protected $fillable = [
        'company_uuid',
        'dsp_code',
        'station_code',
        'place_uuid',
        'vehicle_uuid',
        'weekday',
        'avg_return_time',
        'stddev_minutes',
        'sample_size',
        'lookback_days',
        'computed_at',
        'meta',
    ];

    protected $casts = [
        'weekday'        => 'integer',
        'stddev_minutes' => 'float',
        'sample_size'    => 'integer',
        'lookback_days'  => 'integer',
        'computed_at'    => 'datetime',
        'meta'           => Json::class,
    ];

    public function place(): BelongsTo
    {
        return $this->belongsTo(\Fleetbase\FleetOps\Models\Place::class, 'place_uuid', 'uuid');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(\Fleetbase\FleetOps\Models\Vehicle::class, 'vehicle_uuid', 'uuid');
    }
}
