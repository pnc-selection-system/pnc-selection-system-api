<?php

namespace App\Repositories\Attendance;

use App\Models\Attendance;
use App\Models\InfoSession;

class AttendanceRepository
{
    public function createOrUpdate(int $infoSessionId, array $data)
    {
        $session = InfoSession::findOrFail($infoSessionId);
        
        $attendance = $session->attendances()->first();
        
        if ($attendance) {
            $attendance->update($data);
        } else {
            $attendance = $session->attendances()->create($data);
        }
        
        return $attendance;
    }

    public function findByInfoSessionId(int $infoSessionId)
    {
        return Attendance::where('info_session_id', $infoSessionId)->first();
    }
}