<?php

namespace App\Http\Controllers\Api\AssessmentForm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AssessmentForm\StoreAssessmentFormRequest;
use App\Http\Requests\Api\AssessmentForm\UpdateAssessmentFormRequest;
use App\Models\AssessmentForm;
use Illuminate\Http\JsonResponse;
use Services\AssessmentFormServices;

class AssessmentFormController extends Controller
{
    public function __construct(protected AssessmentFormServices $assessmentFormService)
    {
    }

    public function index(): JsonResponse
    {
        $assessmentForms = $this->assessmentFormService->list(request()->all());

        return response()->json([
            'success' => true,
            'message' => 'Assessment forms retrieved successfully',
            'data'    => $assessmentForms,
        ]);
    }

    public function store(StoreAssessmentFormRequest $request): JsonResponse
    {
        $assessmentForm = $this->assessmentFormService->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Assessment form created successfully',
            'data'    => $assessmentForm,
        ], 201);
    }

    public function show(AssessmentForm $assessmentForm): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Assessment form retrieved successfully',
            'data'    => $this->assessmentFormService->find($assessmentForm),
        ]);
    }

    public function update(UpdateAssessmentFormRequest $request, AssessmentForm $assessmentForm): JsonResponse
    {
        $assessmentForm = $this->assessmentFormService->update($assessmentForm, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Assessment form updated successfully',
            'data'    => $assessmentForm,
        ]);
    }

    public function destroy(AssessmentForm $assessmentForm): JsonResponse
    {
        $this->assessmentFormService->delete($assessmentForm);

        return response()->json([
            'success' => true,
            'message' => 'Assessment form deleted successfully',
        ]);
    }
}
