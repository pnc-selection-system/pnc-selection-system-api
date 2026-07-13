<?php

namespace App\Services\InfoSession;

use App\Repositories\InfoSession\InfoSessionRepositoryInterface;

class InfoSessionService
{
    public function __construct(protected InfoSessionRepositoryInterface $repository) {}

    public function list(array $filters)        { return $this->repository->list($filters); }
    public function findById(int $id)           { return $this->repository->findById($id); }
    public function create(array $data)         { return $this->repository->create($data); }
    public function update(int $id, array $data){ return $this->repository->update($id, $data); }
    public function delete(int $id)             { return $this->repository->delete($id); }
    public function updateAttendance(int $id, int $count) { return $this->repository->updateAttendance($id, $count); }
    public function hasConflict(string $schoolId, string $date, string $time, ?int $excludeId = null): bool
    {
        return $this->repository->hasConflict($schoolId, $date, $time, $excludeId);
    }
}
