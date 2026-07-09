<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Exam extends Model
{
    protected $fillable = [
        'campaign_id',
        'exam_date',
        'publish_status',
    ];

    protected $casts = [
        'publish_status' => 'boolean',
        'exam_date' => 'date',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(SelectCampaing::class, 'campaign_id');
    }
}
