<?php

namespace Repositories;

use App\Models\Village;
use Illuminate\Database\Eloquent\Collection;

class VillageRepository
{
    public function list(array $filters = []): Collection
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
}
