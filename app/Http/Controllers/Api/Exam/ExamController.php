<?php

namespace App\Http\Controllers\Api\Exam;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Exam;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Services\ExamServices;

class ExamController extends Controller
{
    public function __construct(protected ExamServices $examService) {}

    public function index(): JsonResponse
    {
        $exams = $this->examService->list(request()->all());

        return ApiResponse::success($exams, 'Exams retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $exam = $this->examService->create($request->validate([
            'campaign_id' => ['required', 'integer', 'exists:selection_campaigns,id'],
            'exam_date' => ['required', 'date'],
            'publish_status' => ['sometimes', 'boolean'],
        ]));

        return ApiResponse::created($exam, 'Exam created successfully');
    }

    public function show(Exam $exam): JsonResponse
    {
        return ApiResponse::success(
            $this->examService->find($exam),
            'Exam retrieved successfully'
        );
    }

    public function update(Request $request, Exam $exam): JsonResponse
    {
        $exam = $this->examService->update($exam, $request->validate([
            'campaign_id' => ['sometimes', 'integer', 'exists:selection_campaigns,id'],
            'exam_date' => ['sometimes', 'date'],
            'publish_status' => ['sometimes', 'boolean'],
        ]));

        return ApiResponse::success($exam, 'Exam updated successfully');
    }

    public function destroy(Exam $exam): JsonResponse
    {
        $this->examService->delete($exam);

        return ApiResponse::ok('Exam deleted successfully');
    }
}
