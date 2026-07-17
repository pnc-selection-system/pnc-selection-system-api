<?php

namespace App\Http\Controllers\Api\Candidate;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Cadidate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CandidateController extends Controller
{
    /**
     * GET /api/candidates/search?q=C-1042
     * Search candidates by ID or code (e.g., C-1042).
     * Public endpoint - no authentication required.
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:1',
        ]);

        $query = $request->input('q');

        // Check if the query matches the code pattern (C-XXXX)
        if (preg_match('/^C-(\d+)$/i', $query, $matches)) {
            $candidateId = (int) $matches[1];
            $candidate = Cadidate::with(['province', 'school'])
                ->find($candidateId);

            if (! $candidate) {
                return ApiResponse::notFound('Candidate not found with code: ' . $query);
            }

            return ApiResponse::success($this->formatCandidate($candidate), 'Candidate found');
        }

        // Check if the query is a numeric ID
        if (is_numeric($query)) {
            $candidate = Cadidate::with(['province', 'school'])
                ->find((int) $query);

            if (! $candidate) {
                return ApiResponse::notFound('Candidate not found with ID: ' . $query);
            }

            return ApiResponse::success($this->formatCandidate($candidate), 'Candidate found');
        }

        // Search by name, phone, or email
        $candidates = Cadidate::with(['province', 'school'])
            ->where(function ($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                    ->orWhere('last_name', 'like', "%{$query}%")
                    ->orWhere('phone', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get()
            ->map(fn ($c) => $this->formatCandidate($c));

        return ApiResponse::success($candidates, 'Candidates retrieved successfully');
    }

    /**
     * GET /api/candidates/{id}
     * Get a single candidate by ID.
     */
    public function show(int $id): JsonResponse
    {
        $candidate = Cadidate::with(['province', 'school'])
            ->find($id);

        if (! $candidate) {
            return ApiResponse::notFound('Candidate not found');
        }

        return ApiResponse::success($this->formatCandidate($candidate), 'Candidate retrieved successfully');
    }

    /**
     * Format candidate data for API response.
     */
    private function formatCandidate(Cadidate $candidate): array
    {
        return [
            'id' => $candidate->id,
            'code' => $candidate->code,
            'full_name' => $candidate->full_name,
            'first_name' => $candidate->first_name,
            'last_name' => $candidate->last_name,
            'gender' => $candidate->gender,
            'phone' => $candidate->phone,
            'email' => $candidate->email,
            'province' => [
                'id' => $candidate->province->id ?? null,
                'name' => $candidate->province->name ?? null,
            ],
            'school' => [
                'id' => $candidate->school->id ?? null,
                'name' => $candidate->school->name ?? null,
            ],
            'status' => $candidate->status,
        ];
    }
}
