<?php

namespace App\Models;

use App\Enums\VoteDecision;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vote extends Model
{
    protected $fillable = [
        'voting_round_id',
        'candidate_id',
        'member_id',
        'decision',
        'comment',
        'voted_at',
    ];

    protected $casts = [
        'decision' => VoteDecision::class,
        'voted_at' => 'datetime',
    ];

    public function votingRound(): BelongsTo
    {
        return $this->belongsTo(VotingRound::class, 'voting_round_id');
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(User::class, 'member_id');
    }
}
