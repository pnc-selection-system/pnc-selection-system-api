<?php

namespace Repositories;

use App\Models\Province;
use Illuminate\Database\Eloquent\Collection;

class ProvinceRepository
{
    public function list(array $filters = []): Collection
    {
        $query = Province::query();

        if (! empty($filters['search'])) {
            $query->where('name', 'like', '%'.$filters['search'].'%');
        }

        return $query->latest()->get();
    }

    public function create(array $data): Province
    {
        return Province::create($data);
    }

    public function find(Province $province): Province
    {
        return $province;
    }

    public function update(Province $province, array $data): Province
    {
        $province->update($data);

        return $province;
    }

    public function delete(Province $province): void
    {
        $province->delete($province);
    }
}
