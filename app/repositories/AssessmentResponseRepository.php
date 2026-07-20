<?php

namespace Repositories;

use App\Models\AssessmentQuestion;
use App\Models\AssessmentRespone;
use Illuminate\Support\Collection;

class AssessmentResponseRepository
{
    /**
     * List all responses grouped by candidate + form.
     * Returns a collection of [ candidate_id, form_id, answers[], total_score, passed ]
     */
    public function list(array $filters = []): Collection
    {
        $query = AssessmentRespone::with(['candidate.province', 'question.form']);

        if (! empty($filters['candidate_id'])) {
            $query->where('candidate_id', (int) $filters['candidate_id']);
        }

        if (! empty($filters['form_id'])) {
            $query->whereHas('question', fn ($q) => $q->where('form_id', (int) $filters['form_id']));
        }

        return $query->get()
            ->groupBy(fn ($r) => $r->candidate_id . '_' . $r->question->form_id)
            ->map(fn ($rows) => $this->groupRows($rows))
            ->values();
    }

    /**
     * Get all responses for a specific candidate + form.
     */
    public function findByFormAndCandidate(int $formId, int $candidateId): Collection
    {
        $rows = AssessmentRespone::with(['candidate.province', 'question.form'])
            ->where('candidate_id', $candidateId)
            ->whereHas('question', fn ($q) => $q->where('form_id', $formId))
            ->get();
    }

    /**
     * Save answers for a candidate — upsert one row per question.
     * $answers = [ question_id => answer_text, ... ]
     */
    public function saveAnswers(int $candidateId, array $answers): Collection
    {
        foreach ($answers as $questionId => $answer) {
            AssessmentRespone::updateOrCreate(
                ['candidate_id' => $candidateId, 'question_id' => $questionId],
                ['answer' => $answer]
            );
        }

        return AssessmentRespone::with('question')
            ->where('candidate_id', $candidateId)
            ->whereIn('question_id', array_keys($answers))
            ->get();
    }

    /**
     * Delete all responses for a candidate on a specific form.
     */
    public function deleteByFormAndCandidate(int $formId, int $candidateId): void
    {
        $questionIds = AssessmentQuestion::where('form_id', $formId)->pluck('id');

        AssessmentRespone::where('candidate_id', $candidateId)
            ->whereIn('question_id', $questionIds)
            ->delete();
    }

    /**
     * Group individual answer rows into a single structured response object.
     * Includes ALL questions from the form, with null answer if not yet answered.
     */
    public function groupRows(Collection $rows): array
    {
        $first     = $rows->first();
        $form      = $first->question->form;
        $candidate = $first->candidate;

        // Index existing answers by question_id
        $answeredMap = $rows->keyBy('question_id');

        // Get ALL questions for this form ordered by order
        $allQuestions = AssessmentQuestion::where('form_id', $form?->id)
            ->orderBy('order')
            ->get();

        $answers = $allQuestions->map(fn ($q) => [
            'question_id' => $q->id,
            'key'         => $q->key,
            'label'       => $q->label,
            'type'        => $q->type,
            'options'     => $q->options,
            'point_map'   => $q->point_map,
            'weight'      => (float) $q->weight,
            'answer'      => $answeredMap->has($q->id) ? $answeredMap->get($q->id)->answer : null,
        ])->values()->toArray();

        $totalScore = $this->calculateScore($form, $rows);
        $passed     = $form ? $totalScore >= (float) $form->pass_threshold : null;

        return [
            'candidate_id'   => $candidate?->id,
            'candidate_code' => $candidate ? 'C-' . str_pad($candidate->id, 4, '0', STR_PAD_LEFT) : null,
            'candidate_name' => $candidate?->full_name,
            'province'       => $candidate?->province?->name ?? null,
            'status'         => $candidate?->status ?? null,
            'form_id'        => $form?->id,
            'form_name'      => $form?->name,
            'answers'        => $answers,
            'total_score'    => $totalScore,
            'pass_threshold' => $form ? (float) $form->pass_threshold : null,
            'passed'         => $passed,
            'submitted_at'   => $rows->max('created_at')?->toDateTimeString(),
        ];
    }

    private function calculateScore($form, Collection $rows): float
    {
        if (! $form) {
            return 0.0;
        }

        $totalWeight   = (float) $form->totalWeight();
        if ($totalWeight <= 0) {
            return 0.0;
        }

        $weightedScore = 0.0;

        foreach ($rows as $row) {
            $question = $row->question;
            $weight   = (float) ($question->weight ?? 0);
            if ($weight <= 0) {
                continue;
            }

            $field = [
                'type'      => $question->type,
                'rules'     => $question->rules ?? [],
                'point_map' => $question->point_map ?? null,
            ];

            $normalized    = $form->normalizeValue($field, $row->answer);
            $weightedScore += $weight * $normalized;
        }

        return round($weightedScore / $totalWeight * 100, 2);
    }
}
