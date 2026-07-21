<?php

namespace Services;

use App\Enums\CampaignStatus;
use App\Helpers\ApiResponse;
use App\Models\ExamSubject;
use App\Models\SelectCampaing;
use Illuminate\Http\Exceptions\HttpResponseException;
use Repositories\ExamSubjectRepository;

class ExamSubjectServices
{
    public function __construct(protected ExamSubjectRepository $examSubjectRepository)
    {
    }

    public function list(array $filters = [])
    {
        return $this->examSubjectRepository->list($filters);
    }

    public function create(array $data): ExamSubject
    {
        $this->ensureCampaignIsActive((int) $data['campaign_id']);

        return $this->examSubjectRepository->create($data);
    }

    public function find(ExamSubject $examSubject): ExamSubject
    {
        return $this->examSubjectRepository->find($examSubject);
    }

    public function update(ExamSubject $examSubject, array $data): ExamSubject
    {
        // If campaign_id is being changed, validate the new campaign
        $campaignId = (int) ($data['campaign_id'] ?? $examSubject->campaign_id);
        $this->ensureCampaignIsActive($campaignId);

        return $this->examSubjectRepository->update($examSubject, $data);
    }

    public function delete(ExamSubject $examSubject): void
    {
        $this->ensureCampaignIsActive((int) $examSubject->campaign_id);

        $this->examSubjectRepository->delete($examSubject);
    }

    /**
     * Ensure the campaign exists and has status 'Active'.
     *
     * @throws HttpResponseException
     */
    private function ensureCampaignIsActive(int $campaignId): void
    {
        $campaign = SelectCampaing::find($campaignId);

        if (! $campaign) {
            throw new HttpResponseException(
                ApiResponse::error('Campaign not found', 404)
            );
        }

        if ($campaign->status !== CampaignStatus::Active) {
            throw new HttpResponseException(
                ApiResponse::error(
                    'Cannot modify subjects for a campaign that is not active. Current status: ' . $campaign->status->value,
                    422
                )
            );
        }
    }
}
