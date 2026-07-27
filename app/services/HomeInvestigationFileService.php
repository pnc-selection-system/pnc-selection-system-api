<?php

namespace Services;

use App\Models\HomeInvestigationFile;
use Repositories\HomeInvestigationFileRepository;

class HomeInvestigationFileService
{
    public function __construct(protected HomeInvestigationFileRepository $homeInvestigationFileRepository) {}

    public function uploadFile(int $homeInvestigationId, array $fileData): HomeInvestigationFile
    {
        $fileData['home_investigation_id'] = $homeInvestigationId;

        if (isset($fileData['file'])) {
            $file = $fileData['file'];
            $fileData['file_name'] = $file->getClientOriginalName();
            $fileData['file_path'] = $file->store('home-investigation-files', 'public');
            $fileData['file_size'] = $file->getSize();
            unset($fileData['file']);
        }

        return $this->homeInvestigationFileRepository->create($fileData);
    }

    public function deleteFile(int $homeInvestigationId, int $fileId): void
    {
        $file = $this->homeInvestigationFileRepository->find($fileId);

        if ($file->home_investigation_id !== $homeInvestigationId) {
            abort(404, 'File not found for this home investigation');
        }

        $this->homeInvestigationFileRepository->delete($fileId);
    }
}