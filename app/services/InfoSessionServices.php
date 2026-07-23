<?php

namespace App\Services;

use App\Repositories\InfoSessionRepository;
use Illuminate\Support\Facades\DB;

class InfoSessionServices
{
    public function __construct(
        protected InfoSessionRepository $repository
    ){}

    public function find(int $id)
    {
        return $this->repository->find($id);
    }

    public function list(array $filters = [])
    {
        return $this->repository->list($filters);
    }

    public function update(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            return $this->repository->update($id, $data);
        });
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {

            return $this->repository->store($data);

        });
    }

    public function delete(int $id): void
    {
        DB::transaction(function () use ($id) {
            $this->repository->delete($id);
        });
    }
}