<?php

namespace Repositories;

use App\Models\SelectCampaing;
use Illuminate\Database\Eloquent\Collection;

class SelectCampaingRepository
{
    public function list(array $filters = []): Collection
    {
        $query = SelectCampaing::query();

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['year'])) {
            $query->where('year', (int) $filters['year']);
        }

        return $query->latest()->get();
    }

    public function create(array $data): SelectCampaing
    {
        return SelectCampaing::create($data);
    }

    public function find(SelectCampaing $selectCampaing): SelectCampaing
    {
        return $selectCampaing;
    }

    public function update(SelectCampaing $selectCampaing, array $data): SelectCampaing
    {
        $selectCampaing->update($data);

        return $selectCampaing;
    }

    public function delete(SelectCampaing $selectCampaing): void
    {
        $selectCampaing->delete();
    }
}
