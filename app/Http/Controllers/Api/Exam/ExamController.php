<?php

namespace App\Http\Controllers\Api\Exam;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Exam\StoreExamRequest;
use App\Http\Requests\Api\Exam\UpdateExamRequest;
use App\Models\Exam;
use Illuminate\Http\JsonResponse;
use Services\ExamServices;

class ExamController extends Controller
{
    public function __construct(protected ExamServices $examService) {}

    public function index(): JsonResponse
    {
        $exams = $this->examService->list(request()->all());

<<<<<<< HEAD
        return response()->json([
            'success' => true,
            'message' => 'Exams retrieved successfully',
            'data' => $exams,
        ]);
=======
        return ApiResponse::success($exams, 'Exams retrieved successfully');
>>>>>>> 8b829ca40b1863c5165ba13b969ca533aef058cc
    }

    public function store(StoreExamRequest $request): JsonResponse
    {
        $exam = $this->examService->create($request->validated());

<<<<<<< HEAD
        return response()->json([
            'success' => true,
            'message' => 'Exam created successfully',
            'data' => $exam,
        ], 201);
=======
        return ApiResponse::created($exam, 'Exam created successfully');
>>>>>>> 8b829ca40b1863c5165ba13b969ca533aef058cc
    }

    public function show(Exam $exam): JsonResponse
    {
<<<<<<< HEAD
        return response()->json([
            'success' => true,
            'message' => 'Exam retrieved successfully',
            'data' => $this->examService->find($exam),
        ]);
=======
        return ApiResponse::success(
            $this->examService->find($exam),
            'Exam retrieved successfully'
        );
>>>>>>> 8b829ca40b1863c5165ba13b969ca533aef058cc
    }

    public function update(UpdateExamRequest $request, Exam $exam): JsonResponse
    {
        $exam = $this->examService->update($exam, $request->validated());

<<<<<<< HEAD
        return response()->json([
            'success' => true,
            'message' => 'Exam updated successfully',
            'data' => $exam,
        ]);
=======
        return ApiResponse::success($exam, 'Exam updated successfully');
>>>>>>> 8b829ca40b1863c5165ba13b969ca533aef058cc
    }

    public function destroy(Exam $exam): JsonResponse
    {
        $this->examService->delete($exam);

        return ApiResponse::ok('Exam deleted successfully');
    }
}
