<?php

namespace App\Models;

use App\Enums\CampaignStatus;
use Illuminate\Database\Eloquent\Model;

class SelectCampaing extends Model
{
    protected $table = 'selection_campaigns';

    protected $fillable = [
        'name',
        'year',
        'condidate_total',
        'province_total',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'year'            => 'integer',
        'condidate_total' => 'integer',
        'province_total'  => 'integer',
        'start_date'      => 'date',
        'end_date'        => 'date',
        'status'          => CampaignStatus::class,
    ];

    public function provinces()
    {
        return $this->belongsToMany(Province::class, 'campaign_province', 'selection_campaign_id', 'province_id');
    }

    public function candidates()
    {
        return $this->hasMany(Cadidate::class, 'campaign_id');
    }

    public function examResults()
    {
        return $this->hasMany(ExamResult::class, 'campaign_id');
    }

    public function examOverallResults()
    {
        return $this->hasMany(ExamOverallResult::class, 'campaign_id');
    }
}
