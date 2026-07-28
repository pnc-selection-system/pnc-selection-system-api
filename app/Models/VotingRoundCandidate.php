<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VotingRoundCandidate extends Model
{
    protected $table = 'voting_round_candidates';

    protected $fillable = [
        'voting_round_id',
        'candidate_id',
    ];

    public function votingRound(): BelongsTo
    {
        return $this->belongsTo(VotingRound::class, 'voting_round_id');
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }
}
