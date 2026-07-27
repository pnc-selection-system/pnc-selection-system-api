<?php

namespace App\Http\Controllers\Api\AssessmentForm;

use App\Enums\CandidateStatus;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\AssessmentForm;
use App\Models\AssessmentRespone;
use App\Models\Cadidate;
use App\Repositories\CandidateRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResponseController extends Controller
{
    /**
     * Get all responses for a given assessment form, with candidate info.
     * Supports optional search query parameter to filter by candidate name/code.
     */
    public function index(Request $request, AssessmentForm $assessmentForm): JsonResponse
    {
        $search = $request->input('search', '');

        $responses = AssessmentRespone::where('form_id', $assessmentForm->id)
            ->when($search, function ($query, $search) {
                $query->whereHas('candidate', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('id', 'like', "%{$search}%");
                });
            })
            ->with('candidate')
            ->latest()
            ->paginate($request->input('per_page', 50));

        // Map to frontend-friendly format
        $mapped = $responses->getCollection()->map(function ($r) use ($assessmentForm) {
            $candidate = $r->candidate;
            $threshold = (float) ($assessmentForm->pass_threshold ?? 60);

            return [
                'id' => $r->id,
                'candidate_id' => $r->candidate_id,
                'candidate_code' => $candidate?->code ?? 'C-'.str_pad($r->candidate_id, 4, '0', STR_PAD_LEFT),
                'candidate_name' => $candidate
                    ? trim(($candidate->first_name ?? '').' '.($candidate->last_name ?? ''))
                    : 'Unknown',
                'province' => null, // Could be loaded via relationship if needed
                'status' => $r->total_score >= $threshold ? 'passed' : 'failed',
                'form_id' => $r->form_id,
                'form_name' => $assessmentForm->name,
                'answers' => $this->mapAnswers($assessmentForm, $r->answers ?? []),
                'total_score' => (float) $r->total_score,
                'pass_threshold' => $threshold,
                'passed' => (float) $r->total_score >= $threshold,
                'submitted_at' => $r->created_at,
            ];
        });

        return ApiResponse::success([
            'data' => $mapped,
            'meta' => [
                'current_page' => $responses->currentPage(),
                'last_page' => $responses->lastPage(),
                'total' => $responses->total(),
            ],
        ], 'Responses retrieved successfully');
    }

    /**
     * Submit a response for a given assessment form.
     *
     * Expects JSON body:
     * {
     *   candidate_id: 123,
     *   answers: {
     *     "q_key_0": "answer_value",
     *     "q_key_1": "4"           // for rating 1-5
     *   }
     * }
     */
    public function store(Request $request, AssessmentForm $assessmentForm): JsonResponse
    {
        $request->validate([
            'candidate_id' => 'required|integer|exists:candidates,id',
            'answers' => 'required|array',
        ]);

        $answers = $request->input('answers', []);

        // Validate answers against the form's field rules
        $validator = $assessmentForm->validateResponse($answers);
        if ($validator->fails()) {
            return ApiResponse::validationError('Validation failed', $validator->errors());
        }

        // Calculate the score and pass/fail using the form's pass_threshold
        $score = $assessmentForm->scoreResponse($answers);
        $passThreshold = (float) ($assessmentForm->pass_threshold ?? 60);
        $passed = $score >= $passThreshold;

        // Persist response to database
        $response = AssessmentRespone::create([
            'candidate_id' => (int) $request->input('candidate_id'),
            'form_id' => $assessmentForm->id,
            'answers' => $answers,
            'total_score' => $score,
            'passed' => $passed,
            'submitted_by' => Auth::id(),
        ]);

        // Update candidate status based on assessment result and record history
        $candidate = Cadidate::find((int) $request->input('candidate_id'));
        if ($candidate) {
            $newStatus = $passed
                ? CandidateStatus::PassInterest->value
                : CandidateStatus::FailInterest->value;

            $candidate->update([
                'status' => $newStatus,
            ]);

            // Record status change in history
            app(CandidateRepository::class)->recordStatusHistory(
                $candidate->id,
                $newStatus,
                Auth::id()
            );
        }

        $responseData = [
            'id' => $response->id,
            'candidate_id' => $response->candidate_id,
            'form_id' => $response->form_id,
            'answers' => $response->answers,
            'total_score' => $score,
            'pass_threshold' => $passThreshold,
            'passed' => $passed,
            'submitted_at' => $response->created_at,
        ];

        return ApiResponse::success($responseData, 'Response submitted successfully');
    }

    /**
     * Map backend answers (key-value) to frontend-friendly AnswerRow format
     * using the form's schema to resolve question labels.
     */
    protected function mapAnswers(AssessmentForm $assessmentForm, array $answers): array
    {
        $fields = $assessmentForm->fields();
        $rows = [];

        foreach ($fields as $i => $field) {
            $key = $field['key'] ?? null;
            if ($key === null) {
                continue;
            }

            $rows[] = [
                'question_id' => $i,
                'label' => $field['label'] ?? $field['title'] ?? 'Question',
                'type' => $field['type'] ?? 'text',
                'answer' => $answers[$key] ?? null,
                'weight' => (float) ($field['weight'] ?? 1),
            ];
        }

        return $rows;
    }
}
