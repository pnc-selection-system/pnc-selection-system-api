<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamThreshold extends Model
{
    protected $fillable = [
        'campaign_id',
        'subject_id',
        'pass_score',
    ];

    protected $casts = [
        'pass_score' => 'decimal:2',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(SelectCampaing::class, 'campaign_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(ExamSubject::class, 'subject_id');
    }
}
