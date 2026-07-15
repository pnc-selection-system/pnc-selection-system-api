<?php

namespace App\Http\Controllers\Api\HomeInvestigation;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\HomeInvestigation\StoreHomeInvestigationRequest;
use App\Http\Requests\Api\HomeInvestigation\UpdateHomeInvestigationRequest;
use App\Http\Requests\Api\HomeInvestigation\SubmitHomeInvestigationRequest;
use Services\HomeInvestigationService;
use Services\HomeInvestigationFileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomeInvestigationController extends Controller
{
    protected HomeInvestigationService $homeInvestigationService;
    protected HomeInvestigationFileService $homeInvestigationFileService;

    public function __construct(
        HomeInvestigationService $homeInvestigationService,
        HomeInvestigationFileService $homeInvestigationFileService
    ) {
        $this->homeInvestigationService = $homeInvestigationService;
        $this->homeInvestigationFileService = $homeInvestigationFileService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['candidate_id', 'campaign_id', 'investigator_id', 'status']);
        $perPage = $request->get('per_page', 15);

        $homeInvestigations = $this->homeInvestigationService->getAllHomeInvestigations($filters, $perPage);

        return response()->json([
            'success' => true,
            'data' => $homeInvestigations,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreHomeInvestigationRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $homeInvestigation = $this->homeInvestigationService->createHomeInvestigation($validatedData);

        return response()->json([
            'message' => 'Investigation report created successfully',
            'data' => $homeInvestigation,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $homeInvestigation): JsonResponse
    {
        $homeInvestigation = $this->homeInvestigationService->getHomeInvestigationById($homeInvestigation);

        return response()->json([
            'data' => $homeInvestigation,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateHomeInvestigationRequest $request, int $homeInvestigation): JsonResponse
    {
        $validatedData = $request->validated();
        $updatedHomeInvestigation = $this->homeInvestigationService->updateHomeInvestigation($homeInvestigation, $validatedData);

        return response()->json([
            'message' => 'Investigation report updated successfully',
            'data' => $updatedHomeInvestigation,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $homeInvestigation): JsonResponse
    {
        $this->homeInvestigationService->deleteHomeInvestigation($homeInvestigation);

        return response()->json([
            'message' => 'Investigation report deleted successfully',
        ]);
    }

    /**
     * Submit the home investigation for review.
     */
    public function submit(SubmitHomeInvestigationRequest $request, int $homeInvestigation): JsonResponse
    {
        $validatedData = $request->validated();
        $submittedHomeInvestigation = $this->homeInvestigationService->submitHomeInvestigation($homeInvestigation, $validatedData);

        return response()->json([
            'message' => 'Investigation report submitted successfully',
            'data' => $submittedHomeInvestigation,
        ]);
    }

    /**
     * Upload a file to the home investigation.
     */
    public function uploadFile(Request $request, int $homeInvestigation): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'file_type' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $file = $request->file('file');
        $fileData = [
            'file' => $file,
            'file_type' => $request->input('file_type'),
            'description' => $request->input('description'),
        ];

        $homeInvestigationFile = $this->homeInvestigationFileService->uploadFile($homeInvestigation, $fileData);

        return response()->json([
            'message' => 'File uploaded successfully',
            'data' => $homeInvestigationFile,
        ], 201);
    }

    /**
     * Delete a file from the home investigation.
     */
    public function deleteFile(int $homeInvestigation, int $file): JsonResponse
    {
        $this->homeInvestigationFileService->deleteFile($homeInvestigation, $file);

        return response()->json([
            'message' => 'File deleted successfully',
        ]);
    }
}