<?php

namespace App\Models;

use App\Enums\ReviewStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamOverallResult extends Model
{
    protected $fillable = [
        'candidate_id',
        'campaign_id',
        'total_weighted_score',
        'overall_percentage',
        'passed',
        'status',
        'version',
    ];

    protected $casts = [
        'total_weighted_score' => 'decimal:2',
        'overall_percentage'   => 'decimal:2',
        'passed'               => 'boolean',
        'status'               => ReviewStatus::class,
        'version'              => 'integer',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Cadidate::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(SelectCampaing::class, 'campaign_id');
    }
}
