<?php

namespace Services;

use App\Models\Cadidate;
use Repositories\CandidateRepository;

class CandidateServices
{
    public function __construct(protected CandidateRepository $candidateRepository) {}

    public function list(array $filters = [])
    {
        return $this->candidateRepository->list($filters);
    }

    public function create(array $data): Cadidate
    {
        return $this->candidateRepository->create($data);
    }

    public function find(Cadidate $candidate): Cadidate
    {
        return $this->candidateRepository->find($candidate);
    }

    public function update(Cadidate $candidate, array $data): Cadidate
    {
        return $this->candidateRepository->update($candidate, $data);
    }

    public function delete(Cadidate $candidate): void
    {
        $this->candidateRepository->delete($candidate);
    }

    public function createFromInterestStudent($interestStudent, array $additionalData = []): Cadidate
    {
        $data = [
            'campaign_id' => $interestStudent->infoSession->campaign_id ?? null,
            'province_id' => $interestStudent->infoSession->province_id ?? null,
            'school_id' => $interestStudent->infoSession->school_id ?? null,
            'first_name' => $interestStudent->full_name,
            'gender' => $interestStudent->gender,
            'phone' => $interestStudent->phone,
            'email' => $interestStudent->email,
            'current_grade' => $interestStudent->current_grade,
            'status' => 'Pending',
        ];

        // Merge with additional data (overwrites defaults if provided)
        $data = array_merge($data, $additionalData);

        return $this->create($data);
    }
}
