<?php

namespace Repositories;

use App\Models\NgoContactPersion;
use Illuminate\Database\Eloquent\Collection;

class NgoContactPersonRepository
{
    public function list(int $ngoPartnerId, array $filters = []): Collection
    {
        $query = NgoContactPersion::where('ngo_partner_id', $ngoPartnerId);

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('full_name', 'like', '%'.$filters['search'].'%')
                  ->orWhere('email', 'like', '%'.$filters['search'].'%')
                  ->orWhere('phone', 'like', '%'.$filters['search'].'%');
            });
        }

        return $query->with('ngoPartner')->latest()->get();
    }

    public function create(int $ngoPartnerId, array $data): NgoContactPersion
    {
        $data['ngo_partner_id'] = $ngoPartnerId;

        return NgoContactPersion::create($data);
    }

    public function find(NgoContactPersion $contactPerson): NgoContactPersion
    {
        return $contactPerson;
    }

    public function update(NgoContactPersion $contactPerson, array $data): NgoContactPersion
    {
        $contactPerson->update($data);

        return $contactPerson;
    }

    public function delete(NgoContactPersion $contactPerson): void
    {
        $contactPerson->delete();
    }
}
