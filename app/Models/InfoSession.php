<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InfoSession extends Model
{
    protected $fillable = [
        'campaign_id',
        'village_id',
        'school_name',
        'session_date',
        'session_time',
        'expected_attendance',
        'attendance_count',
    ];

    protected $casts = [
        'session_date' => 'date',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function hosts()
    {
        return $this->hasMany(InfoSessionHost::class);
    }
}
