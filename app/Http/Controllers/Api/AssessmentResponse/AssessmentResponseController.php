<?php

namespace App\Http\Controllers\Api\AssessmentResponse;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Services\AssessmentResponseServices;

class AssessmentResponseController extends Controller
{
    public function __construct(protected AssessmentResponseServices $assessmentResponseService) {}

    // GET /api/assessment-responses?form_id=1&candidate_id=1
    public function index(): JsonResponse
    {
        $responses = $this->assessmentResponseService->list(request()->all());

        return ApiResponse::success($responses, 'Assessment responses retrieved successfully');
    }

    // GET /api/assessment-responses/candidate/{candidateId}
    public function show(int $candidateId): JsonResponse
    {
        $result = $this->assessmentResponseService->findByCandidate($candidateId);

        if (empty($result)) {
            return ApiResponse::notFound('No response found for this candidate');
        }

        return ApiResponse::success($result, 'Assessment response retrieved successfully');
    }

    // POST /api/assessment-responses/submit
    public function submit(Request $request): JsonResponse
    {
        $request->validate([
            'candidate_id'          => 'required|integer|exists:candidates,id',
            'answers'               => 'required|array|min:1',
            'answers.*.question_id' => 'required|integer|exists:assessment_questions,id',
            'answers.*.answer'      => 'required',
        ]);

        $result = $this->assessmentResponseService->submit(
            (int) $request->candidate_id,
            $request->answers
        );

        return ApiResponse::created($result, 'Assessment response submitted successfully');
    }

    // DELETE /api/assessment-responses/candidate/{candidateId}
    public function destroy(int $candidateId): JsonResponse
    {
        $this->assessmentResponseService->deleteByCandidate($candidateId);

        return ApiResponse::ok('Assessment response deleted successfully');
    }
}
