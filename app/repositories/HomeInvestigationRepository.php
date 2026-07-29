<?php

namespace Repositories;

use App\Models\HomeInvestigation;
use Illuminate\Pagination\LengthAwarePaginator;

class HomeInvestigationRepository
{
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = HomeInvestigation::query();

        if (! empty($filters['candidate_id'])) {
            $query->where('candidate_id', (int) $filters['candidate_id']);
        }

        if (! empty($filters['campaign_id'])) {
            $query->where('campaign_id', (int) $filters['campaign_id']);
        }

        if (! empty($filters['investigator_id'])) {
            $query->where('investigator_id', (int) $filters['investigator_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): HomeInvestigation
    {
        return HomeInvestigation::create($data);
    }

    public function find(int $id): HomeInvestigation
    {
        return HomeInvestigation::findOrFail($id);
    }

    public function update(int $id, array $data): HomeInvestigation
    {
        $homeInvestigation = $this->find($id);
        $homeInvestigation->update($data);

        return $homeInvestigation;
    }

    public function delete(int $id): void
    {
        $homeInvestigation = $this->find($id);
        $homeInvestigation->delete($id);
    }

    public function submit(int $id, array $data): HomeInvestigation
    {
        $homeInvestigation = $this->find($id);
        $homeInvestigation->update($data);

        return $homeInvestigation;
    }
}