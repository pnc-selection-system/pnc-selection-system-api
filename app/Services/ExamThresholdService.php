<?php

namespace Services;

use App\Models\ExamThreshold;
use Repositories\ExamThresholdRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class ExamThresholdService
{
    protected ExamThresholdRepository $repository;

    public function __construct(ExamThresholdRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get overall threshold for a campaign
     */
    public function getOverallThreshold(int $campaignId): ?ExamThreshold
    {
        return $this->repository->getOverallThreshold($campaignId);
    }

    /**
     * Get threshold for a specific subject in a campaign
     */
    public function getSubjectThreshold(int $campaignId, int $subjectId): ?ExamThreshold
    {
        return $this->repository->getSubjectThreshold($campaignId, $subjectId);
    }

    /**
     * Get all thresholds for a campaign
     */
    public function getCampaignThresholds(int $campaignId): array
    {
        return $this->repository->getCampaignThresholds($campaignId);
    }

    /**
     * Create or update overall threshold
     */
    public function upsertOverallThreshold(int $campaignId, array $data): ExamThreshold
    {
        return $this->repository->upsertOverallThreshold($campaignId, $data);
    }

    /**
     * Create or update subject threshold
     */
    public function upsertSubjectThreshold(int $campaignId, int $subjectId, array $data): ExamThreshold
    {
        return $this->repository->upsertSubjectThreshold($campaignId, $subjectId, $data);
    }

    /**
     * Delete threshold
     */
    public function deleteThreshold(int $id): bool
    {
        return $this->repository->delete($id);
    }

    /**
     * Validate threshold data
     */
    public function validateThresholdData(array $data, ?int $subjectId = null): array
    {
        $errors = [];

        // Validate pass_score
        if (!isset($data['pass_score']) || $data['pass_score'] === null) {
            $errors['pass_score'] = 'Pass score is required';
        } elseif (!is_numeric($data['pass_score'])) {
            $errors['pass_score'] = 'Pass score must be a number';
        } elseif ($data['pass_score'] < 0 || $data['pass_score'] > 100) {
            $errors['pass_score'] = 'Pass score must be between 0 and 100';
        }

        // Validate must_pass_every_subject (only for overall threshold)
        if ($subjectId === null) {
            if (!isset($data['must_pass_every_subject'])) {
                $errors['must_pass_every_subject'] = 'Must pass every subject is required';
            } elseif (!is_bool($data['must_pass_every_subject'])) {
                $errors['must_pass_every_subject'] = 'Must pass every subject must be a boolean';
            }
        }

        return $errors;
    }
}
