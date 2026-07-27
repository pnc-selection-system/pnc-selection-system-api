<?php

namespace App\Http\Controllers\Api\Candidate;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Candidate\ImportCandidateConfirmRequest;
use App\Http\Requests\Api\Candidate\ImportCandidateUploadRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Services\ImportCandidateServices;

class ImportCandidateController extends Controller
{
    public function __construct(protected ImportCandidateServices $importService) {}

    /**
     * Upload a CSV or Excel file and preview the data.
     *
     * @POST /api/candidates/import/upload
     */
    public function upload(ImportCandidateUploadRequest $request): JsonResponse
    {
        try {
            $result = $this->importService->parseUploadedFile(
                $request->file('file'),
                (int) $request->input('campaign_id'),
                (int) $request->input('province_id'),
                (int) $request->user()->id
            );

            return ApiResponse::success($result, 'File parsed successfully. Review the columns and mapping before confirming.');
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 422);
        }
    }

    /**
     * Confirm the import with the provided column mapping.
     *
     * @POST /api/candidates/import/confirm
     */
    public function confirm(ImportCandidateConfirmRequest $request): JsonResponse
    {
        try {
            $importFile = \App\Models\ImportFile::findOrFail($request->input('import_file_id'));

            $result = $this->importService->confirmImport(
                (int) $request->input('import_file_id'),
                $request->input('column_mapping'),
                (int) $importFile->campaign_id,
                (int) $importFile->province_id
            );

            $message = "{$result['imported']} candidate(s) imported successfully.";

            if (count($result['errors']) > 0) {
                $message .= ' Some rows had errors.';
            }

            return ApiResponse::success($result, $message);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 422);
        }
    }
}
