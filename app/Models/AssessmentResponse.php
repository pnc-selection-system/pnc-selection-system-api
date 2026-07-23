<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentResponse extends Model
{
    protected $fillable = [
        'candidate_id',
        'assessment_form_id',
        'answers',
        'total_score',
        'passed',
    ];

    protected $casts = [
        'answers' => 'array',
        'total_score' => 'decimal:2',
        'passed' => 'boolean',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(AssessmentForm::class, 'assessment_form_id');
    }
}
