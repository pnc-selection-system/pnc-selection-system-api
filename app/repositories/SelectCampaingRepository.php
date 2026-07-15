<?php

namespace Repositories;

use App\Models\SelectCampaing;
use Illuminate\Database\Eloquent\Collection;

class SelectCampaingRepository
{
    public function list(array $filters = [])
    {
        return SelectCampaing::select('id', 'name', 'year', 'condidate_total', 'start_date', 'end_date', 'status')
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
        return SelectCampaing::create($data);
    }

    public function find(SelectCampaing $selectCampaing): SelectCampaing
    {
        return $selectCampaing;
    }

    public function update(SelectCampaing $selectCampaing, array $data): SelectCampaing
    {
        $selectCampaing->update($data);
        $selectCampaing->refresh();

        return $selectCampaing;
    }

    public function delete(SelectCampaing $selectCampaing): void
    {
        $selectCampaing->delete();
    }
}
