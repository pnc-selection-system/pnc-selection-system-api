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
        // Direct query to return ALL subjects without ANY filter or global scope
        $subjects = ExamSubject::withoutGlobalScopes()
            ->select('id', 'campaign_id', 'name', 'max_score', 'weight', 'is_delete', 'created_at')
            ->with('campaign:id,name,status')
            ->with('rules')
            ->latest('id')
            ->get();

        return ApiResponse::success($subjects, 'Exam subjects retrieved successfully');
    }

    public function store(StoreExamSubjectRequest $request): JsonResponse
    {
        $subject = $this->examSubjectService->create($request->validated());

        return ApiResponse::created($subject, 'Exam subject created successfully');
    }

    public function show(int $id): JsonResponse
    {
        $examSubject = ExamSubject::withoutGlobalScopes()->findOrFail($id);

        return ApiResponse::success(
            $this->examSubjectService->find($examSubject),
            'Exam subject retrieved successfully'
        );
    }

    public function update(UpdateExamSubjectRequest $request, int $id): JsonResponse
    {
        $examSubject = ExamSubject::withoutGlobalScopes()->findOrFail($id);
        $subject = $this->examSubjectService->update($examSubject, $request->validated());

        return ApiResponse::success($subject, 'Exam subject updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $examSubject = ExamSubject::withoutGlobalScopes()->findOrFail($id);
        $this->examSubjectService->delete($examSubject);

        return ApiResponse::ok('Exam subject deleted successfully');
    }
}
