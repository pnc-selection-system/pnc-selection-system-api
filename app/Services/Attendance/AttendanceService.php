<?php

namespace App\Services\Attendance;

use App\Repositories\Attendance\AttendanceRepository;

class AttendanceService
{
    public function __construct(protected AttendanceRepository $repository) {}

    public function createOrUpdate(int $infoSessionId, array $data)
    {
        return $this->repository->createOrUpdate($infoSessionId, $data);
    }

    public function findByInfoSessionId(int $infoSessionId)
    {
        return $this->repository->findByInfoSessionId($infoSessionId);
    }
}