<?php

namespace App\Http\Controllers\Api\InterestStudent;

use App\Http\Controllers\Controller;
use App\Services\InterestStudent\InterestStudentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InterestStudentController extends Controller
{
    public function __construct(protected InterestStudentService $service) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'info_session_id', 'status']);
        $perPage = $request->input('per_page', 15);
        
        $students = $this->service->getPaginated($filters, $perPage);
        
        return response()->json([
            'success' => true,
            'message' => 'Interested students retrieved successfully.',
            'data' => $students->items(),
            'pagination' => [
                'total' => $students->total(),
                'per_page' => $students->perPage(),
                'current_page' => $students->currentPage(),
                'last_page' => $students->lastPage(),
                'from' => $students->firstItem(),
                'to' => $students->lastItem(),
            ]
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'info_session_id' => 'required|exists:information_sessions,id',
            'full_name' => 'required|string|max:150',
            'gender' => 'required|string|max:20',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
            'current_grade' => 'nullable|string|max:50',
            'school_name' => 'nullable|string|max:150',
            'preferred_major' => 'nullable|string|max:150',
            'notes' => 'nullable|string'
        ]);

        $student = $this->service->create($request->all());
        
        return response()->json([
            'success' => true,
            'message' => 'Interested student registered successfully.',
            'data' => $student
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $student = $this->service->findById($id);
        
        return response()->json([
            'success' => true,
            'message' => 'Interested student retrieved successfully.',
            'data' => $student
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'info_session_id' => 'required|exists:information_sessions,id',
            'full_name' => 'required|string|max:150',
            'gender' => 'required|string|max:20',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
            'current_grade' => 'nullable|string|max:50',
            'school_name' => 'nullable|string|max:150',
            'preferred_major' => 'nullable|string|max:150',
            'notes' => 'nullable|string',
            'status' => 'nullable|string|in:interested,contacted,application_started,application_submitted,converted'
        ]);

        $student = $this->service->update($id, $request->all());
        
        return response()->json([
            'success' => true,
            'message' => 'Interested student updated successfully.',
            'data' => $student
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return response()->json([
            'success' => true,
            'message' => 'Interested student deleted successfully.'
        ], 200);
    }
}