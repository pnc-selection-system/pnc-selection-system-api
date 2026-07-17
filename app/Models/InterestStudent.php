<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterestStudent extends Model
{
    protected $table = 'interested_students';

    protected $fillable = [
        'info_session_id',
        'full_name',
        'gender',
        'phone',
        'school_grade',
        'assessment_answers',
        'total_score',
    ];

    protected $casts = [
        'assessment_answers' => 'array',
        'total_score' => 'decimal:2',
    ];

    public function infoSession(): BelongsTo
    {
        return $this->belongsTo(InfoSession::class, 'info_session_id');
    }
}
