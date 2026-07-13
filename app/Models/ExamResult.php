<?php

namespace App\Models;

use App\Enums\ReviewStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamResult extends Model
{
    protected $fillable = [
        'campaign_id',
        'candidate_id',
        'subject_id',
        'raw_correct',
        'raw_wrong',
        'raw_score',
        'deduction',
        'final_score',
        'rank',
        'passed',
        'status',
        'version',
    ];

    protected $casts = [
        'raw_score'   => 'decimal:2',
        'deduction'   => 'decimal:2',
        'final_score' => 'decimal:2',
        'passed'      => 'boolean',
        'status'      => ReviewStatus::class,
        'version'     => 'integer',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Cadidate::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(ExamSubject::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(SelectCampaing::class, 'campaign_id');
    }
}
