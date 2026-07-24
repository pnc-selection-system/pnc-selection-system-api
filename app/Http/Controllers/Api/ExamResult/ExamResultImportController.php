<?php

namespace App\Http\Controllers\Api\ExamResult;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ExamResult\ImportExamResultUploadRequest;
use App\Http\Requests\Api\ExamResult\ImportExamResultConfirmRequest;
use App\Http\Requests\Api\ExamResult\ImportExamResultValidateRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Services\ExamResultImportService;

class ExamResultImportController extends Controller
{
    public function __construct(protected ExamResultImportService $importService) {}

    /**
     * Upload a CSV or Excel file and preview the data.
     *
     * @POST /api/exam-results/import/upload
     */
    public function upload(ImportExamResultUploadRequest $request): JsonResponse
    {
        try {
            $result = $this->importService->parseUploadedFile(
                $request->file('file'),
                (int) $request->input('campaign_id'),
                (int) $request->input('subject_id'),
                (int) $request->user()->id
            );

            return ApiResponse::success($result, 'File parsed successfully. Review the columns and mapping before confirming.');
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 422);
        }
    }

    /**
     * Validate all rows with the provided column mapping.
     *
     * @POST /api/exam-results/import/validate
     */
    public function validate(ImportExamResultValidateRequest $request): JsonResponse
    {
        try {
            $importFile = \App\Models\ImportFile::findOrFail($request->input('import_file_id'));

            $result = $this->importService->validateImport(
                (int) $request->input('import_file_id'),
                $request->input('column_mapping'),
                (int) $importFile->campaign_id,
                (int) $request->input('subject_id')
            );

            return ApiResponse::success($result, 'Validation completed.');
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 422);
        }
    }

    /**
     * Confirm the import with the provided column mapping.
     *
     * @POST /api/exam-results/import/confirm
     */
    public function confirm(ImportExamResultConfirmRequest $request): JsonResponse
    {
        try {
            $importFile = \App\Models\ImportFile::findOrFail($request->input('import_file_id'));

            $result = $this->importService->confirmImport(
                (int) $request->input('import_file_id'),
                $request->input('column_mapping'),
                (int) $importFile->campaign_id,
                (int) $request->input('subject_id')
            );

            $message = "{$result['imported']} exam result(s) imported successfully.";

            if (count($result['errors']) > 0) {
                $message .= ' Some rows had errors.';
            }

            return ApiResponse::success($result, $message);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 422);
        }
    }
}
