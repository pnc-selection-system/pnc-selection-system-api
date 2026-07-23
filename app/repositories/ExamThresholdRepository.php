<?php

namespace Repositories;

use App\Models\ExamThreshold;

class ExamThresholdRepository
{
    /**
     * Get overall threshold for a campaign
     */
    public function getOverallThreshold(int $campaignId): ?ExamThreshold
    {
        return ExamThreshold::forCampaign($campaignId)
            ->overall()
            ->first();
    }

    /**
     * Get threshold for a specific subject in a campaign
     */
    public function getSubjectThreshold(int $campaignId, int $subjectId): ?ExamThreshold
    {
        return ExamThreshold::forCampaign($campaignId)
            ->forSubject($subjectId)
            ->first();
    }

    /**
     * Get all thresholds for a campaign
     */
    public function getCampaignThresholds(int $campaignId): array
    {
        $thresholds = ExamThreshold::forCampaign($campaignId)
            ->with(['subject:id,name,max_score,weight'])
            ->get();

        $overall = $thresholds->whereNull('subject_id')->first();
        $subjectThresholds = $thresholds->whereNotNull('subject_id')->values();

        return [
            'overall' => $overall,
            'subjects' => $subjectThresholds,
        ];
    }

    /**
     * Create or update overall threshold
     */
    public function upsertOverallThreshold(int $campaignId, array $data): ExamThreshold
    {
        $threshold = $this->getOverallThreshold($campaignId);

        if ($threshold) {
            $threshold->update($data);
            $threshold->refresh();
        } else {
            $data['campaign_id'] = $campaignId;
            $data['subject_id'] = null;
            $threshold = ExamThreshold::create($data);
        }

        return $threshold;
    }

    /**
     * Create or update subject threshold
     */
    public function upsertSubjectThreshold(int $campaignId, int $subjectId, array $data): ExamThreshold
    {
        $threshold = $this->getSubjectThreshold($campaignId, $subjectId);

        if ($threshold) {
            $threshold->update($data);
            $threshold->refresh();
        } else {
            $data['campaign_id'] = $campaignId;
            $data['subject_id'] = $subjectId;
            $threshold = ExamThreshold::create($data);
        }

        return $threshold;
    }

    /**
     * Delete threshold
     */
    public function delete(int $id): bool
    {
        $threshold = ExamThreshold::find($id);
        if (!$threshold) {
            return false;
        }

        return $threshold->delete();
    }
}
