<?php

namespace Repositories;

use App\Models\Province;
use Illuminate\Database\Eloquent\Collection;

class ProvinceRepository
{
    public function list(array $filters = []): Collection
    {
        $query = Province::select('id', 'name')->orderBy('name');

        if (!empty($filters['campaign_id'])) {
            $query->whereHas('campaigns', function ($q) use ($filters) {
                $q->where('id', $filters['campaign_id']);
            });
        }

        return $query->get();
    }
}
