<?php

namespace Repositories;

use App\Models\Cadidate;
use App\Models\CandidateStatusHistory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class CandidateRepository
{
    public function list(array $filters = []): Collection
    {
        $query = Cadidate::query();

        if (! empty($filters['search'])) {
            $searchTerm = '%'.$filters['search'].'%';
            $query->where(function ($q) use ($filters, $searchTerm) {
                $q->where('student_id', 'like', $searchTerm)
                  ->orWhere('first_name', 'like', $searchTerm)
                  ->orWhere('last_name', 'like', $searchTerm)
                  ->orWhere('phone', 'like', $searchTerm)
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", [$searchTerm]);
            });
        }

        if (! empty($filters['campaign_id'])) {
            $query->where('campaign_id', (int) $filters['campaign_id']);
        }

        if (! empty($filters['province_id'])) {
            $query->where('province_id', (int) $filters['province_id']);
        }

        if (! empty($filters['school_name'])) {
            $query->where('school_name', 'like', '%'.$filters['school_name'].'%');
        }

        if (! empty($filters['ngo_id'])) {
            $query->where('ngo_id', (int) $filters['ngo_id']);
        }

        if (! empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->with(['campaign', 'province', 'referringNgo'])->latest()->get();
    }

    public function create(array $data): Cadidate
    {
        $candidate = Cadidate::create($data);

        // Record initial status history for Registered status
        CandidateStatusHistory::create([
            'candidate_id' => $candidate->id,
            'status' => $data['status'] ?? 'Register',
            'changed_by' => Auth::id(),
            'changed_at' => now(),
        ]);

        return $candidate;
    }

    public function find(Cadidate $candidate): Cadidate
    {
        return $candidate;
    }

    public function update(Cadidate $candidate, array $data): Cadidate
    {
        // Check if status is being changed
        $oldStatus = $candidate->status;

        $candidate->update($data);

        // Record status history if status changed
        if (isset($data['status']) && $data['status'] !== $oldStatus) {
            CandidateStatusHistory::create([
                'candidate_id' => $candidate->id,
                'status' => $data['status'],
                'changed_by' => Auth::id(),
                'changed_at' => now(),
            ]);
        }

        return $candidate;
    }

    public function delete(Cadidate $candidate): void
    {
        $candidate->delete();
    }

    /**
     * Record a status change without updating the candidate's current status.
     * Used when other processes (like assessment submission) update the status separately.
     */
    public function recordStatusHistory(int $candidateId, string $status, ?int $changedBy = null): void
    {
        CandidateStatusHistory::create([
            'candidate_id' => $candidateId,
            'status' => $status,
            'changed_by' => $changedBy ?? Auth::id(),
            'changed_at' => now(),
        ]);
    }
}
