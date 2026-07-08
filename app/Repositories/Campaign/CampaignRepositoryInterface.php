<?php

namespace App\Repositories\Campaign;

interface CampaignRepositoryInterface
{
    public function getAll();
    public function findById(int $id);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
    public function getPaginated(array $filters = [], int $perPage = 15);
}
