<?php

namespace App\Http\Controllers\Api\Candidate;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Candidate\StoreCandidateRequest;
use App\Http\Requests\Api\Candidate\UpdateCandidateRequest;
use App\Models\Candidate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Services\CandidateServices;

class CandidateController extends Controller
{
    public function __construct(protected CandidateServices $candidateService) {}

    public function index(): JsonResponse
    {
        $candidates = $this->candidateService->list(request()->all());

        return ApiResponse::success($candidates, 'Candidates retrieved successfully');
    }

    public function store(StoreCandidateRequest $request): JsonResponse
    {
        $candidate = $this->candidateService->create($request->validated());

        return ApiResponse::created($candidate, 'Candidate created successfully');
    }

    public function show(Candidate $candidate): JsonResponse
    {
        return ApiResponse::success(
            $this->candidateService->find($candidate),
            'Candidate retrieved successfully'
        );
    }

    public function update(UpdateCandidateRequest $request, Candidate $candidate): JsonResponse
    {
        $candidate = $this->candidateService->update($candidate, $request->validated());

        return ApiResponse::success($candidate, 'Candidate updated successfully');
    }

    public function destroy(Candidate $candidate): JsonResponse
    {
        $this->candidateService->delete($candidate);

        return ApiResponse::ok('Candidate deleted successfully');
    }

    public function stats(): JsonResponse
    {
        $stats = $this->candidateService->stats();

        return ApiResponse::success($stats, 'Candidate stats retrieved successfully');
    }

    /**
     * Upload or update a candidate's profile photo.
     */
    public function uploadPhoto(Request $request, Candidate $candidate): JsonResponse
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // Delete old photo if exists
        if ($candidate->photo_url) {
            $oldPath = str_replace('/storage/', '', $candidate->photo_url);
            Storage::disk('public')->delete($oldPath);
        }

        // Store new photo
        $path = $request->file('photo')->store('candidates', 'public');
        $photoUrl = '/storage/' . $path;

        $candidate->update(['photo_url' => $photoUrl]);

        return ApiResponse::success([
            'photo_url' => $photoUrl,
        ], 'Photo uploaded successfully');
    }
}
