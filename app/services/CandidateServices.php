<?php

namespace Services;

use App\Models\Candidate;
use Repositories\CandidateRepository;

class CandidateServices
{
    public function __construct(protected CandidateRepository $candidateRepository) {}

    public function stats(): array
    {
        return $this->candidateRepository->stats();
    }

    public function list(array $filters = [])
    {
        return $this->candidateRepository->list($filters);
    }

    public function create(array $data): Candidate
    {
        return $this->candidateRepository->create($data);
    }

    public function find(Candidate $candidate): Candidate
    {
        return $this->candidateRepository->find($candidate);
    }

    public function update(Candidate $candidate, array $data): Candidate
    {
        return $this->candidateRepository->update($candidate, $data);
    }

    public function delete(Candidate $candidate): void
    {
        $this->candidateRepository->delete($candidate);
    }
}
