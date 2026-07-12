<?php

namespace IFS\CommandIQ\Models;

use Fleetbase\Casts\Json;
use Fleetbase\Models\Model;
use Fleetbase\Traits\HasApiModelBehavior;
use Fleetbase\Traits\HasMetaAttributes;
use Fleetbase\Traits\HasPublicId;
use Fleetbase\Traits\HasUuid;
use Fleetbase\Traits\Searchable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * A retrofit/remediation program executed across an assigned asset
 * population (e.g., telematics install, conspicuity tape).
 *
 * Spec: Section 8.13 — FR-43, FR-44, FR-45.
 */
class Campaign extends Model
{
    use HasUuid;
    use HasPublicId;
    use HasApiModelBehavior;
    use HasMetaAttributes;
    use Searchable;
    use LogsActivity;

    protected $table = 'commandiq_campaigns';

    protected $publicIdType = 'campaign';

    protected $searchableColumns = ['name', 'code'];

    protected $fillable = [
        'company_uuid',
        'code',
        'name',
        'description',
        'type',
        'status',
        'priority',
        'work_order_category',
        'starts_at',
        'ends_at',
        'bundling_enabled',
        'meta',
    ];

    protected $casts = [
        'starts_at'        => 'datetime',
        'ends_at'          => 'datetime',
        'bundling_enabled' => 'boolean',
        'meta'             => Json::class,
    ];

    public function assignments(): HasMany
    {
        return $this->hasMany(CampaignAssignment::class, 'campaign_uuid', 'uuid');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['name', 'status', 'priority']);
    }
}
