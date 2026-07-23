<?php

namespace App\Http\Controllers\Api\AssessmentResponse;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\AssessmentForm;
use App\Models\AssessmentResponse;
use App\Models\Candidate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AssessmentResponseController extends Controller
{
    /** Default pass threshold percentage */
    private const DEFAULT_PASS_THRESHOLD = 60;

    /**
     * Get the full name of a candidate from first_name + last_name.
     */
    private function getCandidateFullName(?Candidate $candidate): string
    {
        if (!$candidate) {
            return 'Unknown';
        }
        return trim($candidate->first_name . ' ' . $candidate->last_name) ?: 'Unknown';
    }

    /**
     * List assessment responses.
     * GET /assessment-responses
     */
    public function index(Request $request): JsonResponse
    {
        $query = AssessmentResponse::with(['candidate', 'form']);

        if ($candidateId = $request->get('candidate_id')) {
            $query->where('candidate_id', $candidateId);
        }

        if ($formId = $request->get('form_id')) {
            $query->where('form_id', $formId);
        }

        $perPage = (int) $request->get('per_page', 50);
        $paginator = $query->orderBy('created_at', 'desc')->paginate($perPage);

        $data = collect($paginator->items())->map(function ($response) {
            return [
                'id' => $response->id,
                'candidate_id' => $response->candidate_id,
                'candidate_name' => $this->getCandidateFullName($response->candidate),
                'form_id' => $response->form_id,
                'form_name' => $response->form?->name ?? 'Unknown',
                'answers' => $response->answers,
                'total_score' => $response->total_score,
                'passed' => $response->total_score !== null
                    ? $response->total_score >= self::DEFAULT_PASS_THRESHOLD
                    : null,
                'created_at' => $response->created_at?->toIso8601String(),
            ];
        });

        return ApiResponse::success($data, 'Assessment responses retrieved successfully');
    }

    /**
     * Submit an assessment response.
     * POST /assessment-responses/submit
     */
    public function submit(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'candidate_id' => 'required|integer|exists:candidates,id',
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|string',
            'answers.*.answer' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError('Validation failed', $validator->errors());
        }

        $validated = $validator->validated();

        // Find the active assessment form for this candidate's campaign
        $candidate = Candidate::find($validated['candidate_id']);
        if (!$candidate) {
            return ApiResponse::notFound('Candidate not found');
        }

        $campaignId = $candidate->campaign_id ?? $request->get('campaign_id');
        $assessmentForm = AssessmentForm::where('campaign_id', $campaignId)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$assessmentForm) {
            return ApiResponse::notFound('No assessment form found for this campaign');
        }

        // Build answer data keyed by field key
        $answerData = [];
        foreach ($validated['answers'] as $answer) {
            $answerData[$answer['question_id']] = $answer['answer'];
        }

        // Score the response
        $totalScore = $assessmentForm->scoreResponse($answerData);

        // Store the response
        $response = AssessmentResponse::create([
            'candidate_id' => $validated['candidate_id'],
            'form_id' => $assessmentForm->id,
            'answers' => $validated['answers'],
            'total_score' => $totalScore,
        ]);

        return ApiResponse::created([
            'id' => $response->id,
            'candidate_id' => $response->candidate_id,
            'total_score' => $totalScore,
            'passed' => $totalScore >= self::DEFAULT_PASS_THRESHOLD,
            'form_name' => $assessmentForm->name,
        ], 'Assessment submitted successfully');
    }
}
