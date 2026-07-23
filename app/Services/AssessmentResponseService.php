<?php

namespace App\Services;

use App\Repositories\AssessmentResponseRepository;
use Illuminate\Support\Facades\DB;

class AssessmentResponseService
{
    public function __construct(
        protected AssessmentResponseRepository $repository
    ) {}

    public function list(array $filters = [])
    {
        return $this->repository->list($filters);
    }

    public function submit(array $data)
    {
        return DB::transaction(function () use ($data) {
            return $this->repository->store($data);
        });
    }
}
