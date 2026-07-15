<?php

namespace Repositories;

use App\Models\Candidate;
use App\Models\NgoContactPersion;
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

    public function candidates(int $ngoId, array $filters = []): Collection
    {
        $query = Candidate::query() ->where('ngo_id', $ngoId);

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('first_name', 'like', '%'.$filters['search'].'%')
                  ->orWhere('last_name', 'like', '%'.$filters['search'].'%')
                  ->orWhere('phone', 'like', '%'.$filters['search'].'%')
                  ->orWhere('email', 'like', '%'.$filters['search'].'%');
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['campaign_id'])) {
            $query->where('campaign_id', (int) $filters['campaign_id']);
        }

        if (! empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        return $query->with(['campaign', 'province', 'school'])->latest()->get();
    }

    public function listContactPersons(int $ngoPartnerId, array $filters = []): Collection
    {
        $ngoPartner = NgoPartner::findOrFail($ngoPartnerId);

        $query = $ngoPartner->contactPersons();

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('full_name', 'like', '%'.$filters['search'].'%')
                  ->orWhere('email', 'like', '%'.$filters['search'].'%')
                  ->orWhere('phone', 'like', '%'.$filters['search'].'%')
                  ->orWhere('role', 'like', '%'.$filters['search'].'%');
            });
        }

         return $query->latest()->get();
    }

    public function createContactPerson(int $ngoPartnerId, array $data): NgoContactPersion
    {
        $data['ngo_partner_id'] = $ngoPartnerId;

        return NgoContactPersion::create($data);
    }

    public function findContactPerson(NgoContactPersion $contactPerson): NgoContactPersion
    {
        return $contactPerson;
    }

    public function updateContactPerson(NgoContactPersion $contactPerson, array $data): NgoContactPersion
    {
        $contactPerson->update($data);

        return $contactPerson;
    }

    public function deleteContactPerson(NgoContactPersion $contactPerson): void
    {
        $contactPerson->delete($contactPerson);
    }
}
