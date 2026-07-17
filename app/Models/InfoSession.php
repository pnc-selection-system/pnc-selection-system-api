<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InfoSession extends Model
{
    protected $table = 'information_sessions';

    protected $fillable = [
        'campaign_id',
        'province_id',
        'school_id',
        'session_date',
        'session_time',
        'location',
        'host_name',
        'expect_attendance',
        'attendance_count',
    ];

    protected $casts = [
        'session_date' => 'date',
        'expect_attendance' => 'integer',
        'attendance_count' => 'integer',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(SelectCampaing::class, 'campaign_id');
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function interestedStudents(): HasMany
    {
        return $this->hasMany(InterestStudent::class, 'info_session_id');
    }
}
