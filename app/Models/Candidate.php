<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Candidate extends Model
{
    protected $table = 'candidates';

    protected $fillable = [
        'campaign_id',
        'province_id',
        'school_name',
        'ngo_id',
        'first_name',
        'last_name',
        'first_name_kh',
        'last_name_kh',
        'gender',
        'dob',
        'phone',
        'status',
    ];

    protected $casts = [
        'dob' => 'date',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(SelectCampaing::class, 'campaign_id');
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function referringNgo(): BelongsTo
    {
        return $this->belongsTo(NgoPartner::class, 'ngo_id');
    }
    public function ngoPartner()
    {
        return $this->belongsTo(NgoPartner::class, 'ngo_id');
    }

    public function votingRounds(): BelongsToMany
    {
        return $this->belongsToMany(VotingRound::class, 'voting_round_candidates', 'candidate_id', 'voting_round_id');
    }
}
