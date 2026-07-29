<?php

namespace Services;

use App\Models\AssessmentForm;
use App\Models\AssessmentResponse;
use App\Models\Candidate;
use App\Models\CandidateStatusHistory;
use App\Models\User;
use Repositories\CandidateRepository;

class CandidateServices
{
    public function __construct(protected CandidateRepository $candidateRepository) {}

    public function stats(): array
    {
        return $this->candidateRepository->stats();
    }

    public function list(array $filters = [])
    {
        return $this->candidateRepository->list($filters);
    }

    public function create(array $data): Candidate
    {
        return $this->candidateRepository->create($data);
    }

    public function find(Candidate $candidate): Candidate
    {
        return $this->candidateRepository->find($candidate);
    }

    public function update(Candidate $candidate, array $data): Candidate
    {
        return $this->candidateRepository->update($candidate, $data);
    }

    public function delete(Candidate $candidate): void
    {
        $this->candidateRepository->delete($candidate);
    }

    public function assessmentResult(Candidate $candidate): ?array
    {
        $response = AssessmentResponse::where('candidate_id', $candidate->id)
            ->orderBy('id', 'desc')
            ->first();

        if (! $response) {
            return null;
        }

        // Get form info
        $form = AssessmentForm::find($response->assessment_form_id ?? $response->form_id);

        // Get evaluator name if submitted_by exists
        $evaluatedBy = 'System';
        if (! empty($response->submitted_by)) {
            $user = User::find($response->submitted_by);
            if ($user) {
                $evaluatedBy = $user->name;
            }
        }

        return [
            'total_score' => (float) ($response->total_score ?? 0),
            'passed' => (bool) ($response->passed ?? false),
            'form_name' => $form?->name ?? 'Unknown Form',
            'pass_threshold' => (int) ($form?->pass_threshold ?? 0),
            'submitted_at' => $response->created_at?->toIso8601String(),
            'evaluated_by' => $evaluatedBy,
        ];
    }

    public function statusHistory(Candidate $candidate): array
    {
        return CandidateStatusHistory::where('candidate_id', $candidate->id)
            ->with('changedBy')
            ->orderBy('changed_at')
            ->get()
            ->map(fn($entry) => [
                'id' => $entry->id,
                'status' => $entry->status,
                'changed_by' => $entry->changedBy?->name ?? 'System',
                'changed_at' => $entry->changed_at->toIso8601String(),
            ])
            ->toArray();
    }
}
