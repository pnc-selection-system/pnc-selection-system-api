<?php

namespace Services;

use App\Models\NgoPartner;
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
}
