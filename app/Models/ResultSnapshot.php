<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResultSnapshot extends Model
{
    protected $fillable = [
        'campaign_id',
        'candidate_id',
        'subject_id',
        'old_data',
        'new_data',
        'version',
        'reason',
        'recalculated_by',
    ];

    protected $casts = [
        'old_data' => 'json',
        'new_data' => 'json',
        'version'  => 'integer',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(SelectCampaing::class, 'campaign_id');
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(ExamSubject::class, 'subject_id');
    }

    public function recalculatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recalculated_by');
    }
}
