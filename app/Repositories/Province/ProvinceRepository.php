<?php

namespace App\Repositories\Province;

use App\Models\Province;
use Illuminate\Pagination\LengthAwarePaginator;

class ProvinceRepository
{
    public function getPaginated(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = Province::query();

        if (isset($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        return $query->paginate($perPage);
    }

    public function findById(int $id)
    {
        return Province::findOrFail($id);
    }

    public function create(array $data)
    {
        return Province::create($data);
    }

    public function update(Province $province, array $data)
    {
        $province->update($data);
        return $province;
    }

    public function delete(Province $province): void
    {
        $province->delete();
    }
}