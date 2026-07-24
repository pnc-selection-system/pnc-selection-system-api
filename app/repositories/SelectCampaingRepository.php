<?php

namespace Repositories;

use App\Models\SelectCampaing;

class SelectCampaingRepository
{
    public function list(array $filters = [])
    {
        return SelectCampaing::select('id', 'name', 'year', 'condidate_total', 'province_total', 'start_date', 'end_date', 'status')
            ->with('provinces:id,name')
            ->when(
                !empty($filters['status']),
                fn($q) => $q->where('status', $filters['status'])
            )
            ->when(
                !empty($filters['year']),
                fn($q) => $q->where('year', (int) $filters['year'])
            )
            ->latest('id')
            ->paginate($filters['per_page'] ?? 10);
    }

    public function create(array $data): SelectCampaing
    {
<<<<<<< HEAD
        return SelectCampaing::create($data); 
=======
        $provinceIds = $data['province_ids'] ?? [];
        unset($data['province_ids']);

        $selectCampaing = SelectCampaing::create($data);

        if (!empty($provinceIds)) {
            $selectCampaing->provinces()->sync($provinceIds);
        }

        $selectCampaing->province_total = count($provinceIds);
        $selectCampaing->save();

        $selectCampaing->load('provinces:id,name');

        return $selectCampaing;
>>>>>>> 32949326a3c899c0987e8e0bf1925d80e70b7891
    }

    public function find(SelectCampaing $selectCampaing): SelectCampaing
    {
        $selectCampaing->load('provinces:id,name');

        return $selectCampaing;
    }
    public function update(SelectCampaing $selectCampaing, array $data): SelectCampaing
    {
        $provinceIds = $data['province_ids'] ?? null;
        unset($data['province_ids']);

        $selectCampaing->update($data);

        if ($provinceIds !== null) {
            $selectCampaing->provinces()->sync($provinceIds);
            $selectCampaing->province_total = count($provinceIds);
            $selectCampaing->save();
        }

        $selectCampaing->refresh();
        $selectCampaing->load('provinces:id,name');

        return $selectCampaing;
    }

    public function delete(SelectCampaing $selectCampaing): void
    {
<<<<<<< HEAD
        $selectCampaing->delete($selectCampaing);
=======
        $selectCampaing->provinces()->detach();
        $selectCampaing->delete();
>>>>>>> 32949326a3c899c0987e8e0bf1925d80e70b7891
    }
}
