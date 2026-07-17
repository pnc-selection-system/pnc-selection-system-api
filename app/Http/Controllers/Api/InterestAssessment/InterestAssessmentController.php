<?php

namespace App\Http\Controllers;

use App\Models\AssessmentForm;
use App\Models\InfoSession;
use App\Models\InterestStudent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InterestAssessmentController extends Controller
{
    /**
     * Display the interest assessment form for a given info session.
     */
    public function show(string $sessionId)
    {
        $session = InfoSession::with(['campaign', 'province', 'school'])
            ->findOrFail($sessionId);

        // Get the assessment form linked to this session's campaign
        $assessmentForm = AssessmentForm::where('campaign_id', $session->campaign_id)
            ->first();

        return view('interest-assessment.show', compact('session', 'assessmentForm'));
    }

    /**
     * Handle the interest assessment form submission.
     */
    public function store(Request $request, string $sessionId)
    {
        $session = InfoSession::findOrFail($sessionId);

        // Base validation rules for student info
        $rules = [
            'full_name' => 'required|string|max:150',
            'gender' => ['required', 'string', Rule::in(['Male', 'Female', 'Other'])],
            'phone' => 'nullable|string|max:30',
            'school_grade' => 'nullable|string|max:50',
        ];

        // If an assessment form exists, add dynamic validation rules
        $assessmentForm = AssessmentForm::where('campaign_id', $session->campaign_id)
            ->first();

        if ($assessmentForm) {
            $rules = array_merge($rules, $assessmentForm->responseRules());
        }

        $validated = $request->validate($rules);

        // Extract student info and assessment answers
        $studentInfo = [
            'full_name' => $validated['full_name'],
            'gender' => $validated['gender'],
            'phone' => $validated['phone'] ?? null,
            'school_grade' => $validated['school_grade'] ?? null,
        ];

        $assessmentAnswers = null;
        $totalScore = null;

        if ($assessmentForm) {
            // Collect assessment answers (exclude student info fields)
            $fieldKeys = collect($assessmentForm->fields())->pluck('key')->toArray();
            $assessmentAnswers = collect($validated)
                ->only($fieldKeys)
                ->toArray();

            $totalScore = $assessmentForm->scoreResponse($assessmentAnswers);
        }

        // Save to database
        $interestStudent = DB::transaction(function () use ($session, $studentInfo, $assessmentAnswers, $totalScore) {
            return InterestStudent::create(array_merge($studentInfo, [
                'info_session_id' => $session->id,
                'assessment_answers' => $assessmentAnswers,
                'total_score' => $totalScore,
            ]));
        });

        return view('interest-assessment.success', [
            'session' => $session,
            'student' => $interestStudent,
        ]);
    }
}
