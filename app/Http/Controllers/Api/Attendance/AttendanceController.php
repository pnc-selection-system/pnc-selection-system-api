<?php

namespace App\Http\Controllers\Api\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Resources\AttendanceResource;
use App\Services\Attendance\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(protected AttendanceService $service) {}

    public function store(Request $request, int $infoSession): JsonResponse
    {
        $request->validate([
            'total_students' => 'required|integer|min:0',
            'male_students' => 'required|integer|min:0',
            'female_students' => 'required|integer|min:0',
            'teachers_attended' => 'nullable|integer|min:0',
            'notes' => 'nullable|string'
        ]);

        $attendance = $this->service->createOrUpdate($infoSession, $request->all());
        
        return response()->json([
            'success' => true,
            'message' => 'Attendance recorded successfully.',
            'data' => new AttendanceResource($attendance)
        ], 201);
    }

    public function show(int $infoSession, int $attendance): JsonResponse
    {
        $attendance = $this->service->findByInfoSessionId($infoSession);
        
        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'No attendance record found for this session.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Attendance retrieved successfully.',
            'data' => new AttendanceResource($attendance)
        ]);
    }

    public function update(Request $request, int $infoSession, int $attendance): JsonResponse
    {
        $request->validate([
            'total_students' => 'required|integer|min:0',
            'male_students' => 'required|integer|min:0',
            'female_students' => 'required|integer|min:0',
            'teachers_attended' => 'nullable|integer|min:0',
            'notes' => 'nullable|string'
        ]);

        $attendance = $this->service->createOrUpdate($infoSession, $request->all());
        
        return response()->json([
            'success' => true,
            'message' => 'Attendance updated successfully.',
            'data' => new AttendanceResource($attendance)
        ]);
    }
}