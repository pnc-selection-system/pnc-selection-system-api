<?php

namespace App\Services;

use App\Enums\CandidateStatus;
use App\Enums\VoteDecision;
use App\Enums\VotingMethod;
use App\Models\Candidate;
use App\Models\Vote;
use App\Models\VotingRound;
use Illuminate\Support\Collection;

class VotingTallyService
{
    /**
     * Compute the tally for a single candidate in a round.
     */
    public function tallyForCandidate(VotingRound $round, int $candidateId): array
    {
        $votes = Vote::where('voting_round_id', $round->id)
            ->where('candidate_id', $candidateId)
            ->get();

        $approve = $votes->where('decision', VoteDecision::Approve)->count();
        $reject = $votes->where('decision', VoteDecision::Reject)->count();
        $abstain = $votes->where('decision', VoteDecision::Abstain)->count();
        $totalCast = $approve + $reject + $abstain;

        $weightedScore = $this->computeWeightedScore($round, $votes);

        return [
            'candidate_id' => $candidateId,
            'approve' => $approve,
            'reject' => $reject,
            'abstain' => $abstain,
            'total_cast' => $totalCast,
            'total_members' => $round->total_members,
            'quorum_met' => $totalCast >= $round->quorum,
            'weighted_score' => $weightedScore,
            'voting_method' => $round->voting_method->value,
        ];
    }

    /**
     * Compute tallies for all candidates in a round.
     */
    public function tallyAll(VotingRound $round): array
    {
        $candidateIds = $round->candidates()->pluck('candidates.id');

        $tallies = [];
        foreach ($candidateIds as $cid) {
            $tallies[] = $this->tallyForCandidate($round, $cid);
        }

        return $tallies;
    }

    /**
     * Compute weighted score for a candidate based on voting method.
     */
    protected function computeWeightedScore(VotingRound $round, Collection $votes): float
    {
        if ($votes->isEmpty()) {
            return 0.0;
        }

        if ($round->voting_method === VotingMethod::Weighted) {
            // Weighted scoring: Approve = 1.0, Abstain = 0.5, Reject = 0.0
            $totalScore = $votes->sum(function ($vote) {
                return match ($vote->decision) {
                    VoteDecision::Approve => 1.0,
                    VoteDecision::Abstain => 0.5,
                    VoteDecision::Reject => 0.0,
                };
            });

            return round(($totalScore / $votes->count()) * 100, 2);
        }

        // Majority: percentage of approve votes (excluding abstain from denominator)
        $approved = $votes->where('decision', VoteDecision::Approve)->count();
        $rejected = $votes->where('decision', VoteDecision::Reject)->count();
        $totalNonAbstain = $approved + $rejected;

        if ($totalNonAbstain === 0) {
            return 0.0;
        }

        return round(($approved / $totalNonAbstain) * 100, 2);
    }

    /**
     * Check if quorum is met for the round.
     */
    public function checkQuorum(VotingRound $round): array
    {
        $totalVotesCast = Vote::where('voting_round_id', $round->id)
            ->distinct('member_id')
            ->count('member_id');

        return [
            'quorum_required' => $round->quorum,
            'total_voters' => $totalVotesCast,
            'total_members' => $round->total_members,
            'quorum_met' => $totalVotesCast >= $round->quorum,
        ];
    }

    /**
     * Finalize tally and generate SELECTED, WAITLISTED, NOT_SELECTED lists.
     */
    public function finalize(VotingRound $round): array
    {
        $tallies = $this->tallyAll($round);
        $quorumCheck = $this->checkQuorum($round);

        if (! $quorumCheck['quorum_met']) {
            return [
                'success' => false,
                'message' => 'Quorum not met. Cannot lock round.',
                'quorum' => $quorumCheck,
            ];
        }

        // Sort candidates by weighted score descending, then by approve count
        $sorted = collect($tallies)->sortByDesc(function ($tally) {
            return [$tally['weighted_score'], $tally['approve']];
        })->values();

        $selected = [];
        $waitlisted = [];
        $notSelected = [];

        $passThreshold = $round->pass_threshold;
        $waitlistCap = $round->waitlist_cap;

        foreach ($sorted as $tally) {
            $candidate = Candidate::find($tally['candidate_id']);
            if (! $candidate) {
                continue;
            }

            if ($tally['weighted_score'] >= $passThreshold) {
                // If waitlist cap > 0, the last N candidates above threshold go to waitlist
                $aboveThreshold = $sorted->filter(fn ($t) => $t['weighted_score'] >= $passThreshold)->count();
                $selectedLimit = $aboveThreshold - ($waitlistCap > 0 ? $waitlistCap : 0);

                if ($waitlistCap > 0 && count($selected) >= $selectedLimit) {
                    $status = CandidateStatus::Waitlisted;
                    $waitlisted[] = $tally;
                } else {
                    $status = CandidateStatus::Selected;
                    $selected[] = $tally;
                }
            } else {
                $status = CandidateStatus::NotSelected;
                $notSelected[] = $tally;
            }

            $candidate->update(['status' => $status->value]);
        }

        return [
            'success' => true,
            'message' => 'Round locked and results finalized.',
            'quorum' => $quorumCheck,
            'selected' => $selected,
            'waitlisted' => $waitlisted,
            'not_selected' => $notSelected,
        ];
    }
}
