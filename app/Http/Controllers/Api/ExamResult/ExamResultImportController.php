<?php

namespace App\Http\Controllers\Api\ExamResult;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ExamResult\ImportExamResultUploadRequest;
use App\Http\Requests\Api\ExamResult\ImportExamResultValidateRequest;
use App\Models\ExamSubject;
use App\Models\ImportExamResult;
use App\Models\SelectCampaing;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
     * Get import history for a given subject and campaign.
     *
     * @GET /api/exam-results/import/history
     */
    public function history(Request $request): JsonResponse
    {
        try {
            $query = ImportExamResult::with(['subject', 'importer'])
                ->orderBy('created_at', 'desc');

            if ($request->has('subject_id')) {
                $query->where('subject_id', (int) $request->input('subject_id'));
            }
            if ($request->has('campaign_id')) {
                $query->where('campaign_id', (int) $request->input('campaign_id'));
            }

            $history = $query->get()->map(function ($item) {
                return [
                    'id'             => $item->id,
                    'import_file_id' => $item->import_file_id,
                    'subject_id'     => $item->subject_id,
                    'subject_name'   => $item->subject?->name ?? 'Unknown',
                    'campaign_id'    => $item->campaign_id,
                    'imported_by'    => $item->importer?->name ?? 'System',
                    'total_rows'     => $item->total_rows,
                    'imported_rows'  => $item->imported_rows,
                    'errored_rows'   => $item->errored_rows,
                    'status'         => $item->status,
                    'created_at'     => $item->created_at?->toIso8601String(),
                ];
            });

            return ApiResponse::success($history, 'Import history retrieved.');
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 422);
        }
    }

    public function confirm(Request $request): JsonResponse
    {
        try {
            $importFileId = (int) ($request->input('import_file_id') ?: 0);
            $subjectId    = (int) ($request->input('subject_id') ?: 0);
            $importFile   = $importFileId ? \App\Models\ImportFile::find($importFileId) : null;

            $result = $this->importService->confirmImport(
                $importFileId,
                $request->input('column_mapping', []),
                $importFile ? (int) $importFile->campaign_id : 0,
                $subjectId
            );

            // Load subject and campaign names for the frontend
            $subject  = ExamSubject::withoutGlobalScopes()->find($subjectId);
            $campaign = $importFile ? SelectCampaing::find($importFile->campaign_id) : null;

            $result['subject_name']  = $subject?->name ?? 'Unknown';
            $result['campaign_name'] = $campaign?->name ?? 'Unknown';
            $result['file_name']     = $importFile?->original_filename ?? 'Unknown';

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
