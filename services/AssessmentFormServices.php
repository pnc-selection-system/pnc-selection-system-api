<?php

namespace Services;

use App\Models\AssessmentForm;
use Illuminate\Contracts\Validation\Validator;
use App\Repositories\AssessmentFormRepository;

class AssessmentFormServices
{
    public function __construct(protected AssessmentFormRepository $assessmentFormRepository) {}

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

    public function totalWeight(AssessmentForm $assessmentForm): float
    {
        return $assessmentForm->totalWeight();
    }

    public function responseRules(AssessmentForm $assessmentForm): array
    {
        return $assessmentForm->responseRules();
    }

    public function validateResponse(AssessmentForm $assessmentForm, array $data): Validator
    {
        return $assessmentForm->validateResponse($data);
    }

    public function scoreResponse(AssessmentForm $assessmentForm, array $data): float
    {
        return $assessmentForm->scoreResponse($data);
    }
}
