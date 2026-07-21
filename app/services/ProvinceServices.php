<?php

namespace Services;

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
}
