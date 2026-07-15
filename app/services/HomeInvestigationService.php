<?php

namespace Services;

use App\Models\HomeInvestigation;
use Repositories\HomeInvestigationRepository;

class HomeInvestigationService
{
    public function __construct(protected HomeInvestigationRepository $homeInvestigationRepository) {}

    public function getAllHomeInvestigations(array $filters = [], int $perPage = 15)
    {
        return $this->homeInvestigationRepository->list($filters, $perPage);
    }

    public function createHomeInvestigation(array $data): HomeInvestigation
    {
        return $this->homeInvestigationRepository->create($data);
    }

    public function getHomeInvestigationById(int $id): HomeInvestigation
    {
        return $this->homeInvestigationRepository->find($id);
    }

    public function updateHomeInvestigation(int $id, array $data): HomeInvestigation
    {
        return $this->homeInvestigationRepository->update($id, $data);
    }

    public function deleteHomeInvestigation(int $id): void
    {
        $this->homeInvestigationRepository->delete($id);
    }

    public function submitHomeInvestigation(int $id, array $data): HomeInvestigation
    {
        return $this->homeInvestigationRepository->submit($id, $data);
    }
}