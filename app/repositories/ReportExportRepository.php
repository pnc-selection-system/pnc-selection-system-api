<?php

namespace Repositories;

use App\Models\ReportExport;

class ReportExportRepository
{
    public function list(array $filters = [])
    {
        return ReportExport::select(
                'id', 'user_id', 'report_name', 'type', 'export_type',
                'campaign_id', 'status', 'progress', 'file_path',
                'created_at', 'completed_at', 'updated_at'
            )
            ->with('campaign:id,name,year')
            ->when(
                !empty($filters['status']),
                fn($q) => $q->where('status', $filters['status'])
            )
            ->when(
                !empty($filters['campaign_id']),
                fn($q) => $q->where('campaign_id', (int) $filters['campaign_id'])
            )
            ->when(
                !empty($filters['user_id']),
                fn($q) => $q->where('user_id', (int) $filters['user_id'])
            )
            ->latest('id')
            ->paginate($filters['per_page'] ?? 10);
    }

    public function create(array $data): ReportExport
    {
        return ReportExport::create($data);
    }

    public function find(ReportExport $reportExport): ReportExport
    {
        $reportExport->load('campaign:id,name,year');

        return $reportExport;
    }

    public function update(ReportExport $reportExport, array $data): ReportExport
    {
        $reportExport->update($data);
        $reportExport->refresh();
        $reportExport->load('campaign:id,name,year');

        return $reportExport;
    }

    public function delete(ReportExport $reportExport): void
    {
        $reportExport->delete();
    }
}
