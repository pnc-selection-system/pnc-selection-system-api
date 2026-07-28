<?php

namespace App\Repositories;

use App\Models\InfoSession;

class InfoSessionRepository
{
    private array $withRelations = [
        'hosts:id,info_session_id,host_by',
        'province:id,name',
        'district:id,name',
        'commune:id,name',
        'village:id,commune_id,name',
        'campaign:id,name',
    ];

    public function find(int $id)
    {
        return InfoSession::with($this->withRelations)
            ->findOrFail($id);
    }

    public function list(array $filters = [])
    {
        return InfoSession::query()
            ->with($this->withRelations)
            ->when($filters['campaign_id'] ?? null, fn($q, $v) => $q->where('info_sessions.campaign_id', $v))
            ->when($filters['village_id'] ?? null, fn($q, $v) => $q->where('info_sessions.village_id', $v))
            ->when($filters['partner_type'] ?? null, fn($q, $v) => $q->where('info_sessions.partner_type', $v))
            ->when($filters['province'] ?? null, fn($q, $v) => $q->whereHas('province', fn($q) => $q->where('name', $v)))
            ->when($filters['start_date'] ?? null, fn($q, $v) => $q->whereDate('info_sessions.session_date', '>=', $v))
            ->when($filters['end_date'] ?? null, fn($q, $v) => $q->whereDate('info_sessions.session_date', '<=', $v))
            ->when($filters['campaign_year'] ?? null, fn($q, $v) => $q->whereHas('campaign', fn($q) => $q->where('year', $v)))
            ->orderBy('info_sessions.id', 'desc')
            ->paginate($filters['per_page'] ?? 10);
    }

    public function update(int $id, array $data)
    {
        $session = InfoSession::findOrFail($id);

        $session->update(array_filter([
            'campaign_id'         => $data['campaign_id'] ?? null,
            'province_id'         => $data['province_id'] ?? null,
            'district_id'         => $data['district_id'] ?? null,
            'commune_id'          => $data['commune_id'] ?? null,
            'village_id'          => $data['village_id'] ?? $session->village_id,
            'school'              => !empty($data['school']) ? $data['school'] : $session->school,
            'session_date'        => $data['session_date'] ?? null,
            'session_time'        => !empty($data['session_time']) ? $data['session_time'] : $session->session_time,
            'expected_attendance' => $data['expected_attendance'] ?? null,
            'attendance_count'    => $data['attendance_count'] ?? null,
            'partner_type'        => $data['partner_type'] ?? null,
            'partner_name'        => $data['partner_name'] ?? null,
            'host_by'             => $data['host_by'] ?? null,
            'venue'               => $data['venue'] ?? $session->venue,
            'location'            => $data['location'] ?? $session->location,
            'department'          => $data['department'] ?? $session->department,
            'generation'          => $data['generation'] ?? $session->generation,
            'created_by'          => $data['created_by'] ?? $session->created_by,
        ], fn($v) => $v !== null));

        if (isset($data['hosts'])) {
            $session->hosts()->delete();
            $session->hosts()->createMany(
                array_map(fn($h) => ['host_by' => $h['host_by']], $data['hosts'])
            );
        } elseif (!empty($data['host_by'])) {
            $session->hosts()->delete();
            $session->hosts()->create([
                'host_by' => $data['host_by']
            ]);
        }

        return $session->refresh()->load($this->withRelations);
    }

    public function store(array $data)
    {
        $session = InfoSession::create([
            'campaign_id'         => $data['campaign_id'],
            'province_id'         => $data['province_id'] ?? null,
            'district_id'         => $data['district_id'] ?? null,
            'commune_id'          => $data['commune_id'] ?? null,
            'village_id'          => $data['village_id']
                ?? \App\Models\Village::query()->value('id')
                ?? throw new \RuntimeException('No villages found in database. Seed villages or run: php artisan migrate'),
            'school'              => !empty($data['school']) ? $data['school'] : '-',
            'session_date'        => $data['session_date'],
            'session_time'        => !empty($data['session_time']) ? $data['session_time'] : '00:00:00',
            'expected_attendance' => $data['expected_attendance'],
            'attendance_count'    => $data['attendance_count'] ?? 0,
            'partner_type'        => $data['partner_type'] ?? null,
            'partner_name'        => $data['partner_name'] ?? null,
            'host_by'             => $data['host_by'] ?? null,
            'venue'               => $data['venue'] ?? null,
            'location'            => $data['location'] ?? null,
            'department'          => $data['department'] ?? null,
            'generation'          => $data['generation'] ?? null,
            'created_by'          => $data['created_by'] ?? null,
        ]);

        if (isset($data['hosts']) && is_array($data['hosts'])) {
            $session->hosts()->createMany(
                array_map(fn($h) => ['host_by' => $h['host_by']], $data['hosts'])
            );
        } elseif (!empty($data['host_by'])) {
            $session->hosts()->create([
                'host_by' => $data['host_by']
            ]);
        }

        return $session->refresh()->load($this->withRelations);
    }

    public function delete(int $id): void
    {
        $session = InfoSession::findOrFail($id);
        $session->hosts()->delete();
        $session->delete();
    }
}
