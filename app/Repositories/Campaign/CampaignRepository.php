<?php

namespace App\Repositories\Campaign;

use App\Models\Campaign;

class CampaignRepository implements CampaignRepositoryInterface
{
    public function getAll()
    {
        return Campaign::all();
    }

    public function findById(int $id)
    {
        return Campaign::findOrFail($id);
    }

    public function create(array $data)
    {
        return Campaign::create($data);
    }

    public function update(int $id, array $data)
    {
        $campaign = Campaign::findOrFail($id);
        $campaign->update($data);
        return $campaign;
    }

    public function delete(int $id)
    {
        return Campaign::findOrFail($id)->delete();
    }

    public function getPaginated(array $filters = [], int $perPage = 15)
    {
        $query = Campaign::query();

        // Search by name
        if (isset($filters['search']) && !empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        // Filter by year
        if (isset($filters['year']) && !empty($filters['year'])) {
            $query->where('year', $filters['year']);
        }

        // Filter by status
        if (isset($filters['status']) && !empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by date range
        if (isset($filters['start_date_from']) && !empty($filters['start_date_from'])) {
            $query->where('start_date', '>=', $filters['start_date_from']);
        }

        if (isset($filters['start_date_to']) && !empty($filters['start_date_to'])) {
            $query->where('start_date', '<=', $filters['start_date_to']);
        }

        if (isset($filters['end_date_from']) && !empty($filters['end_date_from'])) {
            $query->where('end_date', '>=', $filters['end_date_from']);
        }

        if (isset($filters['end_date_to']) && !empty($filters['end_date_to'])) {
            $query->where('end_date', '<=', $filters['end_date_to']);
        }

        return $query->paginate($perPage);
    }
}
