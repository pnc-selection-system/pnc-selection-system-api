<?php

namespace App\Repositories\Candidate;

use App\Models\Cadidate;

class CandidateRepository
{
    public function findById(int $id)
    {
        return Cadidate::findOrFail($id);
    }

    public function create(array $data)
    {
        return Cadidate::create($data);
    }
}