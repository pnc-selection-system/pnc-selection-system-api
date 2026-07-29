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

        // Validate overall_pass_mark
        if (!isset($data['overall_pass_mark']) || $data['overall_pass_mark'] === null) {
            $errors['overall_pass_mark'] = 'Overall pass mark is required';
        } elseif (!is_numeric($data['overall_pass_mark'])) {
            $errors['overall_pass_mark'] = 'Overall pass mark must be a number';
        } elseif ($data['overall_pass_mark'] < 0 || $data['overall_pass_mark'] > 100) {
            $errors['overall_pass_mark'] = 'Overall pass mark must be between 0 and 100';
        }

        // Validate per_subject_min
        if (!isset($data['per_subject_min']) || $data['per_subject_min'] === null) {
            $errors['per_subject_min'] = 'Per subject minimum is required';
        } elseif (!is_numeric($data['per_subject_min'])) {
            $errors['per_subject_min'] = 'Per subject minimum must be a number';
        } elseif ($data['per_subject_min'] < 0 || $data['per_subject_min'] > 100) {
            $errors['per_subject_min'] = 'Per subject minimum must be between 0 and 100';
        }

        // Validate that per_subject_min is not greater than overall_pass_mark
        if (isset($data['overall_pass_mark']) && isset($data['per_subject_min'])) {
            if ($data['per_subject_min'] > $data['overall_pass_mark']) {
                $errors['per_subject_min'] = 'Per subject minimum cannot be greater than overall pass mark';
            }
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
