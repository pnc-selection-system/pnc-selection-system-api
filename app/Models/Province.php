<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Province extends Model
{
    protected $fillable = [
        'name',
    ];

    public function districts(): HasMany
    {
        return $this->hasMany(District::class);
    }

    public function schools(): HasMany
    {
        return $this->hasMany(School::class);
    }

    public function campaigns(): BelongsToMany
    {
        return $this->belongsToMany(SelectCampaing::class, 'campaign_province', 'province_id', 'selection_campaign_id');
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(Cadidate::class, 'province_id');
    }
}
