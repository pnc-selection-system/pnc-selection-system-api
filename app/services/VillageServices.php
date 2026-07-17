<?php

namespace Services;

use Repositories\VillageRepository;

class VillageServices
{
    public function __construct(protected VillageRepository $villageRepository)
    {
    }

    public function list(array $filters = [])
    {
        return $this->villageRepository->list($filters);
    }
}
