<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class HomeInvestigation extends Model
{
    use SoftDeletes;

    protected $table = 'home_investigations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        // Core fields
        'candidate_id',
        'candidate_name',
        'campaign',
        'campaign_id',
        'gender',
        'phone_number',
        'current_address',
        'assigned_investigator',
        'investigator_id',

        // Form fields
        'visit_date',
        'location',
        'gps_coordinates',
        'people_met',
        'observations',
        'findings',
        'recommendation',
        'reason',

        // Status
        'status',
        'submitted_at',
        'approved_at',
        'rejected_at',
        'rejection_reason',
        'notes',
        'summary',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'visit_date' => 'date:Y-m-d',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<string, string>
     */
    protected $hidden = [
        'deleted_at',
        'campaign_id',
        'investigator_id',
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
    public function campaignRelation(): BelongsTo
    {
        return $this->belongsTo(SelectCampaing::class, 'campaign_id');
    }

    /**
     * Get the investigator (user) for this home investigation.
     */
    public function investigator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'investigator_id');
    }

    /**
     * Get the files for this home investigation (3-status attachments).
     */
    public function files(): HasMany
    {
        return $this->hasMany(HomeInvestigationFile::class, 'home_investigation_id');
    }

    /**
     * Get the history entries for this investigation (5-status system).
     */
    public function history(): HasMany
    {
        return $this->hasMany(InvestigationHistory::class, 'investigation_id');
    }

    /**
     * Scope a query to only include investigations with a specific status.
     */
    public function scopeWhereStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include investigations assigned to a specific investigator.
     */
    public function scopeWhereInvestigator($query, string $investigatorName)
    {
        return $query->where('assigned_investigator', $investigatorName);
    }
}
