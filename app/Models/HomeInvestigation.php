<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class HomeInvestigation extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'candidate_id',
        'campaign_id',
        'investigator_id',
        'visit_date',
        'location',
        'people_met',
        'observations',
        'findings',
        'recommendation',
        'status',
        'submitted_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'visit_date' => 'date',
        'submitted_at' => 'datetime',
    ];

    /**
     * Get the candidate for this home investigation.
     */
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    /**
     * Get the campaign for this home investigation.
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(SelectCampaing::class);
    }

    /**
     * Get the investigator for this home investigation.
     */
    public function investigator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'investigator_id');
    }

    /**
     * Get the files for this home investigation.
     */
    public function files(): HasMany
    {
        return $this->hasMany(HomeInvestigationFile::class);
    }
}