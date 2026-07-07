<?php

namespace App\Http\Controllers\Api\Exam;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Services\ExamServices;

class ExamController extends Controller
{
    public function __construct(protected ExamServices $examService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $exams = $this->examService->list($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Exams retrieved successfully',
            'data'    => $exams,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'campaign_id'    => 'required|integer|exists:selection_campaigns,id',
            'exam_date'      => 'required|date',
            'publish_status' => 'sometimes|boolean',
        ]);

        $exam = $this->examService->create($validated);

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

    public function update(Request $request, Exam $exam): JsonResponse
    {
        $validated = $request->validate([
            'campaign_id'    => 'sometimes|required|integer|exists:selection_campaigns,id',
            'exam_date'      => 'sometimes|required|date',
            'publish_status' => 'sometimes|boolean',
        ]);

        $exam = $this->examService->update($exam, $validated);

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
