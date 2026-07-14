<?php

namespace Services;

use App\Models\School;
use Repositories\SchoolRepository;

class SchoolServices
{
    public function __construct(protected SchoolRepository $schoolRepository) {}

    public function list(array $filters = [])
    {
        return $this->schoolRepository->list($filters);
    }

    public function create(array $data): School
    {
        return $this->schoolRepository->create($data);
    }

    public function find(School $school): School
    {
        return $this->schoolRepository->find($school);
    }

    public function update(School $school, array $data): School
    {
        return $this->schoolRepository->update($school, $data);
    }

    public function delete(School $school): void
    {
        $this->schoolRepository->delete($school);
    }
}
