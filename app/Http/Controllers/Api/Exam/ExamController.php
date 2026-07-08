<?php

namespace App\Http\Controllers\Api\Exam;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Exam\StoreExamRequest;
use App\Http\Requests\Api\Exam\UpdateExamRequest;
use App\Models\Exam;
use Illuminate\Http\JsonResponse;
use Services\ExamServices;

class ExamController extends Controller
{
    public function __construct(protected ExamServices $examService)
    {
    }

    public function index(): JsonResponse
    {
        $exams = $this->examService->list(request()->all());

        return response()->json([
            'success' => true,
            'message' => 'Exams retrieved successfully',
            'data'    => $exams,
        ]);
    }

    public function store(StoreExamRequest $request): JsonResponse
    {
        $exam = $this->examService->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Exam created successfully',
            'data'    => $exam,
        ], 201);
    }

    public function show(Exam $exam): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Exam retrieved successfully',
            'data'    => $this->examService->find($exam),
        ]);
    }

    public function update(UpdateExamRequest $request, Exam $exam): JsonResponse
    {
        $exam = $this->examService->update($exam, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Exam updated successfully',
            'data'    => $exam,
        ]);
    }

    public function destroy(Exam $exam): JsonResponse
    {
        $this->examService->delete($exam);

        return response()->json([
            'success' => true,
            'message' => 'Exam deleted successfully',
        ]);
    }
}
