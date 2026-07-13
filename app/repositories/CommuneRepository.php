<?php

namespace Repositories;

use App\Models\Commune;
use Illuminate\Database\Eloquent\Collection;

class CommuneRepository
{
    public function list(array $filters = []): Collection
    {
        $query = Commune::with('district');

        if (!empty($filters['district_id'])) {
            $query->where('district_id', (int) $filters['district_id']);
        }

        return $query->latest()->get();
    }
}
