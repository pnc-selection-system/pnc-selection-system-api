<?php

namespace Services;

use App\Models\SelectCampaing;
use Repositories\SelectCampaingRepository;

class SelectCampaingServices
{
    public function __construct(protected SelectCampaingRepository $selectCampaingRepository)
    {
    }

    public function list(array $filters = [])
    {
        return $this->selectCampaingRepository->list($filters);
    }

    public function create(array $data): SelectCampaing
    {
        return $this->selectCampaingRepository->create($data);
    }

    public function find(SelectCampaing $selectCampaing): SelectCampaing
    {
        return $this->selectCampaingRepository->find($selectCampaing);
    }

    public function update(SelectCampaing $selectCampaing, array $data): SelectCampaing
    {
        return $this->selectCampaingRepository->update($selectCampaing, $data);
    }

    public function delete(SelectCampaing $selectCampaing): void
    {
        $this->selectCampaingRepository->delete($selectCampaing);
    }
}
