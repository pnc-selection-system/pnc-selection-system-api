<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Candidate extends Model
{
    protected $table = 'candidates';

    protected $fillable = [
        'campaign_id',
        'province_id',
        'school_id',
        'ngo_id',
        'first_name',
        'last_name',
        'gender',
        'dob',
        'phone',
        'email',
        'national',
        'photo',
        'address',
        'household_size',
        'father_name',
        'mother_name',
        'guardian_name',
        'family_income',
        'housing',
        'graduation_year',
        'current_grade',
        'status',
    ];

    protected $casts = [
        'dob' => 'date',
        'household_size' => 'integer',
        'family_income' => 'decimal:2',
        'graduation_year' => 'integer',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(SelectCampaing::class, 'campaign_id');
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function referringNgo(): BelongsTo
    {
        return $this->belongsTo(NgoPartner::class, 'ngo_id');
    }
    public function ngoPartner()
    {
        return $this->belongsTo(NgoPartner::class, 'ngo_id');
    }
}
