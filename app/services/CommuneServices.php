<?php

namespace Services;

use App\Models\Commune;
use Repositories\CommuneRepository;

class CommuneServices
{
    public function __construct(protected CommuneRepository $communeRepository)
    {
    }

    public function list(array $filters = [])
    {
        return $this->communeRepository->list($filters);
    }

    public function create(array $data): Commune
    {
        return $this->communeRepository->create($data);
    }

    public function find(Commune $commune): Commune
    {
        return $this->communeRepository->find($commune);
    }

    public function update(Commune $commune, array $data): Commune
    {
        return $this->communeRepository->update($commune, $data);
    }

    public function delete(Commune $commune): void
    {
        $this->communeRepository->delete($commune);
    }
}
