<?php

namespace App\Repositories\InterestStudent;

use App\Models\InterestStudent;
use Illuminate\Pagination\LengthAwarePaginator;

class InterestStudentRepository
{
    public function getPaginated(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = InterestStudent::query();

        if (isset($filters['search'])) {
            $query->where('full_name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('phone', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('email', 'like', '%' . $filters['search'] . '%');
        }

        if (isset($filters['info_session_id'])) {
            $query->where('info_session_id', $filters['info_session_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage);
    }

    public function findById(int $id)
    {
        return InterestStudent::findOrFail($id);
    }

    public function create(array $data)
    {
        return InterestStudent::create($data);
    }

    public function update(InterestStudent $student, array $data)
    {
        $student->update($data);
        return $student;
    }

    public function delete(InterestStudent $student): void
    {
        $student->delete();
    }
}