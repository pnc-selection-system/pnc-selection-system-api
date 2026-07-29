<?php

namespace App\Repositories;

use App\Models\AssessmentResponse;

class AssessmentResponseRepository
{
    public function list(array $filters = [])
    {
        return AssessmentResponse::with(['candidate', 'form'])
            ->when($filters['candidate_id'] ?? null, fn($q, $v) => $q->where('candidate_id', $v))
            ->when($filters['assessment_form_id'] ?? null, fn($q, $v) => $q->where('assessment_form_id', $v))
            ->orderBy('id', 'desc')
            ->get();
    }

    public function findByCandidate(int $candidateId, int $formId)
    {
        return AssessmentResponse::where('candidate_id', $candidateId)
            ->where('assessment_form_id', $formId)
            ->first();
    }

    public function store(array $data)
    {
        return AssessmentResponse::create($data);
    }
}
