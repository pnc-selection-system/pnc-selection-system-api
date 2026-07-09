<?php

namespace App\Http\Controllers\Api\Report;

use App\Http\Controllers\Controller;
use App\Services\Report\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(protected ReportService $service) {}

    public function sessionStatistics(Request $request): JsonResponse
    {
        $filters = $request->only(['campaign_id', 'province_id', 'school_id', 'date_from', 'date_to']);
        $stats = $this->service->getSessionStatistics($filters);
        
        return response()->json([
            'success' => true,
            'message' => 'Session statistics retrieved successfully.',
            'data' => $stats
        ]);
    }

    public function attendanceStatistics(Request $request): JsonResponse
    {
        $filters = $request->only(['campaign_id', 'province_id', 'school_id', 'date_from', 'date_to']);
        $stats = $this->service->getAttendanceStatistics($filters);
        
        return response()->json([
            'success' => true,
            'message' => 'Attendance statistics retrieved successfully.',
            'data' => $stats
        ]);
    }

    public function conversionStatistics(Request $request): JsonResponse
    {
        $filters = $request->only(['campaign_id', 'province_id', 'school_id', 'date_from', 'date_to']);
        $stats = $this->service->getConversionStatistics($filters);
        
        return response()->json([
            'success' => true,
            'message' => 'Conversion statistics retrieved successfully.',
            'data' => $stats
        ]);
    }

    public function dashboard(Request $request): JsonResponse
    {
        $filters = $request->only(['campaign_id', 'date_from', 'date_to']);
        $dashboard = $this->service->getDashboardData($filters);
        
        return response()->json([
            'success' => true,
            'message' => 'Dashboard data retrieved successfully.',
            'data' => $dashboard
        ]);
    }
}