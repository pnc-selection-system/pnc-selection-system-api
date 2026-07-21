<?php

namespace App\Http\Controllers\Api\ExamSubject;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ExamSubject\StoreExamSubjectRequest;
use App\Http\Requests\Api\ExamSubject\UpdateExamSubjectRequest;
use App\Models\ExamSubject;
use Illuminate\Http\JsonResponse;
use Services\ExamSubjectServices;

class ExamSubjectController extends Controller
{
    public function __construct(protected ExamSubjectServices $examSubjectService)
    {
    }

    public function index(): JsonResponse
    {
        $subjects = $this->examSubjectService->list(request()->only(['campaign_id', 'per_page']));

        return ApiResponse::success($subjects, 'Exam subjects retrieved successfully');
    }

    public function store(StoreExamSubjectRequest $request): JsonResponse
    {
        $subject = $this->examSubjectService->create($request->validated());

        return ApiResponse::created($subject, 'Exam subject created successfully');
    }

    public function show(ExamSubject $examSubject): JsonResponse
    {
        return ApiResponse::success(
            $this->examSubjectService->find($examSubject),
            'Exam subject retrieved successfully'
        );
    }

    public function update(UpdateExamSubjectRequest $request, ExamSubject $examSubject): JsonResponse
    {
        $subject = $this->examSubjectService->update($examSubject, $request->validated());

        return ApiResponse::success($subject, 'Exam subject updated successfully');
    }

    public function destroy(ExamSubject $examSubject): JsonResponse
    {
        $this->examSubjectService->delete($examSubject);

        return ApiResponse::ok('Exam subject deleted successfully');
    }
}
