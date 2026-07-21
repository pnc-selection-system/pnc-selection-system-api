<?php

namespace Repositories;

use App\Models\ExamSubject;

class ExamSubjectRepository
{
    public function list(array $filters = [])
    {
        return ExamSubject::select('id', 'campaign_id', 'name', 'max_score', 'weight', 'is_delete', 'created_at')
            ->with('campaign:id,name,status')
            ->when(
                !empty($filters['campaign_id']),
                fn($q) => $q->where('campaign_id', (int) $filters['campaign_id'])
            )
            ->latest('id')
            ->get();
    }

    public function create(array $data): ExamSubject
    {
        return ExamSubject::create($data);
    }

    public function find(ExamSubject $examSubject): ExamSubject
    {
        $examSubject->load('campaign:id,name,status');

        return $examSubject;
    }

    public function update(ExamSubject $examSubject, array $data): ExamSubject
    {
        $examSubject->update($data);
        $examSubject->refresh();
        $examSubject->load('campaign:id,name,status');

        return $examSubject;
    }

    /**
     * Soft delete: set is_delete = true instead of removing the record.
     */
    public function delete(ExamSubject $examSubject): void
    {
        $examSubject->update(['is_delete' => true]);
    }
}
