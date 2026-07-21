<?php

namespace Repositories;

use App\Models\AssessmentForm;
use Illuminate\Database\Eloquent\Collection;

class AssessmentFormRepository
{
    public function list(array $filters = [])
    {
        return AssessmentForm::select('id', 'campaign_id', 'name')
            ->with('campaign:id,name,year,status')
            ->when(
                !empty($filters['campaign_id']),
                fn($q) => $q->where('campaign_id', (int) $filters['campaign_id'])
            )
            ->latest('id')
            ->paginate($filters['per_page'] ?? 10);
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
        $assessmentForm->delete($assessmentForm);
    }
}
