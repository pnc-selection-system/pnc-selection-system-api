<?php

namespace Services;

use Repositories\CommuneRepository;

class CommuneServices
{
    public function __construct(protected CommuneRepository $communeRepository)
    {
    }

    public function list(array $filters = [])
    {
        return $this->communeRepository->list($filters);
    }
}
