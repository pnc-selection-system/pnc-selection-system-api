<?php

namespace App\Services\Province;

use App\Repositories\Province\ProvinceRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class ProvinceService
{
    public function __construct(protected ProvinceRepository $repository) {}

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
        $province = $this->findById($id);
        return $this->repository->update($province, $data);
    }

    public function delete(int $id): void
    {
        $province = $this->findById($id);
        $this->repository->delete($province);
    }
}