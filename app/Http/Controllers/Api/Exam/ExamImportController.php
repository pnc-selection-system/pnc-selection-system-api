<?php

namespace App\Http\Controllers\Api\Exam;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Exam\ExamImportUploadRequest;
use App\Http\Requests\Api\Exam\ExamImportValidateRequest;
use App\Models\ImportFile;
use Illuminate\Http\JsonResponse;
use Services\ExamImportServices;

class ExamImportController extends Controller
{
    public function __construct(protected ExamImportServices $importService) {}

    /**
     * POST /exam-import/upload
     *
     * Accept a CSV/XLSX file exported from ZipGrade, parse headers,
     * detect columns, store the raw file for audit, and return
     * detected columns + system fields so staff can map them.
     */
    public function upload(ExamImportUploadRequest $request): JsonResponse
    {
        try {
            $result = $this->importService->upload(
                $request->file('file'),
                (int) $request->input('campaign_id')
            );

            return ApiResponse::created(
                $result,
                'File uploaded successfully. ' . count($result['detected_columns']) . ' columns detected.'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError('File validation failed', $e->errors());
        }
    }

    /**
     * POST /exam-import/{importFile}/validate
     *
     * Accept a column mapping (system field → column index),
     * validate each row for score bounds, candidate matching,
     * and duplicate detection. Returns a per-row report for
     * staff review before final commit.
     */
    public function validate(ExamImportValidateRequest $request, ImportFile $importFile): JsonResponse
    {
        try {
            $result = $this->importService->validate(
                $importFile->id,
                $request->validated()['column_mapping']
            );

            $summary = $result['summary'];
            $message = "Validation complete: {$summary['valid_rows']} valid, {$summary['rows_with_errors']} with errors, {$summary['rows_with_warnings']} with warnings.";

            return ApiResponse::success($result, $message);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError('Validation failed', $e->errors());
        }
    }
}
