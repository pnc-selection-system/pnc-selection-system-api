<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InfoSession extends Model
{
    protected $fillable = [
        'campaign_id',
        'created_by',
        'province_id',
        'district_id',
        'commune_id',
        'village_id',
        'school',
        'session_date',
        'session_time',
        'expected_attendance',
        'attendance_count',
        'partner_type',
        'partner_name',
        'host_by',
        'venue',
        'location',
        'department',
        'generation',
    ];

    protected $appends = [
        'school_name',
        'host_name',
    ];

    public function getSchoolNameAttribute(): ?string
    {
        return $this->school;
    }

    public function getHostNameAttribute(): ?string
    {
        return $this->host_by;
    }

    protected $casts = [
        'session_date' => 'date',
    ];

    /**
     * Override toArray to replace province/district/commune/village objects with just their name strings.
     */
    public function toArray(): array
    {
        $data = parent::toArray();

        foreach (['province', 'district', 'commune', 'village'] as $rel) {
            if ($this->relationLoaded($rel)) {
                $data[$rel] = $this->getRelation($rel)->name ?? null;
            }
        }

        return $data;
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(SelectCampaing::class);
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function commune(): BelongsTo
    {
        return $this->belongsTo(Commune::class);
    }

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function hosts()
    {
        return $this->hasMany(InfoSessionHost::class);
    }
}
