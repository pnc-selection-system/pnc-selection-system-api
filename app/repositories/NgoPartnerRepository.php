<?php

namespace Repositories;

use App\Models\NgoPartner;
use Illuminate\Database\Eloquent\Collection;

class NgoPartnerRepository
{
    public function list(array $filters = []): Collection
    {
        $query = NgoPartner::query();

        if (! empty($filters['search'])) {
            $query->where('name', 'like', '%'.$filters['search'].'%');
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->get();
    }

    public function create(array $data): NgoPartner
    {
        return NgoPartner::create($data);
    }

    public function find(NgoPartner $ngoPartner): NgoPartner
    {
        return $ngoPartner;
    }

    public function update(NgoPartner $ngoPartner, array $data): NgoPartner
    {
        $ngoPartner->update($data);

        return $ngoPartner;
    }

    public function delete(NgoPartner $ngoPartner): void
    {
        $ngoPartner->delete($ngoPartner);
    }
}
