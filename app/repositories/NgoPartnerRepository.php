<?php

namespace Repositories;

use App\Models\Cadidate;
use App\Models\CommunicationLog;
use App\Models\NgoContactPersion;
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

    public function candidates(int $ngoId, array $filters = []): Collection
    {
        $query = Cadidate::where('ngo_id', $ngoId);

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('first_name', 'like', '%'.$filters['search'].'%')
                  ->orWhere('last_name', 'like', '%'.$filters['search'].'%')
                  ->orWhere('phone', 'like', '%'.$filters['search'].'%');
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

        return $query->with(['campaign', 'province'])->latest()->get();
    }

    public function listContactPersons(int $ngoPartnerId, array $filters = []): Collection
    {
        $query = NgoContactPersion::where('ngo_partner_id', $ngoPartnerId);

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('full_name', 'like', '%'.$filters['search'].'%')
                  ->orWhere('email', 'like', '%'.$filters['search'].'%')
                  ->orWhere('phone', 'like', '%'.$filters['search'].'%')
                  ->orWhere('role', 'like', '%'.$filters['search'].'%');
            });
        }

        return $query->with('ngoPartner')->latest()->get();
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
        $contactPerson->delete();
    }

    public function listCommunicationLogs(int $ngoPartnerId): Collection
    {
        return CommunicationLog::where('ngo_partner_id', $ngoPartnerId)
            ->latest()
            ->get();
    }

    public function createCommunicationLog(int $ngoPartnerId, array $data): CommunicationLog
    {
        $data['ngo_partner_id'] = $ngoPartnerId;

        return CommunicationLog::create($data);
    }
}
