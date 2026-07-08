<?php

namespace Services;

use App\Models\AssessmentForm;
use Repositories\AssessmentFormRepository;

class AssessmentFormServices
{
    public function __construct(protected AssessmentFormRepository $assessmentFormRepository)
    {
    }

    public function list(array $filters = [])
    {
        return $this->assessmentFormRepository->list($filters);
    }

    public function create(array $data): AssessmentForm
    {
        return $this->assessmentFormRepository->create($data);
    }

    public function find(AssessmentForm $assessmentForm): AssessmentForm
    {
        return $this->assessmentFormRepository->find($assessmentForm);
    }

    public function update(AssessmentForm $assessmentForm, array $data): AssessmentForm
    {
        return $this->assessmentFormRepository->update($assessmentForm, $data);
    }

    public function delete(AssessmentForm $assessmentForm): void
    {
        $this->assessmentFormRepository->delete($assessmentForm);
    }
}
