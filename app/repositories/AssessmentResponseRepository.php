<?php

namespace Repositories;

use App\Models\AssessmentRespone;
use Illuminate\Database\Eloquent\Collection;

class AssessmentResponseRepository
{
    public function list(array $filters = []): Collection
    {
        $query = AssessmentRespone::query();

        if (! empty($filters['form_id'])) {
            $query->where('form_id', (int) $filters['form_id']);
        }

        if (! empty($filters['candidate_id'])) {
            $query->where('candidate_id', (int) $filters['candidate_id']);
        }

        return $query->with(['form', 'candidate'])->latest()->get();
    }

    public function create(array $data): AssessmentRespone
    {
        return AssessmentRespone::create($data)->load('form');
    }

    public function find(AssessmentRespone $response): AssessmentRespone
    {
        return $response->load(['form', 'candidate']);
    }

    public function update(AssessmentRespone $response, array $data): AssessmentRespone
    {
        $response->update($data);

        return $response->load('form');
    }

    public function delete(AssessmentRespone $response): void
    {
        $response->delete();
    }

    public function findByFormAndCandidate(int $formId, int $candidateId): ?AssessmentRespone
    {
        return AssessmentRespone::where('form_id', $formId)
            ->where('candidate_id', $candidateId)
            ->first();
    }
}
