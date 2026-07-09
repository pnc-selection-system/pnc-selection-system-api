<?php

namespace App\Http\Controllers\Api\InterestStudent;

use App\Http\Controllers\Controller;
use App\Services\InterestStudent\InterestStudentService;
use App\Services\Candidate\CandidateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConvertToCandidateController extends Controller
{
    public function __construct(
        protected InterestStudentService $interestStudentService,
        protected CandidateService $candidateService
    ) {}

    public function __invoke(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'notes' => 'nullable|string'
        ]);

        $interestStudent = $this->interestStudentService->findById($id);
        
        if ($interestStudent->status === 'converted') {
            return response()->json([
                'success' => false,
                'message' => 'This student has already been converted to a candidate.'
            ], 400);
        }

        $candidate = $this->candidateService->createFromInterestStudent($interestStudent, $request->all());
        
        $this->interestStudentService->update($id, [
            'status' => 'converted',
            'converted_to_candidate_id' => $candidate->id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Student converted to candidate successfully.',
            'data' => [
                'candidate' => $candidate,
                'interest_student' => $interestStudent
            ]
        ], 201);
    }
}