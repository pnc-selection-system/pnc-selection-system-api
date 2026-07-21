<?php

namespace Repositories;

use App\Models\Candidate;
use Illuminate\Database\Eloquent\Collection;

class CandidateRepository
{
    public function list(array $filters = []): Collection
    {
        $query = Candidate::query();

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('first_name', 'like', '%'.$filters['search'].'%')
                  ->orWhere('last_name', 'like', '%'.$filters['search'].'%')
                  ->orWhere('phone', 'like', '%'.$filters['search'].'%');
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

    public function create(array $data): Candidate
    {
        return Candidate::create($data);
    }

    public function find(Candidate $candidate): Candidate
    {
        return $candidate;
    }

    public function update(Candidate $candidate, array $data): Candidate
    {
        $candidate->update($data);

        return $candidate;
    }

    public function delete(Candidate $candidate): void
    {
        $candidate->delete($candidate);
    }
}
