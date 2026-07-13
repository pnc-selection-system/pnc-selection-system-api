<?php

namespace Repositories;

use App\Models\ExamThreshold;
use Illuminate\Database\Eloquent\Collection;

class ExamThresholdRepository
{
    /**
     * Get all thresholds for a campaign.
     */
    public function getByCampaign(int $campaignId): Collection
    {
        return ExamThreshold::where('campaign_id', $campaignId)->get();
    }

    /**
     * Upsert a threshold (create or update existing one).
     */
    public function upsert(int $campaignId, ?int $subjectId, float $passScore): ExamThreshold
    {
        return ExamThreshold::updateOrCreate(
            [
                'campaign_id' => $campaignId,
                'subject_id'  => $subjectId,
            ],
            [
                'pass_score' => $passScore,
            ]
        );
    }

    /**
     * Bulk upsert thresholds for a campaign.
     * Returns the collection of affected thresholds.
     */
    public function bulkUpsert(int $campaignId, array $thresholds): Collection
    {
        foreach ($thresholds as $threshold) {
            $this->upsert(
                $campaignId,
                $threshold['subject_id'] ?? null,
                (float) $threshold['pass_score']
            );
        }

        return $this->getByCampaign($campaignId);
    }
}
