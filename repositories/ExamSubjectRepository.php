<?php

namespace Repositories;

use App\Models\ExamSubject;
use Illuminate\Database\Eloquent\Collection;

class ExamSubjectRepository
{
    public function list(array $filters = []): Collection
    {
        $query = ExamSubject::query();

        if (! empty($filters['campaign_id'])) {
            $query->where('campaign_id', (int) $filters['campaign_id']);
        }

        return $query->latest()->get();
    }

    public function create(array $data): ExamSubject
    {
        return ExamSubject::create($data);
    }

    public function find(ExamSubject $examSubject): ExamSubject
    {
        return $examSubject;
    }

    public function update(ExamSubject $examSubject, array $data): ExamSubject
    {
        $examSubject->update($data);

        return $examSubject;
    }

    public function delete(ExamSubject $examSubject): void
    {
        $examSubject->delete();
    }
}
