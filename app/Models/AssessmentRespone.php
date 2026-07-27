<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentRespone extends Model
{
    protected $table = 'assessment_responses';

    protected $fillable = [
        'candidate_id',
        'form_id',
        'answers',
        'total_score',
        'passed',
        'submitted_by',
    ];

    protected $casts = [
        'answers' => 'array',
        'total_score' => 'decimal:2',
        'passed' => 'boolean',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(AssessmentForm::class, 'form_id');
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Cadidate::class, 'candidate_id');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}
