<?php

namespace App\Http\Controllers\Api\InformationSession;

use App\Http\Controllers\Controller;
use App\Services\InformationSession\InfoSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InfoSessionController extends Controller
{
    public function __construct(protected InfoSessionService $service) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'campaign_id', 'province_id', 'school_id', 'session_date_from', 'session_date_to']);
        $perPage = $request->input('per_page', 15);
        
        $sessions = $this->service->getPaginated($filters, $perPage);
        
        return response()->json([
            'success' => true,
            'message' => 'Information sessions retrieved successfully.',
            'data' => $sessions->items(),
            'pagination' => [
                'total' => $sessions->total(),
                'per_page' => $sessions->perPage(),
                'current_page' => $sessions->currentPage(),
                'last_page' => $sessions->lastPage(),
                'from' => $sessions->firstItem(),
                'to' => $sessions->lastItem(),
            ]
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'campaign_id' => 'required|exists:selection_campaigns,id',
            'province_id' => 'required|exists:provinces,id',
            'school_id' => 'required|exists:schools,id',
            'session_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'location' => 'required|string|max:255',
            'hosted_by' => 'nullable|string|max:150',
            'description' => 'nullable|string',
            'expected_attendance' => 'nullable|integer|min:0'
        ]);

        $session = $this->service->create($request->all());
        
        return response()->json([
            'success' => true,
            'message' => 'Information session created successfully.',
            'data' => $session
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $session = $this->service->findById($id);
        
        return response()->json([
            'success' => true,
            'message' => 'Information session retrieved successfully.',
            'data' => $session
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'campaign_id' => 'required|exists:selection_campaigns,id',
            'province_id' => 'required|exists:provinces,id',
            'school_id' => 'required|exists:schools,id',
            'session_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'location' => 'required|string|max:255',
            'hosted_by' => 'nullable|string|max:150',
            'description' => 'nullable|string',
            'expected_attendance' => 'nullable|integer|min:0'
        ]);

        $session = $this->service->update($id, $request->all());
        
        return response()->json([
            'success' => true,
            'message' => 'Information session updated successfully.',
            'data' => $session
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return response()->json([
            'success' => true,
            'message' => 'Information session deleted successfully.'
        ], 200);
    }
}