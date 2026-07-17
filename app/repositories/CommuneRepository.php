<?php

namespace Repositories;

use App\Models\Commune;
use Illuminate\Database\Eloquent\Collection;

class CommuneRepository
{
    public function list(array $filters = []): Collection
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
}
