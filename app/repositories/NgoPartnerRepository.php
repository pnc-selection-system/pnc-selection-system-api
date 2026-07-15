<?php

namespace Repositories;

use App\Models\NgoPartner;
use Illuminate\Database\Eloquent\Collection;

class NgoPartnerRepository
{
    public function list(array $filters = [])
    {
        return NgoPartner::select('id', 'name', 'type', 'address', 'phone', 'email', 'active', 'status')
            ->with('contactPersons:id,ngo_partner_id,full_name,phone,email')
            ->when(
                !empty($filters['search']),
                fn($q) => $q->where('name', 'like', '%'.$filters['search'].'%')
            )
            ->when(
                !empty($filters['type']),
                fn($q) => $q->where('type', $filters['type'])
            )
            ->when(
                !empty($filters['status']),
                fn($q) => $q->where('status', $filters['status'])
            )
            ->latest('id')
            ->paginate($filters['per_page'] ?? 10);
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
        $ngoPartner->delete();
    }
}
