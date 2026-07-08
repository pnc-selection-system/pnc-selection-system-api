<?php

namespace Repositories;

use App\Models\Exam;
use Illuminate\Database\Eloquent\Collection;

class ExamRepository
{
    public function list(array $filters = []): Collection
    {
        $query = Exam::query();

        if (!empty($filters['campaign_id'])) {
            $query->where('campaign_id', (int) $filters['campaign_id']);
        }

        if (array_key_exists('publish_status', $filters)) {
            $query->where('publish_status', filter_var($filters['publish_status'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query->latest()->get();
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
