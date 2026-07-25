<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamOverallResult extends Model
{
    protected $table = 'exam_overall_results';

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
        'version'              => 'integer',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(SelectCampaing::class, 'campaign_id');
    }
}
