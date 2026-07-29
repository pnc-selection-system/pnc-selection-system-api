<?php

namespace Repositories;

use App\Models\Commune;

class CommuneRepository
{
    public function list(array $filters = [])
    {
        return Commune::select('id', 'district_id', 'name')
            ->with('district:id,province_id,name')
            ->when(
                !empty($filters['district_id']),
                fn($q) => $q->where('district_id', (int) $filters['district_id'])
            )
            ->latest('id')
            ->get();
    }

    public function create(array $data): Commune
    {
        return Commune::create($data);
    }

    public function find(Commune $commune): Commune
    {
        return $commune->load('district:id,province_id,name');
    }

    public function update(Commune $commune, array $data): Commune
    {
        $commune->update($data);

        return $commune->fresh()->load('district:id,province_id,name');
    }

    public function delete(Commune $commune): void
    {
        $commune->delete();
    }
}
