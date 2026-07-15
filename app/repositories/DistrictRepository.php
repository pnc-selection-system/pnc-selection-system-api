<?php

namespace Repositories;

use App\Models\District;
use Illuminate\Database\Eloquent\Collection;

class DistrictRepository
{
    public function list(array $filters = []): Collection
    {
        $query = District::with('province');

        if (!empty($filters['province_id'])) {
            $query->where('province_id', (int) $filters['province_id']);
        }

        return $query->latest()->get();
    }
}
