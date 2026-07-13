<?php

namespace App\Http\Controllers\Api\Exam;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Exam\StoreExamSubjectRequest;
use App\Http\Requests\Api\Exam\UpdateExamSubjectRequest;
use App\Models\ExamSubject;
use Illuminate\Http\JsonResponse;
use Services\ExamSubjectServices;

class ExamSubjectController extends Controller
{
    public function __construct(protected ExamSubjectServices $examSubjectService) {}

    /**
     * List all exam subjects (optional ?campaign_id= filter).
     */
    public function index(): JsonResponse
    {
        $subjects = $this->examSubjectService->list(request()->all());

        return ApiResponse::success($subjects, 'Exam subjects retrieved successfully');
    }

    /**
     * Create a new exam subject.
     */
    public function store(StoreExamSubjectRequest $request): JsonResponse
    {
        $subject = $this->examSubjectService->create($request->validated());

        return ApiResponse::created($subject, 'Exam subject created successfully');
    }

    /**
     * Show a single exam subject.
     */
    public function show(ExamSubject $examSubject): JsonResponse
    {
        return ApiResponse::success(
            $this->examSubjectService->find($examSubject),
            'Exam subject retrieved successfully'
        );
    }

    /**
     * Update an exam subject.
     */
    public function update(UpdateExamSubjectRequest $request, ExamSubject $examSubject): JsonResponse
    {
        $subject = $this->examSubjectService->update($examSubject, $request->validated());

        return ApiResponse::success($subject, 'Exam subject updated successfully');
    }

    /**
     * Delete an exam subject.
     */
    public function destroy(ExamSubject $examSubject): JsonResponse
    {
        $this->examSubjectService->delete($examSubject);

        return ApiResponse::ok('Exam subject deleted successfully');
    }

    /**
     * Validate that subject weights for a campaign sum to 100%.
     * Used before publishing an exam or campaign.
     */
    public function validateWeights(int $campaignId): JsonResponse
    {
        try {
            $this->examSubjectService->validateForPublish($campaignId);

            return ApiResponse::ok('Subject weights sum to 100% — ready to publish');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError(
                'Weight validation failed',
                $e->errors()
            );
        }
    }
}
