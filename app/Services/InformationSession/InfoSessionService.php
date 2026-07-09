<?php

namespace App\Services\InformationSession;

use App\Repositories\InformationSession\InfoSessionRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class InfoSessionService
{
    public function __construct(protected InfoSessionRepository $repository) {}

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
        $session = $this->findById($id);
        return $this->repository->update($session, $data);
    }

    public function delete(int $id): void
    {
        $session = $this->findById($id);
        $this->repository->delete($session);
    }
}