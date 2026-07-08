<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentForm extends Model
{
    protected $fillable = [
        'campaign_id',
        'name',
        'schema',
    ];

    protected $casts = [
        'schema' => 'array',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(SelectCampaing::class, 'campaign_id');
    }
}
