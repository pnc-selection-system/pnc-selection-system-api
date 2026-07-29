<?php

namespace App\Http\Controllers\Api\ReportExport;

use App\Enums\ReportStatus;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ReportExport\StoreReportExportRequest;
use App\Models\ReportExport;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Services\ReportExportServices;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportExportController extends Controller
{
    public function __construct(protected ReportExportServices $reportExportService)
    {
    }

    public function index(): JsonResponse
    {
        $filters = request()->only(['status', 'campaign_id', 'per_page']);

        // Scope to current user unless they have Admin or Manager role
        $user = request()->user();
        $roleName = $user?->role?->name;
        $hasElevatedRole = $roleName && in_array($roleName, ['Admin', 'Manager']);
        if (!$hasElevatedRole) {
            $filters['user_id'] = $user?->id;
        }

        $reports = $this->reportExportService->list($filters);

        return ApiResponse::success($reports, 'Reports retrieved successfully');
    }

    public function store(StoreReportExportRequest $request): JsonResponse
    {
        try {
            $report = $this->reportExportService->create(
                $request->validated(),
                $request->user()->id
            );

            return ApiResponse::created($report, 'Report generated successfully');
        } catch (\Throwable $e) {
            Log::error('Report store failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return ApiResponse::error(
                'Failed to generate report: ' . $e->getMessage(),
                500
            );
        }
    }

    public function show(ReportExport $reportExport): JsonResponse
    {
        $user = request()->user();
        $roleName = $user?->role?->name;
        $hasElevatedRole = $roleName && in_array($roleName, ['Admin', 'Manager']);
        if (!$hasElevatedRole && $reportExport->user_id !== $user?->id) {
            return ApiResponse::forbidden('You do not have access to this report');
        }

        return ApiResponse::success(
            $this->reportExportService->find($reportExport),
            'Report retrieved successfully'
        );
    }

    public function destroy(ReportExport $reportExport): JsonResponse
    {
        $user = request()->user();
        $roleName = $user?->role?->name;
        $hasElevatedRole = $roleName && in_array($roleName, ['Admin', 'Manager']);
        if (!$hasElevatedRole && $reportExport->user_id !== $user?->id) {
            return ApiResponse::forbidden('You do not have access to this report');
        }

        $this->reportExportService->delete($reportExport);

        return ApiResponse::ok('Report deleted successfully');
    }

    /**
     * Download the generated report file.
     */
    public function download(ReportExport $reportExport): JsonResponse|BinaryFileResponse
    {
        $user = request()->user();
        $roleName = $user?->role?->name;
        $hasElevatedRole = $roleName && in_array($roleName, ['Admin', 'Manager']);
        if (!$hasElevatedRole && $reportExport->user_id !== $user?->id) {
            return ApiResponse::forbidden('You do not have access to this report');
        }

        if ($reportExport->status !== ReportStatus::Ready || !$reportExport->file_path) {
            return ApiResponse::error('Report is not ready for download', 400);
        }

        $fullPath = Storage::disk('public')->path($reportExport->file_path);

        if (!file_exists($fullPath)) {
            return ApiResponse::notFound('Report file not found');
        }

        $fileName = preg_replace(
            '/[^a-zA-Z0-9_\-\.]/',
            '_',
            $reportExport->report_name . '.' . pathinfo($reportExport->file_path, PATHINFO_EXTENSION)
        );

        return response()->download($fullPath, $fileName);
    }
}
