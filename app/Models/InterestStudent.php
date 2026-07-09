<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterestStudent extends Model
{
    protected $fillable = [
        'info_session_id',
        'full_name',
        'gender',
        'phone',
        'email',
        'current_grade',
        'school_name',
        'preferred_major',
        'notes',
        'status',
        'converted_to_candidate_id'
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function infoSession(): BelongsTo
    {
        return $this->belongsTo(InfoSession::class);
    }

    public function convertedToCandidate(): BelongsTo
    {
        return $this->belongsTo(Cadidate::class, 'converted_to_candidate_id');
    }
}