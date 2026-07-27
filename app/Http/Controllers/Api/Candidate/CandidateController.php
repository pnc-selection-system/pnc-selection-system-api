<?php

namespace App\Http\Controllers\Api\Candidate;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Candidate\StoreCandidateRequest;
use App\Http\Requests\Api\Candidate\UpdateCandidateRequest;
use App\Models\AssessmentRespone;
use App\Models\Cadidate;
use App\Models\CandidateStatusHistory;
use Illuminate\Http\JsonResponse;
use Services\CandidateServices;

class CandidateController extends Controller
{
    public function __construct(protected CandidateServices $candidateService) {}

    public function index(): JsonResponse
    {
        $candidates = $this->candidateService->list(request()->all());

        return ApiResponse::success($candidates, 'Candidates retrieved successfully');
    }

    public function store(StoreCandidateRequest $request): JsonResponse
    {
        $candidate = $this->candidateService->create($request->validated());

        return ApiResponse::created($candidate, 'Candidate created successfully');
    }

    public function show(Cadidate $candidate): JsonResponse
    {
        return ApiResponse::success(
            $this->candidateService->find($candidate),
            'Candidate retrieved successfully'
        );
    }

    public function update(UpdateCandidateRequest $request, Cadidate $candidate): JsonResponse
    {
        $candidate = $this->candidateService->update($candidate, $request->validated());

        return ApiResponse::success($candidate, 'Candidate updated successfully');
    }

    public function destroy(Cadidate $candidate): JsonResponse
    {
        $this->candidateService->delete($candidate);

        return ApiResponse::ok('Candidate deleted successfully');
    }

    /**
     * Get the latest interest assessment result for a candidate.
     */
    public function assessmentResult(Cadidate $candidate): JsonResponse
    {
        $latestResponse = AssessmentRespone::where('candidate_id', $candidate->id)
            ->with(['form', 'submittedBy'])
            ->latest()
            ->first();

        if (!$latestResponse) {
            return ApiResponse::success(null, 'No assessment found for this candidate');
        }

        // Determine evaluator name from the submittedBy relationship
        // For new assessments, this is the authenticated user who submitted via ResponseController::store()
        // For old records (before submitted_by column existed), this will be null → 'System'
        $evaluatedBy = $latestResponse->submittedBy?->name ?? 'System';

        return ApiResponse::success([
            'total_score' => (float) $latestResponse->total_score,
            'passed' => $latestResponse->passed,
            'form_name' => $latestResponse->form?->name ?? 'Interest Assessment',
            'pass_threshold' => (float) ($latestResponse->form?->pass_threshold ?? 60),
            'submitted_at' => $latestResponse->created_at?->toIso8601String(),
            'evaluated_by' => $evaluatedBy,
        ], 'Assessment result retrieved successfully');
    }

    /**
     * Get status history for a candidate.
     * If no history exists yet (e.g. for candidates created before this feature),
     * auto-create an initial entry based on their current status and created_at date.
     */
    public function statusHistory(Cadidate $candidate): JsonResponse
    {
        $existingHistory = CandidateStatusHistory::where('candidate_id', $candidate->id)
            ->with('changedBy')
            ->orderBy('changed_at', 'asc')
            ->get();

        // If no history exists, auto-create entries from candidate's current data
        // This handles candidates created before the status history feature existed
        if ($existingHistory->isEmpty()) {
            $now = now();
            $currentStatus = $candidate->status ?? 'Register';

            // Always record the initial "Register" at creation time
            CandidateStatusHistory::create([
                'candidate_id' => $candidate->id,
                'status' => 'Register',
                'changed_by' => null,
                'changed_at' => $candidate->created_at ?? $now,
            ]);

            // If current status differs from Register, record that change too
            if ($currentStatus !== 'Register') {
                CandidateStatusHistory::create([
                    'candidate_id' => $candidate->id,
                    'status' => $currentStatus,
                    'changed_by' => null,
                    'changed_at' => $now,
                ]);
            }

            // Re-fetch to get the newly created entries
            $existingHistory = CandidateStatusHistory::where('candidate_id', $candidate->id)
                ->with('changedBy')
                ->orderBy('changed_at', 'asc')
                ->get();
        }

        $history = $existingHistory->map(function ($entry) {
            return [
                'id' => $entry->id,
                'status' => $entry->status,
                'changed_by' => $entry->changedBy?->name ?? 'System',
                'changed_at' => $entry->changed_at->toIso8601String(),
            ];
        });

        return ApiResponse::success($history, 'Status history retrieved successfully');
    }
}
