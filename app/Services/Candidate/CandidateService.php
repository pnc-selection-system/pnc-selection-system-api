<?php

namespace App\Services\Candidate;

use App\Models\InterestStudent;
use App\Repositories\Candidate\CandidateRepository;
use App\Models\Cadidate;

class CandidateService
{
    public function __construct(protected CandidateRepository $repository) {}

    public function createFromInterestStudent(InterestStudent $interestStudent, array $additionalData = []): Cadidate
    {
        $candidateData = [
            'full_name' => $interestStudent->full_name,
            'gender' => $interestStudent->gender,
            'phone' => $interestStudent->phone,
            'email' => $interestStudent->email,
            'current_grade' => $interestStudent->current_grade,
            'school_name' => $interestStudent->school_name,
            'preferred_major' => $interestStudent->preferred_major,
            'info_session_id' => $interestStudent->info_session_id,
            'status' => 'new',
            'notes' => $additionalData['notes'] ?? $interestStudent->notes
        ];

        return $this->repository->create($candidateData);
    }

    public function findById(int $id)
    {
        return $this->repository->findById($id);
    }
}