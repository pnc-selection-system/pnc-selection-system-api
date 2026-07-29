<?php

namespace Services;

use App\Enums\ReportType;
use App\Enums\ReportStatus;
use App\Models\ReportExport;
use App\Models\SelectCampaing;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Repositories\ReportExportRepository;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReportExportServices
{
    public function __construct(protected ReportExportRepository $reportExportRepository)
    {
    }

    public function list(array $filters = [])
    {
        return $this->reportExportRepository->list($filters);
    }

    public function create(array $data, int $userId): ReportExport
    {
        try {
            $report = $this->reportExportRepository->create([
                'user_id'     => $userId,
                'report_name' => $data['report_name'] ?? ReportType::from($data['type'])->label(),
                'type'        => $data['type'],
                'export_type' => $data['export_type'] ?? 'Excel',
                'campaign_id' => $data['campaign_id'],
                'status'      => ReportStatus::Processing,
                'progress'    => 0,
            ]);

            try {
                $this->generateReportFile($report);
            } catch (\Throwable $e) {
                Log::error('Report file generation failed: ' . $e->getMessage(), [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);

                $report = $this->reportExportRepository->update($report, [
                    'status'   => ReportStatus::Failed,
                    'progress' => null,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Report creation failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            throw new \RuntimeException(
                'Failed to create report: ' . $e->getMessage(),
                0,
                $e
            );
        }

        return $this->reportExportRepository->find($report);
    }

    public function find(ReportExport $reportExport): ReportExport
    {
        return $this->reportExportRepository->find($reportExport);
    }

    public function update(ReportExport $reportExport, array $data): ReportExport
    {
        return $this->reportExportRepository->update($reportExport, $data);
    }

    public function delete(ReportExport $reportExport): void
    {
        if ($reportExport->file_path && Storage::disk('public')->exists($reportExport->file_path)) {
            Storage::disk('public')->delete($reportExport->file_path);
        }

        $this->reportExportRepository->delete($reportExport);
    }

    /**
     * Generate the report file (XLSX) with data based on the report type.
     */
    protected function generateReportFile(ReportExport $report): void
    {
        $reportType = $report->type instanceof ReportType
            ? $report->type
            : ReportType::from($report->type);

        $campaign = SelectCampaing::find($report->campaign_id);

        // ── Build spreadsheet with data ──────────────────────────────
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $spreadsheet->getProperties()
            ->setCreator('PNC Selection System')
            ->setTitle($report->report_name)
            ->setSubject($report->report_name);

        // Build the report content based on type, wrapped in try-catch
        // so a query failure doesn't crash the whole report
        try {
            match ($reportType) {
                ReportType::FinalSelectedList    => $this->buildFinalSelectedList($sheet, $campaign),
                ReportType::ExamResults          => $this->buildExamResults($sheet, $campaign),
                ReportType::InvestigationSummary  => $this->buildInvestigationSummary($sheet, $campaign),
                ReportType::VotingRecord         => $this->buildVotingRecord($sheet, $campaign),
            };
        } catch (\Throwable $e) {
            Log::error('Report data fetch failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            // Write error info to the sheet so user sees something
            $sheet->setCellValue('A1', 'Report: ' . $report->report_name);
            $sheet->setCellValue('A3', 'Error fetching data: ' . $e->getMessage());
        }

        $this->reportExportRepository->update($report, ['progress' => 80]);

        // ── Save as XLSX ─────────────────────────────────────────────
        $fileName = sprintf(
            '%s_%s_%s.xlsx',
            str_replace(' ', '_', $report->report_name),
            $campaign?->name ?? 'campaign',
            now()->format('Ymd_His')
        );
        $fileName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $fileName);

        $directory = 'reports/' . $report->id;
        Storage::disk('public')->makeDirectory($directory);
        $filePath = $directory . '/' . $fileName;

        $writer = new Xlsx($spreadsheet);
        $tempPath = tempnam(sys_get_temp_dir(), 'report_');
        $writer->save($tempPath);

        Storage::disk('public')->put($filePath, file_get_contents($tempPath));
        unlink($tempPath);
        $spreadsheet->disconnectWorksheets();

        // ── Update report record ─────────────────────────────────────
        $this->reportExportRepository->update($report, [
            'status'       => ReportStatus::Ready,
            'progress'     => 100,
            'file_path'    => $filePath,
            'completed_at' => now(),
        ]);
    }

    /**
     * Style header row.
     */
    protected function styleHeaderRow(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, int $colCount): void
    {
        $headerRange = 'A1:' . chr(64 + $colCount) . '1';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F2937']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF374151']]],
        ]);
        foreach (range('A', chr(64 + $colCount)) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    /**
     * Style data rows with alternating colors.
     */
    protected function styleDataRows(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, int $rowCount, int $colCount): void
    {
        if ($rowCount < 2) return;
        $dataRange = 'A2:' . chr(64 + $colCount) . $rowCount;
        $sheet->getStyle($dataRange)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE5E7EB']]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        for ($i = 2; $i <= $rowCount; $i++) {
            if ($i % 2 === 0) {
                $sheet->getStyle('A' . $i . ':' . chr(64 + $colCount) . $i)
                    ->getFill()->setFillType(Fill::FILL_SOLID)->setStartColor(['argb' => 'FFF9FAFB']);
            }
        }
    }

    // ─── Report Builders ─────────────────────────────────────────────────

    protected function buildFinalSelectedList(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, ?SelectCampaing $campaign): void
    {
        $headers = ['#', 'Student ID', 'Full Name', 'Gender', 'Province', 'School', 'Status'];
        foreach ($headers as $i => $header) {
            $sheet->setCellValue(chr(65 + $i) . '1', $header);
        }
        $this->styleHeaderRow($sheet, count($headers));

        try {
            $data = DB::table('candidates')
                ->leftJoin('provinces', 'candidates.province_id', '=', 'provinces.id')
                ->where('candidates.campaign_id', $campaign?->id)
                ->where('candidates.status', 'Selected')
                ->select('candidates.student_id', DB::raw("CONCAT(candidates.first_name, ' ', candidates.last_name) as full_name"), 'candidates.gender', 'provinces.name as province', 'candidates.school_name', 'candidates.status')
                ->orderBy('candidates.first_name')
                ->get();

            $row = 2;
            foreach ($data as $idx => $c) {
                $sheet->setCellValue('A' . $row, $idx + 1);
                $sheet->setCellValue('B' . $row, $c->student_id ?? '');
                $sheet->setCellValue('C' . $row, $c->full_name ?? '');
                $sheet->setCellValue('D' . $row, $c->gender ?? '');
                $sheet->setCellValue('E' . $row, $c->province ?? '');
                $sheet->setCellValue('F' . $row, $c->school_name ?? '');
                $sheet->setCellValue('G' . $row, $c->status ?? 'Selected');
                $row++;
            }
            if ($row > 2) $this->styleDataRows($sheet, $row - 1, count($headers));
        } catch (\Throwable $e) {
            $sheet->setCellValue('A2', 'No data available');
        }
    }

    protected function buildExamResults(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, ?SelectCampaing $campaign): void
    {
        $headers = ['#', 'Student ID', 'Full Name', 'Province', 'Subject', 'Final Score', 'Passed'];
        foreach ($headers as $i => $header) {
            $sheet->setCellValue(chr(65 + $i) . '1', $header);
        }
        $this->styleHeaderRow($sheet, count($headers));

        try {
            $data = DB::table('exam_results')
                ->join('candidates', 'exam_results.candidate_id', '=', 'candidates.id')
                ->join('exam_subjects', 'exam_results.subject_id', '=', 'exam_subjects.id')
                ->leftJoin('provinces', 'candidates.province_id', '=', 'provinces.id')
                ->where('candidates.campaign_id', $campaign?->id)
                ->select('candidates.student_id', DB::raw("CONCAT(candidates.first_name, ' ', candidates.last_name) as full_name"), 'provinces.name as province', 'exam_subjects.name as subject', 'exam_results.final_score', 'exam_results.passed')
                ->orderBy('candidates.first_name')
                ->get();

            $row = 2;
            foreach ($data as $idx => $r) {
                $sheet->setCellValue('A' . $row, $idx + 1);
                $sheet->setCellValue('B' . $row, $r->student_id ?? '');
                $sheet->setCellValue('C' . $row, $r->full_name ?? '');
                $sheet->setCellValue('D' . $row, $r->province ?? '');
                $sheet->setCellValue('E' . $row, $r->subject ?? '');
                $sheet->setCellValue('F' . $row, $r->final_score ?? 0);
                $sheet->setCellValue('G' . $row, $r->passed ? 'Yes' : 'No');
                $row++;
            }
            if ($row > 2) $this->styleDataRows($sheet, $row - 1, count($headers));
        } catch (\Throwable $e) {
            $sheet->setCellValue('A2', 'No data available');
        }
    }

    protected function buildInvestigationSummary(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, ?SelectCampaing $campaign): void
    {
        $headers = ['#', 'Student ID', 'Full Name', 'Province', 'Investigator', 'Status', 'Recommendation', 'Completed Date'];
        foreach ($headers as $i => $header) {
            $sheet->setCellValue(chr(65 + $i) . '1', $header);
        }
        $this->styleHeaderRow($sheet, count($headers));

        try {
            $data = DB::table('home_investigations')
                ->join('candidates', 'home_investigations.candidate_id', '=', 'candidates.id')
                ->leftJoin('provinces', 'candidates.province_id', '=', 'provinces.id')
                ->leftJoin('users', 'home_investigations.investigator_id', '=', 'users.id')
                ->where('candidates.campaign_id', $campaign?->id)
                ->select('candidates.student_id', DB::raw("CONCAT(candidates.first_name, ' ', candidates.last_name) as full_name"), 'provinces.name as province', 'users.name as investigator', 'home_investigations.status', 'home_investigations.recommendation', 'home_investigations.completed_at')
                ->get();

            $row = 2;
            foreach ($data as $idx => $inv) {
                $sheet->setCellValue('A' . $row, $idx + 1);
                $sheet->setCellValue('B' . $row, $inv->student_id ?? '');
                $sheet->setCellValue('C' . $row, $inv->full_name ?? '');
                $sheet->setCellValue('D' . $row, $inv->province ?? '');
                $sheet->setCellValue('E' . $row, $inv->investigator ?? '');
                $sheet->setCellValue('F' . $row, $inv->status ?? '');
                $sheet->setCellValue('G' . $row, $inv->recommendation ?? '');
                $sheet->setCellValue('H' . $row, isset($inv->completed_at) ? date('Y-m-d', strtotime($inv->completed_at)) : '');
                $row++;
            }
            if ($row > 2) $this->styleDataRows($sheet, $row - 1, count($headers));
        } catch (\Throwable $e) {
            $sheet->setCellValue('A2', 'No data available');
        }
    }

    protected function buildVotingRecord(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, ?SelectCampaing $campaign): void
    {
        $headers = ['#', 'Student ID', 'Full Name', 'Province', 'Round', 'Votes For', 'Votes Against', 'Status'];
        foreach ($headers as $i => $header) {
            $sheet->setCellValue(chr(65 + $i) . '1', $header);
        }
        $this->styleHeaderRow($sheet, count($headers));

        try {
            $data = DB::table('voting_round_candidates')
                ->join('candidates', 'voting_round_candidates.candidate_id', '=', 'candidates.id')
                ->join('voting_rounds', 'voting_round_candidates.voting_round_id', '=', 'voting_rounds.id')
                ->leftJoin('provinces', 'candidates.province_id', '=', 'provinces.id')
                ->where('candidates.campaign_id', $campaign?->id)
                ->select('candidates.student_id', DB::raw("CONCAT(candidates.first_name, ' ', candidates.last_name) as full_name"), 'provinces.name as province', 'voting_rounds.name as round', 'voting_round_candidates.votes_for', 'voting_round_candidates.votes_against', 'voting_round_candidates.status')
                ->orderBy('voting_rounds.name')
                ->get();

            $row = 2;
            foreach ($data as $idx => $rec) {
                $sheet->setCellValue('A' . $row, $idx + 1);
                $sheet->setCellValue('B' . $row, $rec->student_id ?? '');
                $sheet->setCellValue('C' . $row, $rec->full_name ?? '');
                $sheet->setCellValue('D' . $row, $rec->province ?? '');
                $sheet->setCellValue('E' . $row, $rec->round ?? '');
                $sheet->setCellValue('F' . $row, $rec->votes_for ?? 0);
                $sheet->setCellValue('G' . $row, $rec->votes_against ?? 0);
                $sheet->setCellValue('H' . $row, $rec->status ?? '');
                $row++;
            }
            if ($row > 2) $this->styleDataRows($sheet, $row - 1, count($headers));
        } catch (\Throwable $e) {
            $sheet->setCellValue('A2', 'No data available');
        }
    }
}
