<?php

namespace App\Http\Controllers\Api\AssessmentForm;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AssessmentForm\StoreAssessmentFormRequest;
use App\Http\Requests\Api\AssessmentForm\UpdateAssessmentFormRequest;
use App\Models\AssessmentForm;
use Illuminate\Http\JsonResponse;
use Services\AssessmentFormServices;

class AssessmentFormController extends Controller
{
    public function __construct(protected AssessmentFormServices $assessmentFormService) {}

    public function index(): JsonResponse
    {
        $assessmentForms = $this->assessmentFormService->list(request()->only(['campaign_id', 'per_page']));

        return ApiResponse::success($assessmentForms, 'Assessment forms retrieved successfully');
    }

    public function store(StoreAssessmentFormRequest $request): JsonResponse
    {
        $assessmentForm = $this->assessmentFormService->create($request->validated());

        return ApiResponse::created($assessmentForm, 'Assessment form created successfully');
    }

    public function show(AssessmentForm $assessmentForm): JsonResponse
    {
        return ApiResponse::success(
            $this->assessmentFormService->find($assessmentForm),
            'Assessment form retrieved successfully'
        );
    }

    public function update(UpdateAssessmentFormRequest $request, AssessmentForm $assessmentForm): JsonResponse
    {
        $assessmentForm = $this->assessmentFormService->update($assessmentForm, $request->validated());

        return ApiResponse::success($assessmentForm, 'Assessment form updated successfully');
    }

    public function destroy(AssessmentForm $assessmentForm): JsonResponse
    {
        $this->assessmentFormService->delete($assessmentForm);

        return ApiResponse::ok('Assessment form deleted successfully');
    }

    /**
     * Get the questions (fields) from an assessment form's schema.
     * GET /assessment-forms/{assessmentForm}/questions
     */
    public function questions(AssessmentForm $assessmentForm): JsonResponse
    {
        $questions = collect($assessmentForm->fields())->map(function ($field) {
            return [
                'id' => $field['key'] ?? null,
                'key' => $field['key'] ?? '',
                'label' => $field['label'] ?? '',
                'type' => $field['type'] ?? 'text',
                'weight' => $field['weight'] ?? 1,
                'order' => $field['order'] ?? 0,
                'options' => $field['options'] ?? null,
                'point_map' => $field['point_map'] ?? null,
                'rules' => $field['rules'] ?? ['required' => true],
            ];
        })->filter(function ($q) {
            return $q['key'] !== null && $q['key'] !== '';
        })->values();

        return ApiResponse::success($questions, 'Questions retrieved successfully');
    }
}
