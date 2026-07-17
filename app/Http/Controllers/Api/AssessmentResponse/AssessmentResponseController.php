<?php

namespace App\Http\Controllers\Api\AssessmentResponse;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AssessmentResponse\StoreAssessmentResponseRequest;
use App\Http\Requests\Api\AssessmentResponse\UpdateAssessmentResponseRequest;
use App\Models\AssessmentRespone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Services\AssessmentResponseServices;

class AssessmentResponseController extends Controller
{
    public function __construct(protected AssessmentResponseServices $assessmentResponseService) {}

    public function index(): JsonResponse
    {
        $responses = $this->assessmentResponseService->list(request()->all());

        return ApiResponse::success($responses, 'Assessment responses retrieved successfully');
    }

    public function store(StoreAssessmentResponseRequest $request): JsonResponse
    {
        $response = $this->assessmentResponseService->create($request->validated());

        return ApiResponse::created($response, 'Assessment response created successfully');
    }

    public function show(AssessmentRespone $response): JsonResponse
    {
        return ApiResponse::success(
            $this->assessmentResponseService->find($response),
            'Assessment response retrieved successfully'
        );
    }

    public function update(UpdateAssessmentResponseRequest $request, AssessmentRespone $response): JsonResponse
    {
        $response = $this->assessmentResponseService->update($response, $request->validated());

        return ApiResponse::success($response, 'Assessment response updated successfully');
    }

    public function destroy(AssessmentRespone $response): JsonResponse
    {
        $this->assessmentResponseService->delete($response);

        return ApiResponse::ok('Assessment response deleted successfully');
    }

    public function submit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'form_id'      => 'required|integer|exists:assessment_forms,id',
            'candidate_id' => 'required|integer|exists:candidates,id',
            'answers'      => 'required|array',
        ]);

        try {
            $response = $this->assessmentResponseService->submit(
                $validated['form_id'],
                $validated['candidate_id'],
                $validated['answers']
            );
        } catch (ValidationException $e) {
            return ApiResponse::validationError('Invalid answers', $e->errors());
        }

        $form = $response->form;
        $passed = $form ? (float) $response->total_score >= (float) $form->pass_threshold : null;

        return ApiResponse::created([
            'id'           => $response->id,
            'candidate_id' => $response->candidate_id,
            'form_id'      => $response->form_id,
            'answers'      => $response->answers,
            'total_score'  => (float) $response->total_score,
            'passed'       => $passed,
            'pass_threshold' => $form ? (float) $form->pass_threshold : null,
        ], 'Assessment response submitted successfully');
    }
}
