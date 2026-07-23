<?php

namespace App\Repositories;

use App\Models\InfoSession;

class InfoSessionRepository
{
    public function find(int $id)
    {
        return InfoSession::with([
                'hosts:id,info_session_id,host_name',
                'province:id,name',
                'district:id,name',
                'commune:id,name',
                'village:id,commune_id,name',
                'campaign:id,name',
            ])
            ->findOrFail($id);
    }

    public function list(array $filters = [])
    {
        return InfoSession::query()
            ->select(
                'info_sessions.*',
                'villages.name as village',
                'communes.name as commune',
                'districts.name as district',
                'provinces.name as province'
            )
            ->join('villages', 'villages.id', '=', 'info_sessions.village_id')
            ->join('communes', 'communes.id', '=', 'villages.commune_id')
            ->join('districts', 'districts.id', '=', 'communes.district_id')
            ->join('provinces', 'provinces.id', '=', 'districts.province_id')
            ->with(['hosts:id,info_session_id,host_name'])
            ->when($filters['campaign_id'] ?? null, fn($q, $v) => $q->where('info_sessions.campaign_id', $v))
            ->when($filters['village_id'] ?? null, fn($q, $v) => $q->where('info_sessions.village_id', $v))
            ->when($filters['partner_type'] ?? null, fn($q, $v) => $q->where('info_sessions.partner_type', $v))
            ->when($filters['province'] ?? null, fn($q, $v) => $q->where('provinces.name', $v))
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
            'village_id'          => $data['village_id'] ?? null,
            'school'              => $data['school'] ?? null,
            'session_date'        => $data['session_date'] ?? null,
            'session_time'        => $data['session_time'] ?? null,
            'expected_attendance' => $data['expected_attendance'] ?? null,
            'attendance_count'    => $data['attendance_count'] ?? null,
            'partner_type'        => $data['partner_type'] ?? null,
            'partner_name'        => $data['partner_name'] ?? null,
            'host_by'             => $data['host_by'] ?? null,
        ], fn($v) => $v !== null));

        if (isset($data['hosts'])) {
            $session->hosts()->delete();
            $session->hosts()->createMany(
                array_map(fn($h) => ['host_name' => $h['host_name']], $data['hosts'])
            );
        }

        return $session->refresh()->load('hosts');
    }

    public function store(array $data)
    {
        $session = InfoSession::create([
            'campaign_id'         => $data['campaign_id'],
            'province_id'         => $data['province_id'] ?? null,
            'district_id'         => $data['district_id'] ?? null,
            'commune_id'          => $data['commune_id'] ?? null,
            'village_id'          => $data['village_id'],
            'school'              => $data['school'],
            'session_date'        => $data['session_date'],
            'session_time'        => $data['session_time'],
            'expected_attendance' => $data['expected_attendance'],
            'attendance_count'    => $data['attendance_count'] ?? 0,
            'partner_type'        => $data['partner_type'] ?? null,
            'partner_name'        => $data['partner_name'] ?? null,
            'host_by'             => $data['host_by'] ?? null,
        ]);

        if (isset($data['hosts']) && is_array($data['hosts'])) {
            $session->hosts()->createMany(
                array_map(fn($h) => ['host_name' => $h['host_name']], $data['hosts'])
            );
        }

        return $session->refresh()->load('hosts');
    }

    public function delete(int $id): void
    {
        $session = InfoSession::findOrFail($id);
        $session->hosts()->delete();
        $session->delete();
    }
}
