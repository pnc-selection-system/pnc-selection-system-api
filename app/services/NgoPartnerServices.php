<?php

namespace Services;

use App\Models\NgoContactPersion;
use App\Models\NgoPartner;
use Illuminate\Database\Eloquent\Collection;
use Repositories\NgoPartnerRepository;

class NgoPartnerServices
{
    public function __construct(protected NgoPartnerRepository $ngoPartnerRepository) {}

    public function list(array $filters = [])
    {
        return $this->ngoPartnerRepository->list($filters);
    }

    public function create(array $data): NgoPartner
    {
        return $this->ngoPartnerRepository->create($data);
    }

    public function find(NgoPartner $ngoPartner): NgoPartner
    {
        return $this->ngoPartnerRepository->find($ngoPartner);
    }

    public function update(NgoPartner $ngoPartner, array $data): NgoPartner
    {
        return $this->ngoPartnerRepository->update($ngoPartner, $data);
    }

    public function delete(NgoPartner $ngoPartner): void
    {
        $this->ngoPartnerRepository->delete($ngoPartner);
    }

    public function candidates(int $ngoId, array $filters = []): Collection
    {
        return $this->ngoPartnerRepository->candidates($ngoId, $filters);
    }

    public function listContactPersons(int $ngoPartnerId, array $filters = []): Collection
    {
        return $this->ngoPartnerRepository->listContactPersons($ngoPartnerId, $filters);
    }

    public function createContactPerson(int $ngoPartnerId, array $data): NgoContactPersion
    {
        return $this->ngoPartnerRepository->createContactPerson($ngoPartnerId, $data);
    }

    public function findContactPerson(NgoContactPersion $contactPerson): NgoContactPersion
    {
        return $this->ngoPartnerRepository->findContactPerson($contactPerson);
    }

    public function updateContactPerson(NgoContactPersion $contactPerson, array $data): NgoContactPersion
    {
        return $this->ngoPartnerRepository->updateContactPerson($contactPerson, $data);
    }

    public function deleteContactPerson(NgoContactPersion $contactPerson): void
    {
        $this->ngoPartnerRepository->deleteContactPerson($contactPerson);
    }
}
