<?php

namespace Repositories;

use App\Models\District;
use Illuminate\Database\Eloquent\Collection;

class DistrictRepository
{
    public function list(array $filters = []): Collection
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
}
