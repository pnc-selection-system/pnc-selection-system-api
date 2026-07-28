<?php

namespace App\Http\Controllers\Api\AssessmentForm;

use App\Http\Controllers\Controller;
use App\Services\AssessmentFormService;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;

class AssessmentFormController extends Controller
{
    public function __construct(
        protected AssessmentFormService $service
    ) {}

    public function index(Request $request)
    {
        $forms = $this->service->list($request->only(['campaign_id']));
        return ApiResponse::success($forms, 'Assessment forms retrieved successfully.');
    }

    public function show(int $id)
    {
        $form = $this->service->find($id);
        return ApiResponse::success($form, 'Assessment form retrieved successfully.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'campaign_id' => 'required|integer|exists:selection_campaigns,id',
            'name' => 'required|string|max:255',
            'pass_threshold' => 'nullable|integer|min:0|max:100',
            'schema' => 'nullable|array',
            'schema.fields' => 'nullable|array',
            'schema.fields.*.key' => 'nullable|string',
            'schema.fields.*.label' => 'required|string',
            'schema.fields.*.type' => 'required|string',
            'schema.fields.*.weight' => 'nullable|integer',
            'schema.fields.*.options' => 'nullable|array',
            'schema.fields.*.point_map' => 'nullable|array',
        ]);

        $form = $this->service->store($data);
        return ApiResponse::created($form, 'Assessment form created successfully.');
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'name' => 'nullable|string|max:255',
            'pass_threshold' => 'nullable|integer|min:0|max:100',
            'schema' => 'nullable|array',
            'schema.fields' => 'nullable|array',
            'schema.fields.*.key' => 'nullable|string',
            'schema.fields.*.label' => 'required_with:schema.fields|string',
            'schema.fields.*.type' => 'required_with:schema.fields|string',
            'schema.fields.*.weight' => 'nullable|integer',
            'schema.fields.*.options' => 'nullable|array',
            'schema.fields.*.point_map' => 'nullable|array',
        ]);

        $form = $this->service->update($id, $data);
        return ApiResponse::success($form, 'Assessment form updated successfully.');
    }

    public function questions(int $id)
    {
        $form = $this->service->find($id);
        return ApiResponse::success($form->questions, 'Questions retrieved successfully.');
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
