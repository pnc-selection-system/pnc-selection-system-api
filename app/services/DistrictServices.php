<?php

namespace Services;

use App\Models\District;
use Repositories\DistrictRepository;

class DistrictServices
{
    public function __construct(protected DistrictRepository $districtRepository)
    {
    }

    public function list(array $filters = [])
    {
        return $this->districtRepository->list($filters);
    }

    public function create(array $data): District
    {
        return $this->districtRepository->create($data);
    }

    public function find(District $district): District
    {
        return $this->districtRepository->find($district);
    }

    public function update(District $district, array $data): District
    {
        return $this->districtRepository->update($district, $data);
    }

    public function delete(District $district): void
    {
        $this->districtRepository->delete($district);
    }
}
