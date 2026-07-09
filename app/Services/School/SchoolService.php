<?php

namespace App\Services\School;

use App\Repositories\School\SchoolRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class SchoolService
{
    public function __construct(protected SchoolRepository $repository) {}

    public function getPaginated(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->repository->getPaginated($filters, $perPage);
    }

    public function findById(int $id)
    {
        return $this->repository->findById($id);
    }

    public function create(array $data)
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data)
    {
        $school = $this->findById($id);
        return $this->repository->update($school, $data);
    }

    public function delete(int $id): void
    {
        $school = $this->findById($id);
        $this->repository->delete($school);
    }
}