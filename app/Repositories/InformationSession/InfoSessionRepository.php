<?php

namespace App\Repositories\InformationSession;

use App\Models\InfoSession;
use Illuminate\Pagination\LengthAwarePaginator;

class InfoSessionRepository
{
    public function getPaginated(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = InfoSession::query();

        if (isset($filters['search'])) {
            $query->where('location', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%');
        }

        if (isset($filters['campaign_id'])) {
            $query->where('campaign_id', $filters['campaign_id']);
        }

        if (isset($filters['province_id'])) {
            $query->where('province_id', $filters['province_id']);
        }

        if (isset($filters['school_id'])) {
            $query->where('school_id', $filters['school_id']);
        }

        if (isset($filters['session_date_from'])) {
            $query->whereDate('session_date', '>=', $filters['session_date_from']);
        }

        if (isset($filters['session_date_to'])) {
            $query->whereDate('session_date', '<=', $filters['session_date_to']);
        }

        return $query->paginate($perPage);
    }

    public function findById(int $id)
    {
        return InfoSession::findOrFail($id);
    }

    public function create(array $data)
    {
        return InfoSession::create($data);
    }

    public function update(InfoSession $session, array $data)
    {
        $session->update($data);
        return $session;
    }

    public function delete(InfoSession $session): void
    {
        $session->delete();
    }
}