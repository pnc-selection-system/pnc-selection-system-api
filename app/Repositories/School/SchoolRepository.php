<?php

namespace App\Repositories\School;

use App\Models\School;
use Illuminate\Pagination\LengthAwarePaginator;

class SchoolRepository
{
    public function getPaginated(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = School::query();

        if (isset($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        if (isset($filters['province_id'])) {
            $query->where('province_id', $filters['province_id']);
        }

        return $query->paginate($perPage);
    }

    public function findById(int $id)
    {
        return School::findOrFail($id);
    }

    public function create(array $data)
    {
        return School::create($data);
    }

    public function update(School $school, array $data)
    {
        $school->update($data);
        return $school;
    }

    public function delete(School $school): void
    {
        $school->delete();
    }
}