<?php

namespace App\Models;

use App\Enums\VotingMethod;
use App\Enums\VotingRoundStatus;
use App\Models\District;
use App\Models\Province;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Date;

class VotingRound extends Model
{
    protected $fillable = [
        'campaign_id',
        'province_id',
        'district_id',
        'name',
        'voting_method',
        'start_date',
        'end_date',
        'quorum',
        'pass_threshold',
        'waitlist_cap',
        'total_members',
        'status',
        'locked_at',
    ];

    protected $casts = [
        'status' => VotingRoundStatus::class,
        'voting_method' => VotingMethod::class,
        'locked_at' => 'datetime',
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
        'quorum' => 'integer',
        'pass_threshold' => 'integer',
        'waitlist_cap' => 'integer',
        'total_members' => 'integer',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(SelectCampaing::class, 'campaign_id');
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    public function candidates(): BelongsToMany
    {
        return $this->belongsToMany(Candidate::class, 'voting_round_candidates', 'voting_round_id', 'candidate_id')
            ->withTimestamps();
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class, 'voting_round_id');
    }

    /**
     * Check if the round is locked (closed or past end_date).
     */
    public function isLocked(): bool
    {
        if ($this->status === VotingRoundStatus::Closed || $this->locked_at !== null) {
            return true;
        }

        // Auto-lock if end_date is in the past
        if ($this->end_date && Date::today()->gt($this->end_date)) {
            return true;
        }

        return false;
    }

    /**
     * Check if the round is currently open for voting.
     * Must be status=Open, within start_date..end_date, and not locked.
     */
    public function isOpen(): bool
    {
        if ($this->isLocked()) {
            return false;
        }

        if ($this->status !== VotingRoundStatus::Open) {
            return false;
        }

        // Check if voting period has started
        if ($this->start_date && Date::today()->lt($this->start_date)) {
            return false;
        }

        return true;
    }

    /**
     * Check if the round is scheduled but not yet open.
     */
    public function isScheduled(): bool
    {
        return $this->status === VotingRoundStatus::Scheduled
            || ($this->start_date && Date::today()->lt($this->start_date));
    }

    /**
     * Automatically update the round status based on dates.
     * Called before any voting operation.
     */
    public function syncStatus(): void
    {
        $now = Date::today();

        // If end_date has passed, auto-close
        if ($this->end_date && $now->gt($this->end_date) && $this->status !== VotingRoundStatus::Closed) {
            $this->update([
                'status' => VotingRoundStatus::Closed,
                'locked_at' => $now,
            ]);
            return;
        }

        // If start_date has arrived, auto-open
        if ($this->status === VotingRoundStatus::Scheduled
            && $this->start_date
            && $now->gte($this->start_date)
            && (! $this->end_date || $now->lte($this->end_date))
        ) {
            $this->update(['status' => VotingRoundStatus::Open]);
        }
    }
}
