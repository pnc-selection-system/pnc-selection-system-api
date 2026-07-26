<?php

namespace App\Services;

use App\Repositories\AssessmentFormRepository;
use Illuminate\Support\Facades\DB;

class AssessmentFormService
{
    public function __construct(
        protected AssessmentFormRepository $repository
    ) {}

    public function list(array $filters = [])
    {
        return $this->repository->list($filters);
    }

    public function find(int $id)
    {
        return $this->repository->find($id);
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            return $this->repository->create($data);
        });
    }

    public function update(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            return $this->repository->update($id, $data);
        });
    }
}
