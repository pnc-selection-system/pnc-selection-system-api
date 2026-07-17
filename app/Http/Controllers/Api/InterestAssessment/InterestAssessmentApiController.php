<?php

namespace App\Http\Controllers\Api\InterestAssessment;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\AssessmentForm;
use App\Models\InfoSession;
use App\Models\InterestStudent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InterestAssessmentApiController extends Controller
{
    /**
     * GET /api/interest-assessment/{sessionId}
     * Returns the info session details and its linked assessment form schema.
     */
    public function show(string $sessionId): JsonResponse
    {
        $session = InfoSession::with(['campaign', 'province', 'school'])
            ->findOrFail($sessionId);

        $assessmentForm = AssessmentForm::where('campaign_id', $session->campaign_id)
            ->first();

        return ApiResponse::success([
            'session' => [
                'id' => $session->id,
                'campaign' => [
                    'id' => $session->campaign->id ?? null,
                    'name' => $session->campaign->name ?? null,
                ],
                'province' => [
                    'id' => $session->province->id ?? null,
                    'name' => $session->province->name ?? null,
                ],
                'school' => [
                    'id' => $session->school->id ?? null,
                    'name' => $session->school->name ?? null,
                ],
                'session_date' => $session->session_date?->format('Y-m-d'),
                'session_time' => $session->session_time,
                'location' => $session->location,
            ],
            'assessment_form' => $assessmentForm ? [
                'id' => $assessmentForm->id,
                'name' => $assessmentForm->name,
                'pass_threshold' => (float) $assessmentForm->pass_threshold,
                'fields' => $assessmentForm->fields(),
            ] : null,
        ], 'Interest assessment data retrieved successfully');
    }

    /**
     * POST /api/interest-assessment/{sessionId}
     * Stores student info + assessment answers.
     */
    public function store(Request $request, string $sessionId): JsonResponse
    {
        $session = InfoSession::findOrFail($sessionId);

        // Base validation for student info
        $rules = [
            'full_name' => 'required|string|max:150',
            'gender' => ['required', 'string', Rule::in(['Male', 'Female', 'Other'])],
            'phone' => 'nullable|string|max:30',
            'school_grade' => 'nullable|string|max:50',
        ];

        // Merge dynamic assessment field rules if form exists
        $assessmentForm = AssessmentForm::where('campaign_id', $session->campaign_id)
            ->first();

        if ($assessmentForm) {
            $rules = array_merge($rules, $assessmentForm->responseRules());
        }

        $validated = $request->validate($rules);

        // Check for duplicate submission
        $exists = InterestStudent::where('info_session_id', $session->id)
            ->where('full_name', $validated['full_name'])
            ->exists();

        if ($exists) {
            return ApiResponse::error('You have already submitted an interest assessment for this session.', 409);
        }

        // Separate student info from assessment answers
        $studentInfo = [
            'full_name' => $validated['full_name'],
            'gender' => $validated['gender'],
            'phone' => $validated['phone'] ?? null,
            'school_grade' => $validated['school_grade'] ?? null,
        ];

        $assessmentAnswers = null;
        $totalScore = null;

        if ($assessmentForm) {
            $fieldKeys = collect($assessmentForm->fields())->pluck('key')->toArray();
            $assessmentAnswers = collect($validated)->only($fieldKeys)->toArray();
            $totalScore = $assessmentForm->scoreResponse($assessmentAnswers);
        }

        $interestStudent = InterestStudent::create(array_merge($studentInfo, [
            'info_session_id' => $session->id,
            'assessment_answers' => $assessmentAnswers,
            'total_score' => $totalScore,
        ]));

        $passed = $assessmentForm && $totalScore !== null
            ? $totalScore >= (float) $assessmentForm->pass_threshold
            : null;

        return ApiResponse::created([
            'id' => $interestStudent->id,
            'full_name' => $interestStudent->full_name,
            'gender' => $interestStudent->gender,
            'phone' => $interestStudent->phone,
            'school_grade' => $interestStudent->school_grade,
            'total_score' => $interestStudent->total_score,
            'passed' => $passed,
        ], 'Interest assessment submitted successfully');
    }
}
