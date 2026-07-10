<?php

namespace App\Http\Controllers\Api\InterestStudent;

use App\Http\Controllers\Controller;
use App\Models\InterestStudent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InterestStudentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'info_session_id', 'status']);
        $perPage = $request->input('per_page', 15);

        $query = InterestStudent::query()->with(['infoSession', 'convertedToCandidate']);

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('full_name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('phone', 'like', '%'.$filters['search'].'%')
                    ->orWhere('email', 'like', '%'.$filters['search'].'%');
            });
        }

        if (! empty($filters['info_session_id'])) {
            $query->where('info_session_id', (int) $filters['info_session_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $students = $query->latest()->paginate($perPage);
        
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
        $data = $request->validate([
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

        $student = InterestStudent::create($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Interested student registered successfully.',
            'data' => $student
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $student = InterestStudent::with(['infoSession', 'convertedToCandidate'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'message' => 'Interested student retrieved successfully.',
            'data' => $student
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'info_session_id' => 'sometimes|exists:information_sessions,id',
            'full_name' => 'sometimes|string|max:150',
            'gender' => 'sometimes|string|max:20',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
            'current_grade' => 'nullable|string|max:50',
            'school_name' => 'nullable|string|max:150',
            'preferred_major' => 'nullable|string|max:150',
            'notes' => 'nullable|string',
            'status' => 'nullable|string|in:interested,contacted,application_started,application_submitted,converted'
        ]);

        $student = InterestStudent::findOrFail($id);
        $student->update($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Interested student updated successfully.',
            'data' => $student
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        InterestStudent::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Interested student deleted successfully.'
        ], 200);
    }
}
