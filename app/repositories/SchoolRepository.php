<?php

namespace Repositories;

use App\Models\School;
use Illuminate\Database\Eloquent\Collection;

class SchoolRepository
{
    public function list(array $filters = []): Collection
    {
        $query = School::query();

        if (! empty($filters['search'])) {
            $query->where('name', 'like', '%'.$filters['search'].'%');
        }

        if (! empty($filters['province_id'])) {
            $query->where('province_id', (int) $filters['province_id']);
        }

        return $query->with('province')->latest()->get();
    }

    public function create(array $data): School
    {
        return School::create($data);
    }

    public function find(School $school): School
    {
        return $school;
    }

    public function update(School $school, array $data): School
    {
        $school->update($data);

        return $school;
    }

    public function delete(School $school): void
    {
        $school->delete($school);
    }
}
