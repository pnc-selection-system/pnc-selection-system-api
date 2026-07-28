<?php

namespace Repositories;

use App\Models\Province;

class ProvinceRepository
{
    public function list(array $filters = [])
    {
        $query = Province::select('id', 'name')->orderBy('name');

        if (!empty($filters['campaign_id'])) {
            $query->whereHas('campaigns', function ($q) use ($filters) {
                $q->where('id', $filters['campaign_id']);
            });
        }

        return $query->get();
    }

    public function create(array $data): Province
    {
        return Province::create($data);
    }

    public function find(Province $province): Province
    {
        return $province;
    }

    public function update(Province $province, array $data): Province
    {
        $province->update($data);

        return $province->fresh();
    }

    public function delete(Province $province): void
    {
        $province->delete();
    }
}
