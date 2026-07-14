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
}
