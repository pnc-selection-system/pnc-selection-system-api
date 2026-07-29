<?php

namespace Repositories;

use App\Models\Village;

class VillageRepository
{
    public function list(array $filters = [])
    {
        return Village::select('id', 'commune_id', 'name')
            ->with('commune:id,district_id,name')
            ->when(
                !empty($filters['commune_id']),
                fn($q) => $q->where('commune_id', (int) $filters['commune_id'])
            )
            ->when(
                !empty($filters['district_id']),
                fn($q) => $q->whereHas('commune', fn($q) => $q->where('district_id', (int) $filters['district_id']))
            )
            ->when(
                !empty($filters['province_id']),
                fn($q) => $q->whereHas('commune.district', fn($q) => $q->where('province_id', (int) $filters['province_id']))
            )
            ->latest('id')
            ->get();
    }

    public function create(array $data): Village
    {
        return Village::create($data);
    }

    public function find(Village $village): Village
    {
        return $village->load('commune:id,district_id,name');
    }

    public function update(Village $village, array $data): Village
    {
        $village->update($data);

        return $village->fresh()->load('commune:id,district_id,name');
    }

    public function delete(Village $village): void
    {
        $village->delete();
    }
}
