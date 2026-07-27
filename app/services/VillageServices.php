<?php

namespace Services;

use App\Models\Village;
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

    public function create(array $data): Village
    {
        return $this->villageRepository->create($data);
    }

    public function find(Village $village): Village
    {
        return $this->villageRepository->find($village);
    }

    public function update(Village $village, array $data): Village
    {
        return $this->villageRepository->update($village, $data);
    }

    public function delete(Village $village): void
    {
        $this->villageRepository->delete($village);
    }
}
