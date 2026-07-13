<?php

namespace Repositories;

use App\Models\Village;
use Illuminate\Database\Eloquent\Collection;

class VillageRepository
{
    public function list(array $filters = []): Collection
    {
        $query = Village::with('commune');

        if (!empty($filters['commune_id'])) {
            $query->where('commune_id', (int) $filters['commune_id']);
        }

        if (!empty($filters['district_id'])) {
            $query->whereHas('commune', function ($q) use ($filters) {
                $q->where('district_id', (int) $filters['district_id']);
            });
        }

        if (!empty($filters['province_id'])) {
            $query->whereHas('commune.district', function ($q) use ($filters) {
                $q->where('province_id', (int) $filters['province_id']);
            });
        }

        return $query->latest()->get();
    }
}
