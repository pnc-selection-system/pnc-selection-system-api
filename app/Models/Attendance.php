<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'info_session_id',
        'total_students',
        'male_students',
        'female_students',
        'teachers_attended',
        'notes'
    ];

    public function infoSession(): BelongsTo
    {
        return $this->belongsTo(InfoSession::class);
    }
}