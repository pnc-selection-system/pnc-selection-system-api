<?php

namespace Repositories;

use App\Models\Province;

class ProvinceRepository
{
    public function list(array $filters = [])
    {
        return Province::select('id', 'name')->orderBy('name')->get();
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

        return $province->fresh();
    }

    public function delete(Province $province): void
    {
        $province->delete();
    }
}
