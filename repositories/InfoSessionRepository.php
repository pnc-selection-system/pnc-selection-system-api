<?php

namespace App\Repositories\InfoSession;

use App\Models\InfoSession;

class InfoSessionRepository implements
{
    public function list(array $filters)
    {
        $query = InfoSession::with(['province', 'school']);

        if (!empty($filters['campaign_id']))  $query->where('campaign_id', $filters['campaign_id']);
        if (!empty($filters['province_id']))  $query->where('province_id', $filters['province_id']);
        if (!empty($filters['school_id']))    $query->where('school_id', $filters['school_id']);
        if (!empty($filters['partner_type'])) $query->where('partner_type', $filters['partner_type']);
        if (!empty($filters['date_from']))    $query->whereDate('date', '>=', $filters['date_from']);
        if (!empty($filters['date_to']))      $query->whereDate('date', '<=', $filters['date_to']);

        $pageSize = $filters['page_size'] ?? 20;
        return $query->paginate($pageSize);
    }

    public function findById(int $id)
    {
        return InfoSession::with(['province', 'district', 'commune', 'village', 'school'])->findOrFail($id);
    }

    public function create(array $data)
    {
        return InfoSession::create($data);
    }

    public function update(int $id, array $data)
    {
        $session = InfoSession::findOrFail($id);
        $session->update($data);
        return $session;
    }

    public function delete(int $id)
    {
        return InfoSession::findOrFail($id)->delete();
    }

    public function updateAttendance(int $id, int $count)
    {
        $session = InfoSession::findOrFail($id);
        $session->update(['attendance_count' => $count]);
        return $session;
    }

    public function hasConflict(string $schoolId, string $date, string $time, ?int $excludeId = null): bool
    {
        return InfoSession::where('school_id', $schoolId)
            ->whereDate('date', $date)
            ->where('time', $time)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->exists();
    }
}
