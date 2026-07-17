<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cadidate extends Model
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

    protected $appends = ['code', 'full_name'];

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
        return $this->belongsTo(Province::class, 'province_id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function ngo(): BelongsTo
    {
        return $this->belongsTo(NgoPartner::class, 'ngo_id');
    }

    public function assessmentResponses(): HasMany
    {
        return $this->hasMany(AssessmentRespone::class, 'candidate_id');
    }

    /**
     * Get the candidate's full name.
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Get a display code for the candidate (e.g., C-1042).
     */
    public function getCodeAttribute(): string
    {
        return 'C-' . str_pad($this->id, 4, '0', STR_PAD_LEFT);
    }
}
