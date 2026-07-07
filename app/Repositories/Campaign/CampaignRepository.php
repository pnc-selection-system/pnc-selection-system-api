<?php

namespace App\Repositories\Campaign;

use App\Models\Campaign;

class CampaignRepository implements CampaignRepositoryInterface
{
    public function getAll()
    {
        return Campaign::all();
    }

    public function findById(int $id)
    {
        return Campaign::findOrFail($id);
    }

    public function create(array $data)
    {
        return Campaign::create($data);
    }

    public function update(int $id, array $data)
    {
        $campaign = Campaign::findOrFail($id);
        $campaign->update($data);
        return $campaign;
    }

    public function delete(int $id)
    {
        return Campaign::findOrFail($id)->delete();
    }
}
