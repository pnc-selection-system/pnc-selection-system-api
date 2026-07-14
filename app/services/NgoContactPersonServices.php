<?php

namespace Services;

use App\Models\NgoContactPersion;
use Repositories\NgoContactPersonRepository;

class NgoContactPersonServices
{
    public function __construct(protected NgoContactPersonRepository $contactPersonRepository) {}

    public function list(int $ngoPartnerId, array $filters = [])
    {
        return $this->contactPersonRepository->list($ngoPartnerId, $filters);
    }

    public function create(int $ngoPartnerId, array $data): NgoContactPersion
    {
        return $this->contactPersonRepository->create($ngoPartnerId, $data);
    }

    public function find(NgoContactPersion $contactPerson): NgoContactPersion
    {
        return $this->contactPersonRepository->find($contactPerson);
    }

    public function update(NgoContactPersion $contactPerson, array $data): NgoContactPersion
    {
        return $this->contactPersonRepository->update($contactPerson, $data);
    }

    public function delete(NgoContactPersion $contactPerson): void
    {
        $this->contactPersonRepository->delete($contactPerson);
    }
}
