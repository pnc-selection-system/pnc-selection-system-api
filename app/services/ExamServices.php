<?php

namespace Services;

use App\Models\Exam;
use Repositories\ExamRepository;

class ExamServices
{
    public function __construct(protected ExamRepository $examRepository) {}

    public function list(array $filters = [])
    {
        return $this->examRepository->list($filters);
    }

    public function create(array $data): Exam
    {
        return $this->examRepository->create($data);
    }

    public function find(Exam $exam): Exam
    {
        return $this->examRepository->find($exam);
    }

    public function update(Exam $exam, array $data): Exam
    {
        return $this->examRepository->update($exam, $data);
    }

    public function delete(Exam $exam): void
    {
        $this->examRepository->delete($exam);
    }
}
