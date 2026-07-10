<?php

namespace App\Http\Controllers\Api\InterestStudent;

use App\Http\Controllers\Controller;
use App\Models\InterestStudent;
use Services\CandidateServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConvertToCandidateController extends Controller
{
    public function __construct(
        protected CandidateServices $candidateService
    ) {}

    public function __invoke(Request $request, int $id): JsonResponse
    {
        $interestStudent = InterestStudent::with('infoSession')->findOrFail($id);
        $candidateGenders = ['Male', 'Female', 'Other'];

        $data = $request->validate([
            'last_name' => ['required', 'string', 'max:100'],
            'dob' => ['required', 'date'],
            'gender' => [in_array($interestStudent->gender, $candidateGenders, true) ? 'sometimes' : 'required', 'string', 'in:Male,Female,Other'],
            'phone' => [$interestStudent->phone ? 'sometimes' : 'required', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($interestStudent->status === 'converted') {
            return response()->json([
                'success' => false,
                'message' => 'This student has already been converted to a candidate.'
            ], 400);
        }

        $candidate = $this->candidateService->createFromInterestStudent($interestStudent, $data);
        
        $interestStudent->update([
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
