<?php

namespace App\Repositories;

use App\Models\InfoSession;

class InfoSessionRepository
{
    public function store(array $data)
    {
        $session = InfoSession::create([
            'campaign_id'         => $data['campaign_id'],
            'village_id'          => $data['village_id'],
            'school_name'         => $data['school_name'],
            'session_date'        => $data['session_date'],
            'session_time'        => $data['session_time'],
            'expected_attendance' => $data['expected_attendance'],
            'attendance_count'    => $data['attendance_count'] ?? 0,
        ]);

        foreach ($data['hosts'] as $host) {

            $session->hosts()->create([
                'host_name' => $host['host_name']
            ]);

        }

        return $session->refresh()->load('hosts');
    }
}