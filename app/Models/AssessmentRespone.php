<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentRespone extends Model
{
    protected $table = 'assessment_responses';

    protected $fillable = [
        'candidate_id',
        'question_id',
        'answer',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Cadidate::class, 'candidate_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(AssessmentQuestion::class, 'question_id');
    }
}
