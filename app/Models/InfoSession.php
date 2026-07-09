<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InfoSession extends Model
{
    protected $fillable = [
        'campaign_id',
        'province_id',
        'school_id',
        'session_date',
        'start_time',
        'end_time',
        'location',
        'hosted_by',
        'description',
        'expected_attendance',
        'actual_attendance'
    ];

    protected $casts = [
        'session_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function interestedStudents(): HasMany
    {
        return $this->hasMany(InterestStudent::class);
    }
}