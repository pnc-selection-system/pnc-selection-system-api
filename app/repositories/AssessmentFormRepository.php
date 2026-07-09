<?php

namespace Repositories;

use App\Models\AssessmentForm;
use Illuminate\Database\Eloquent\Collection;

class AssessmentFormRepository
{
    public function list(array $filters = []): Collection
    {
        $query = AssessmentForm::query();

        if (!empty($filters['campaign_id'])) {
            $query->where('campaign_id', (int) $filters['campaign_id']);
        }

        return $query->latest()->get();
    }

    public function create(array $data): AssessmentForm
    {
        return AssessmentForm::create($data);
    }

    public function find(AssessmentForm $assessmentForm): AssessmentForm
    {
        return $assessmentForm;
    }

    public function update(AssessmentForm $assessmentForm, array $data): AssessmentForm
    {
        $assessmentForm->update($data);

        return $assessmentForm;
    }

    public function delete(AssessmentForm $assessmentForm): void
    {
        $assessmentForm->delete();
    }
}
