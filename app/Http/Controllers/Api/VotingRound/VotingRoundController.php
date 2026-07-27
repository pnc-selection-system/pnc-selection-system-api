<?php

namespace App\Http\Controllers\Api\VotingRound;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\VotingRound\AddCandidatesRequest;
use App\Http\Requests\Api\VotingRound\CastVoteRequest;
use App\Http\Requests\Api\VotingRound\StoreVotingRoundRequest;
use App\Models\Candidate;
use App\Models\Role;
use App\Models\Vote;
use App\Models\VotingRound;
use App\Services\VotingTallyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VotingRoundController extends Controller
{
    public function __construct(protected VotingTallyService $tallyService) {}

    /**
     * Authorize that only Super Admin or Selection Manager can perform this action.
     */
    private function authorizeManager(): void
    {
        $user = Auth::user();
        if (! $user) {
            abort(401, 'Unauthenticated');
        }

        $managerRoles = ['Admin', 'Manager'];
        $role = Role::find($user->role_id);

        if (! $role || ! in_array($role->name, $managerRoles)) {
            abort(403, 'Only Super Admin or Selection Manager can perform this action');
        }
    }

    /**
     * POST /voting-rounds
     * Create a new voting round for a campaign (Super Admin / Selection Manager only).
     */
    public function store(StoreVotingRoundRequest $request): JsonResponse
    {
        $this->authorizeManager();

        $data = $request->validated();

        $round = VotingRound::create($data);
        $round->load(['campaign:id,name', 'province:id,name', 'district:id,name']);

        return ApiResponse::created($round, 'Voting round created successfully');
    }

    /**
     * GET /voting-rounds
     * List voting rounds (optionally filtered by campaign_id).
     */
    public function index(Request $request): JsonResponse
    {
        $paginated = VotingRound::with(['campaign:id,name', 'province:id,name', 'district:id,province_id,name'])
            ->when($request->filled('campaign_id'), fn($q) => $q->where('campaign_id', $request->integer('campaign_id')))
            ->latest()
            ->paginate($request->get('per_page', 10));

        // Sync statuses for auto-open/close based on dates
        foreach ($paginated->items() as $round) {
            $round->syncStatus();
        }

        // Refresh in case status changed
        $paginated->getCollection()->transform(function ($round) {
            return $round->fresh()->load(['campaign:id,name', 'province:id,name', 'district:id,province_id,name']);
        });

        return ApiResponse::success($paginated, 'Voting rounds retrieved successfully');
    }

    /**
     * GET /voting-rounds/{votingRound}
     * Show a single voting round.
     */
    public function show(VotingRound $votingRound): JsonResponse
    {
        $votingRound->syncStatus();
        $votingRound->load(['campaign:id,name', 'province:id,name', 'district:id,province_id,name']);

        return ApiResponse::success($votingRound, 'Voting round retrieved successfully');
    }

    /**
     * POST /voting-rounds/{votingRound}/candidates
     * Add candidates to a voting round (set status to In Voting).
     * Super Admin / Selection Manager only.
     *
     * Only candidates who have passed interest assessment AND home investigation
     * are eligible (validated by AddCandidatesRequest).
     */
    public function addCandidates(AddCandidatesRequest $request, VotingRound $votingRound): JsonResponse
    {
        $this->authorizeManager();

        $votingRound->syncStatus();

        if ($votingRound->isLocked()) {
            return ApiResponse::error('Cannot add candidates to a locked round.', 422);
        }

        // Validation is already done in AddCandidatesRequest::withValidator()
        $candidateIds = $request->input('candidate_ids', []);

        DB::transaction(function () use ($candidateIds, $votingRound) {
            // Attach candidates to round (skip already attached)
            // Keep candidate status as 'Investigated' so they remain visible in the voting shortlist
            $votingRound->candidates()->syncWithoutDetaching($candidateIds);
        });

        $votingRound->load('candidates:id,first_name,last_name');

        return ApiResponse::success($votingRound, 'Candidates added to voting round successfully');
    }

    /**
     * GET /voting-rounds/{votingRound}/candidates
     * List candidates eligible for voting.
     *
     * Only returns candidates who have passed interest assessment AND home investigation.
     *
     * 1. If candidates have been added to the round, returns those (filtered to eligible only).
     * 2. Otherwise, returns all eligible candidates from the same campaign.
     *
     * Each candidate includes their full enriched profile (exam, assessment, investigation, vote).
     */
    public function candidates(Request $request, VotingRound $votingRound): JsonResponse
    {
        $votingRound->syncStatus();

        // Check if any candidates are already in the round
        $roundCandidateIds = $votingRound->candidates()->pluck('candidates.id');

        if ($roundCandidateIds->isNotEmpty()) {
            // Return candidates already in the round (only investigated candidates who passed assessment + investigation)
            $candidates = $votingRound->candidates()
                ->with(['campaign:id,name', 'province:id,name', 'referringNgo:id,name'])
                ->where('candidates.status', 'Investigated')
                ->whereExists(function ($q) {
                    $q->select(DB::raw(1))
                      ->from('assessment_responses')
                      ->whereColumn('assessment_responses.candidate_id', 'candidates.id')
                      ->where('assessment_responses.passed', true);
                })
                ->whereExists(function ($q) {
                    $q->select(DB::raw(1))
                      ->from('home_investigations')
                      ->whereColumn('home_investigations.candidate_id', 'candidates.id')
                      ->where('home_investigations.recommendation', 'Recommend');
                })
                ->get()
                ->map(function ($candidate) use ($votingRound) {
                    $profile = $this->enrichCandidateProfile($candidate, $votingRound);
                    $profile['in_round'] = true;
                    return $profile;
                });

            return ApiResponse::success($candidates, 'Candidates retrieved successfully');
        }

        // Round is empty — return eligible candidates from the same campaign
        // Eligible = passed interest assessment (assessment_responses.passed = true)
        //            AND approved home investigation (home_investigations.recommendation = 'Recommend')
        $eligibleCandidates = Candidate::where('candidates.campaign_id', $votingRound->campaign_id)
            ->where('candidates.status', 'Investigated')
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('assessment_responses')
                  ->whereColumn('assessment_responses.candidate_id', 'candidates.id')
                  ->where('assessment_responses.passed', true);
            })
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('home_investigations')
                  ->whereColumn('home_investigations.candidate_id', 'candidates.id')
                  ->where('home_investigations.recommendation', 'Recommend');
            })
            ->doesntHave('votingRounds')  // exclude candidates already in any round
            ->with(['campaign:id,name', 'province:id,name', 'referringNgo:id,name'])
            ->latest('candidates.id')
            ->get()
            ->map(function ($candidate) use ($votingRound) {
                $profile = $this->enrichCandidateProfile($candidate, $votingRound);
                $profile['in_round'] = false;
                return $profile;
            });

        return ApiResponse::success($eligibleCandidates, 'Eligible candidates retrieved successfully');
    }

    /**
     * POST /voting-rounds/{votingRound}/candidates/{cid}/vote
     * Cast or update a vote for the current user (upsert, changeable until round closes).
     * Accessible by any authenticated committee member.
     *
     * Only candidates who passed interest assessment AND home investigation may be voted on.
     */
    public function castVote(CastVoteRequest $request, VotingRound $votingRound, int $cid): JsonResponse
    {
        $votingRound->syncStatus();

        if ($votingRound->isLocked()) {
            return ApiResponse::error('Round is locked. Voting is closed.', 422);
        }

        if (! $votingRound->isOpen()) {
            return ApiResponse::error('Round is not open for voting.', 422);
        }

        // Verify candidate is in this round
        $exists = $votingRound->candidates()->where('candidate_id', $cid)->exists();
        if (! $exists) {
            return ApiResponse::notFound('Candidate is not in this voting round.');
        }

        // Double-check eligibility: must have passed interest assessment AND home investigation
        $passedAssessment = DB::table('assessment_responses')
            ->where('candidate_id', $cid)
            ->where('passed', true)
            ->exists();

        if (! $passedAssessment) {
            return ApiResponse::error('Candidate has not passed the interest assessment.', 422);
        }

        $passedInvestigation = DB::table('home_investigations')
            ->where('candidate_id', $cid)
            ->where('recommendation', 'Recommend')
            ->exists();

        if (! $passedInvestigation) {
            return ApiResponse::error('Candidate has not been recommended by home investigation.', 422);
        }

        $user = $request->user();
        $decision = $request->input('decision');
        $comment = $request->input('comment');

        // Upsert vote (one vote per member per candidate per round)
        $vote = Vote::updateOrCreate(
            [
                'voting_round_id' => $votingRound->id,
                'candidate_id' => $cid,
                'member_id' => $user->id,
            ],
            [
                'decision' => $decision,
                'comment' => $comment,
                'voted_at' => now(),
            ]
        );

        // Return updated tally for this candidate
        $tally = $this->tallyService->tallyForCandidate($votingRound, $cid);

        return ApiResponse::success([
            'vote' => $vote->load('member:id,name'),
            'tally' => $tally,
        ], 'Vote recorded successfully');
    }

    /**
     * GET /voting-rounds/{votingRound}/tally
     * Show current tally results for all candidates.
     */
    public function tally(Request $request, VotingRound $votingRound): JsonResponse
    {
        $tallies = $this->tallyService->tallyAll($votingRound);
        $quorum = $this->tallyService->checkQuorum($votingRound);

        // Enrich with candidate info
        $enriched = [];
        foreach ($tallies as $tally) {
            $candidate = Candidate::find($tally['candidate_id']);
            $enriched[] = array_merge($tally, [
                'candidate_name' => $candidate ? $candidate->first_name . ' ' . $candidate->last_name : 'Unknown',
            ]);
        }

        return ApiResponse::success([
            'round' => $votingRound->only(['id', 'name', 'status', 'voting_method', 'locked_at', 'total_members']),
            'quorum' => $quorum,
            'candidates' => $enriched,
        ], 'Tally retrieved successfully');
    }

    /**
     * POST /voting-rounds/{votingRound}/lock
     * Lock the round, finalize tally, and set candidate statuses.
     * Super Admin / Selection Manager only (enforced by middleware).
     */
    /**
     * PUT /voting-rounds/{votingRound}
     * Update a voting round (Super Admin / Selection Manager only).
     */
    public function update(Request $request, VotingRound $votingRound): JsonResponse
    {
        $this->authorizeManager();

        if ($votingRound->isLocked()) {
            return ApiResponse::error('Cannot update a locked round.', 422);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'voting_method' => 'sometimes|string|in:Majority,Weighted',
            'province_id' => 'nullable|integer|exists:provinces,id',
            'district_id' => 'nullable|integer|exists:districts,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'quorum' => 'nullable|integer|min:0',
            'pass_threshold' => 'nullable|integer|min:0|max:100',
            'waitlist_cap' => 'nullable|integer|min:0',
            'total_members' => 'nullable|integer|min:0',
        ]);

        $votingRound->update($validated);
        $votingRound->load(['campaign:id,name', 'province:id,name', 'district:id,province_id,name']);

        return ApiResponse::success($votingRound, 'Voting round updated successfully');
    }

    /**
     * POST /voting-rounds/{votingRound}/lock
     * Lock the round, finalize tally, and set candidate statuses.
     * Super Admin / Selection Manager only (enforced by middleware).
     */
    public function lock(Request $request, VotingRound $votingRound): JsonResponse
    {
        $this->authorizeManager();

        $votingRound->syncStatus();

        if ($votingRound->isLocked()) {
            return ApiResponse::error('Round is already locked.', 422);
        }

        $result = $this->tallyService->finalize($votingRound);

        if (! $result['success']) {
            return ApiResponse::error($result['message'], 422);
        }

        // Update round status
        $votingRound->update([
            'status' => 'Closed',
            'locked_at' => now(),
        ]);

        return ApiResponse::success($result, 'Round locked and results finalized successfully');
    }

    /**
     * GET /voting-rounds/{votingRound}/results
     * Get final selected / waitlisted / not selected lists for reports.
     */
    public function results(Request $request, VotingRound $votingRound): JsonResponse
    {
        if (! $votingRound->isLocked()) {
            return ApiResponse::error('Round is not yet locked. Results are not final.', 422);
        }

        $selected = Candidate::whereIn('id', $votingRound->candidates()->pluck('candidates.id'))
            ->where('status', 'Selected')
            ->get()
            ->map(fn ($c) => $this->enrichCandidateProfile($c, $votingRound));

        $waitlisted = Candidate::whereIn('id', $votingRound->candidates()->pluck('candidates.id'))
            ->where('status', 'Waitlisted')
            ->get()
            ->map(fn ($c) => $this->enrichCandidateProfile($c, $votingRound));

        $notSelected = Candidate::whereIn('id', $votingRound->candidates()->pluck('candidates.id'))
            ->where('status', 'Not Selected')
            ->get()
            ->map(fn ($c) => $this->enrichCandidateProfile($c, $votingRound));

        $quorum = $this->tallyService->checkQuorum($votingRound);

        return ApiResponse::success([
            'round' => $votingRound->only(['id', 'name', 'status', 'voting_method', 'locked_at', 'total_members']),
            'quorum' => $quorum,
            'selected' => $selected,
            'waitlisted' => $waitlisted,
            'not_selected' => $notSelected,
        ], 'Results retrieved successfully');
    }

    /**
     * GET /voting-rounds/{votingRound}/my-votes
     * Get the current user's votes for all candidates in this round.
     */
    public function myVotes(Request $request, VotingRound $votingRound): JsonResponse
    {
        $user = $request->user();

        $votes = Vote::where('voting_round_id', $votingRound->id)
            ->where('member_id', $user->id)
            ->get()
            ->keyBy('candidate_id')
            ->map(fn ($vote) => [
                'candidate_id' => $vote->candidate_id,
                'decision' => $vote->decision->value,
                'comment' => $vote->comment,
                'voted_at' => $vote->voted_at,
            ]);

        return ApiResponse::success($votes, 'My votes retrieved successfully');
    }

    /**
     * Enrich a candidate with exam score, assessment score, investigation recommendation.
     */
    protected function enrichCandidateProfile(Candidate $candidate, VotingRound $round): array
    {
        // Get exam scores (aggregate from exam_results)
        $examScore = DB::table('exam_results')
            ->join('exam_subjects', 'exam_results.subject_id', '=', 'exam_subjects.id')
            ->join('exams', 'exam_subjects.exam_id', '=', 'exams.id')
            ->where('exam_results.candidate_id', $candidate->id)
            ->where('exams.campaign_id', $round->campaign_id)
            ->avg('exam_results.final_score');

        // Get exam rank (overall rank from candidate_rank if exists, else from first subject)
        $examRank = DB::table('exam_results')
            ->join('exam_subjects', 'exam_results.subject_id', '=', 'exam_subjects.id')
            ->join('exams', 'exam_subjects.exam_id', '=', 'exams.id')
            ->where('exam_results.candidate_id', $candidate->id)
            ->where('exams.campaign_id', $round->campaign_id)
            ->value('exam_results.rank');

        // Get assessment score
        $assessment = DB::table('assessment_responses')
            ->where('candidate_id', $candidate->id)
            ->select('total_score', 'passed')
            ->first();

        // Get investigation recommendation
        $investigation = DB::table('home_investigations')
            ->where('candidate_id', $candidate->id)
            ->select('recommendation', 'summary')
            ->first();

        // Get my vote for this candidate in this round
        $myVote = Vote::where('voting_round_id', $round->id)
            ->where('candidate_id', $candidate->id)
            ->where('member_id', request()->user()?->id)
            ->first();

        return [
            'id' => $candidate->id,
            'first_name' => $candidate->first_name,
            'last_name' => $candidate->last_name,
            'first_name_kh' => $candidate->first_name_kh,
            'last_name_kh' => $candidate->last_name_kh,
            'gender' => $candidate->gender,
            'phone' => $candidate->phone,
            'status' => $candidate->status,
            'province' => $candidate->province?->name,
            'ngo' => $candidate->referringNgo?->name,
            'school_name' => $candidate->school_name,
            'exam_score' => $examScore ? round((float) $examScore, 2) : null,
            'exam_rank' => $examRank ? (int) $examRank : null,
            'assessment_percent' => $assessment ? (float) $assessment->total_score : null,
            'assessment_passed' => $assessment ? (bool) $assessment->passed : null,
            'investigation_recommendation' => $investigation?->recommendation,
            'investigation_summary' => $investigation?->summary,
            'total_members' => $round->total_members,
            'my_vote' => $myVote ? [
                'decision' => $myVote->decision->value,
                'comment' => $myVote->comment,
                'voted_at' => $myVote->voted_at,
            ] : null,
        ];
    }
}
