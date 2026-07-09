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
        $assessmentForms = $this->assessmentFormService->list(request()->all());

<<<<<<< HEAD
        return response()->json([
            'success' => true,
            'message' => 'Assessment forms retrieved successfully',
            'data' => $assessmentForms,
        ]);
=======
        return ApiResponse::success($assessmentForms, 'Assessment forms retrieved successfully');
>>>>>>> 8b829ca40b1863c5165ba13b969ca533aef058cc
    }

    public function store(StoreAssessmentFormRequest $request): JsonResponse
    {
        $assessmentForm = $this->assessmentFormService->create($request->validated());

<<<<<<< HEAD
        return response()->json([
            'success' => true,
            'message' => 'Assessment form created successfully',
            'data' => $assessmentForm,
        ], 201);
=======
        return ApiResponse::created($assessmentForm, 'Assessment form created successfully');
>>>>>>> 8b829ca40b1863c5165ba13b969ca533aef058cc
    }

    public function show(AssessmentForm $assessmentForm): JsonResponse
    {
<<<<<<< HEAD
        return response()->json([
            'success' => true,
            'message' => 'Assessment form retrieved successfully',
            'data' => $this->assessmentFormService->find($assessmentForm),
        ]);
=======
        return ApiResponse::success(
            $this->assessmentFormService->find($assessmentForm),
            'Assessment form retrieved successfully'
        );
>>>>>>> 8b829ca40b1863c5165ba13b969ca533aef058cc
    }

    public function update(UpdateAssessmentFormRequest $request, AssessmentForm $assessmentForm): JsonResponse
    {
        $assessmentForm = $this->assessmentFormService->update($assessmentForm, $request->validated());

<<<<<<< HEAD
        return response()->json([
            'success' => true,
            'message' => 'Assessment form updated successfully',
            'data' => $assessmentForm,
        ]);
=======
        return ApiResponse::success($assessmentForm, 'Assessment form updated successfully');
>>>>>>> 8b829ca40b1863c5165ba13b969ca533aef058cc
    }

    public function destroy(AssessmentForm $assessmentForm): JsonResponse
    {
        $this->assessmentFormService->delete($assessmentForm);

        return ApiResponse::ok('Assessment form deleted successfully');
    }
}
