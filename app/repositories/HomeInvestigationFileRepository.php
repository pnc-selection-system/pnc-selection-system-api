<?php

namespace Repositories;

use App\Models\HomeInvestigationFile;
use Illuminate\Database\Eloquent\Collection;


class HomeInvestigationFileRepository
{
    public function list(int $homeInvestigationId): Collection
    {
        return HomeInvestigationFile::query()
            ->where('home_investigation_id', $homeInvestigationId)
            ->get();
    }

    public function create(array $data): HomeInvestigationFile
    {
        return HomeInvestigationFile::create($data);
    }

    public function find(int $id): HomeInvestigationFile
    {
        return HomeInvestigationFile::findOrFail($id);
    }

    public function delete(int $id): void
    {
        $file = $this->find($id);
        $file->delete($id);
    }
}