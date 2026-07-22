<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamResult extends Model
{
    protected $table = 'exam_results';

    protected $fillable = [
        'candidate_id',
        'subject_id',
        'raw_correct',
        'raw_wrong',
        'raw_score',
        'deduction',
        'final_score',
        'rank',
        'passed',
    ];

    protected $casts = [
        'raw_correct' => 'integer',
        'raw_wrong' => 'integer',
        'raw_score' => 'decimal:2',
        'deduction' => 'decimal:2',
        'final_score' => 'decimal:2',
        'rank' => 'integer',
        'passed' => 'boolean',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Cadidate::class, 'candidate_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(ExamSubject::class, 'subject_id');
    }
}
