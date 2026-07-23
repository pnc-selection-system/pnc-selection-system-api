<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamThreshold extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'subject_id',
        'overall_pass_mark',
        'per_subject_min',
        'must_pass_every_subject',
    ];

    protected $casts = [
        'overall_pass_mark' => 'decimal:2',
        'per_subject_min' => 'decimal:2',
        'must_pass_every_subject' => 'boolean',
    ];

    /**
     * Relationship with Campaign
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(SelectCampaing::class, 'campaign_id');
    }

    /**
     * Relationship with ExamSubject
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(ExamSubject::class, 'subject_id');
    }

    /**
     * Scope for overall threshold (no subject)
     */
    public function scopeOverall($query)
    {
        return $query->whereNull('subject_id');
    }

    /**
     * Scope for subject-specific threshold
     */
    public function scopeForSubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    /**
     * Scope for campaign
     */
    public function scopeForCampaign($query, $campaignId)
    {
        return $query->where('campaign_id', $campaignId);
    }
}
