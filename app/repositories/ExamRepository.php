<?php

namespace Repositories;

use App\Models\Exam;
use Illuminate\Database\Eloquent\Collection;

class ExamRepository
{
    public function list(array $filters = [])
    {
        return Exam::select('id', 'campaign_id', 'exam_date', 'publish_status', 'created_at')
            ->with('campaign:id,name,year,status')
            ->when(
                !empty($filters['campaign_id']),
                fn($q) => $q->where('campaign_id', (int) $filters['campaign_id'])
            )
            ->when(
                array_key_exists('publish_status', $filters),
                fn($q) => $q->where('publish_status', filter_var($filters['publish_status'], FILTER_VALIDATE_BOOLEAN))
            )
            ->latest('id')
            ->paginate($filters['per_page'] ?? 10);
    }

    public function create(array $data): Exam
    {
        return Exam::create($data);
    }

    public function find(Exam $exam): Exam
    {
        return $exam;
    }

    public function update(Exam $exam, array $data): Exam
    {
        $exam->update($data);

        return $exam;
    }

    public function delete(Exam $exam): void
    {
        $exam->delete();
    }
}
