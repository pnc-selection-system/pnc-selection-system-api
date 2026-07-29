<?php

namespace Repositories;

use App\Models\District;

class DistrictRepository
{
    public function list(array $filters = [])
    {
        return District::select('id', 'province_id', 'name')
            ->with('province:id,name')
            ->when(
                !empty($filters['province_id']),
                fn($q) => $q->where('province_id', (int) $filters['province_id'])
            )
            ->latest('id')
            ->get();
    }

    public function create(array $data): District
    {
        return District::create($data);
    }

    public function find(District $district): District
    {
        return $district->load('province:id,name');
    }

    public function update(District $district, array $data): District
    {
        $district->update($data);

        return $district->fresh()->load('province:id,name');
    }

    public function delete(District $district): void
    {
        $district->delete();
    }
}
