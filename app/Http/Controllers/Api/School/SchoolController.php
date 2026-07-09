<?php

namespace App\Http\Controllers\Api\School;

use App\Http\Controllers\Controller;
use App\Services\School\SchoolService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    public function __construct(protected SchoolService $service) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'province_id']);
        $perPage = $request->input('per_page', 15);
        
        $schools = $this->service->getPaginated($filters, $perPage);
        
        return response()->json([
            'success' => true,
            'message' => 'Schools retrieved successfully.',
            'data' => $schools->items(),
            'pagination' => [
                'total' => $schools->total(),
                'per_page' => $schools->perPage(),
                'current_page' => $schools->currentPage(),
                'last_page' => $schools->lastPage(),
                'from' => $schools->firstItem(),
                'to' => $schools->lastItem(),
            ]
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'province_id' => 'required|exists:provinces,id',
            'name' => 'required|string|max:150',
            'address' => 'nullable|string'
        ]);

        $school = $this->service->create($request->all());
        
        return response()->json([
            'success' => true,
            'message' => 'School created successfully.',
            'data' => $school
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'School retrieved successfully.',
            'data' => $this->service->findById($id)
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'province_id' => 'required|exists:provinces,id',
            'name' => 'required|string|max:150',
            'address' => 'nullable|string'
        ]);

        $school = $this->service->update($id, $request->all());
        
        return response()->json([
            'success' => true,
            'message' => 'School updated successfully.',
            'data' => $school
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return response()->json([
            'success' => true,
            'message' => 'School deleted successfully.'
        ], 200);
    }
}