<?php

namespace App\Services;

use App\Repositories\InfoSessionRepository;
use Illuminate\Support\Facades\DB;

class InfoSessionServices
{
    public function __construct(
        protected InfoSessionRepository $repository
    ){}

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {

            return $this->repository->store($data);

        });
    }
}