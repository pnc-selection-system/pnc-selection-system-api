<?php

namespace App\Services\InterestStudent;

use App\Repositories\InterestStudent\InterestStudentRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class InterestStudentService
{
    public function __construct(protected InterestStudentRepository $repository) {}

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
        $student = $this->findById($id);
        return $this->repository->update($student, $data);
    }

    public function delete(int $id): void
    {
        $student = $this->findById($id);
        $this->repository->delete($student);
    }
}