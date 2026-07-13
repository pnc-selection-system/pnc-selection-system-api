<?php

namespace Services;

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
}
