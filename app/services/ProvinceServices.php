<?php

namespace Services;

use App\Models\Province;
use Repositories\ProvinceRepository;

class ProvinceServices
{
    public function __construct(protected ProvinceRepository $provinceRepository)
    {
    }

    public function list(array $filters = [])
    {
        return $this->provinceRepository->list($filters);
    }

    public function create(array $data): Province
    {
        return $this->provinceRepository->create($data);
    }

    public function find(Province $province): Province
    {
        return $this->provinceRepository->find($province);
    }

    public function update(Province $province, array $data): Province
    {
        return $this->provinceRepository->update($province, $data);
    }

    public function delete(Province $province): void
    {
        $this->provinceRepository->delete($province);
    }
}
