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
 * A forecasted period during which an asset is at a serviceable location
 * (LM station return or MM hub arrival) long enough to be worked on.
 *
 * Spec: Section 8.2 — FR-3, FR-4, FR-5, FR-19, FR-21, FR-22.
 */
class AvailabilityWindow extends Model
{
    use HasUuid;
    use HasPublicId;
    use HasApiModelBehavior;
    use HasMetaAttributes;

    protected $table = 'commandiq_availability_windows';

    protected $publicIdType = 'availability_window';

    protected $fillable = [
        'company_uuid',
        'subject_type',
        'subject_uuid',
        'segment',
        'place_uuid',
        'location_code',
        'starts_at',
        'ends_at',
        'confidence',
        'source',
        'status',
        'validated_at',
        'meta',
    ];

    protected $casts = [
        'starts_at'    => 'datetime',
        'ends_at'      => 'datetime',
        'validated_at' => 'datetime',
        'confidence'   => 'float',
        'meta'         => Json::class,
    ];

    /**
     * The asset (vehicle/trailer) this window is forecast for.
     */
    public function subject(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'subject_type', 'subject_uuid');
    }

    /**
     * The station/hub place this window occurs at.
     */
    public function place(): BelongsTo
    {
        return $this->belongsTo(\Fleetbase\FleetOps\Models\Place::class, 'place_uuid', 'uuid');
    }
}
