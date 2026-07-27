<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentResponse extends Model
{
    protected $table = 'assessment_responses';

    protected $fillable = [
        'candidate_id',
        'form_id',
        'answers',
        'total_score',
        'passed',
    ];

    protected $casts = [
        'answers' => 'array',
        'passed' => 'boolean',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(AssessmentForm::class, 'form_id');
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }
}
