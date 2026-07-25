<?php

namespace Services;

use App\Models\Candidate;
use App\Models\ExamOverallResult;
use App\Models\ExamResult;
use App\Models\ExamSubject;
use App\Models\ExamThreshold;
use App\Models\ImportFile;
use App\Models\Rule;
use App\Services\ScoringEngine;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExamResultImportService
{
    /**
     * Known database column names mapped to common CSV header variations for exam results.
     */
    private const HEADER_ALIASES = [
        'student_id'    => ['student id', 'student_id', 'studentid', 'id', 'student number', 'student no'],
        'candidate_id'  => ['candidate id', 'candidate_id', 'candidate'],
        'raw_score'     => ['raw score', 'raw_score', 'score', 'points', 'raw', 'total'],
        'raw_correct'   => ['raw correct', 'raw_correct', 'correct', 'correct answers', 'right', 'correct count'],
        'raw_wrong'     => ['raw wrong', 'raw_wrong', 'wrong', 'incorrect', 'wrong answers', 'wrong count'],
        'raw_unanswered'=> ['unanswered', 'blank', 'unanswered count', 'no answer', 'skipped'],
        'deduction'     => ['deduction', 'deduct', 'penalty', 'minus'],
    ];

    /**
     * Parse an uploaded file and return preview data for exam results.
     *
     * @return array{import_file_id: int, original_filename: string, detected_columns: array, sample_rows: array, row_count: int, auto_mapping: array}
     */
    public function parseUploadedFile(
        \Illuminate\Http\UploadedFile $file,
        int $campaignId,
        int $subjectId,
        int $userId
    ): array {
        $storedPath = $file->store('imports/exam-results');
        $extension = strtolower($file->getClientOriginalExtension());
        $originalName = $file->getClientOriginalName();

        $rows = $this->readFile(Storage::disk('local')->path($storedPath), $extension);
        $rows = array_values($rows);

        if (count($rows) < 1) {
            throw new Exception('The file is empty or has no data rows.');
        }

        $headers = $rows[0];
        $dataRows = array_slice($rows, 1);

        $detectedColumns = [];
        $autoMapping = [];

        foreach ($headers as $index => $header) {
            $cleanHeader = trim((string) $header);
            if (empty($cleanHeader)) {
                continue;
            }
            $detectedColumns[] = $cleanHeader;
            $autoMapping[$index] = $this->autoMapColumn($cleanHeader);
        }

        $sampleRows = array_slice($dataRows, 0, 10);

        $indexToField = [];
        foreach ($detectedColumns as $index => $colName) {
            $indexToField[$index] = $autoMapping[$index] ?? null;
        }

        $readableMapping = [];
        foreach ($autoMapping as $index => $dbField) {
            if ($dbField !== null && isset($detectedColumns[$index])) {
                $readableMapping[$detectedColumns[$index]] = $dbField;
            }
        }

        // Load subject rules for preview/scoring info
        $subject = ExamSubject::with('rules')->find($subjectId);
        $subjectRules = $subject ? $subject->rules->toArray() : [];

        // Build rule summary for frontend display
        $ruleSummary = [];
        foreach ($subjectRules as $rule) {
            $ruleSummary[] = [
                'name'  => $rule['name'],
                'desc'  => $rule['desc'] ?? '',
                'sign'  => $rule['sign'],
                'value' => (float) $rule['value'],
            ];
        }

        // Preview rows with validation
        $previewRows = [];
        $validCount = 0;
        $rowErrors = [];

        foreach ($sampleRows as $rowIndex => $row) {
            $rowData = [];
            $rowValidationErrors = [];

            foreach ($row as $colIndex => $value) {
                $dbField = $indexToField[$colIndex] ?? null;
                if ($dbField !== null) {
                    $rowData[$dbField] = $value;
                }
            }

            $rowNumber = $rowIndex + 2;

            // Validate student_id
            if (! isset($rowData['student_id']) || empty(trim((string) $rowData['student_id']))) {
                $rowValidationErrors[] = 'Student ID is required';
            } else {
                $studentId = trim((string) $rowData['student_id']);
                $candidate = Candidate::where('student_id', $studentId)->first();
                if (! $candidate) {
                    $rowValidationErrors[] = "Student ID '{$studentId}' not found in candidate list";
                } elseif ((int) $candidate->campaign_id !== $campaignId) {
                    $rowValidationErrors[] = "Student ID '{$studentId}' does not belong to this campaign";
                }
            }

            // Either raw_score OR (raw_correct + raw_wrong) must be present
            $hasRawScore = isset($rowData['raw_score']) && is_numeric($rowData['raw_score']);
            $hasCorrect  = isset($rowData['raw_correct']) && is_numeric($rowData['raw_correct']);
            $hasWrong    = isset($rowData['raw_wrong']) && is_numeric($rowData['raw_wrong']);

            if (! $hasRawScore && ! ($hasCorrect)) {
                $rowValidationErrors[] = 'Either Raw Score or Correct Count is required';
            }

            // Calculate preview score if we have enough data
            $previewScore = null;
            if ($hasCorrect) {
                $correctCount = (int) $rowData['raw_correct'];
                $wrongCount = $hasWrong ? (int) $rowData['raw_wrong'] : 0;
                $calcResult = $this->calculateScoreFromRules($correctCount, $wrongCount, $subjectRules);
                $previewScore = $calcResult['final_score'];
            } elseif ($hasRawScore) {
                // Use raw_score as final if deduction is not provided
                $deduction = (isset($rowData['deduction']) && is_numeric($rowData['deduction']))
                    ? (float) $rowData['deduction']
                    : 0;
                $finalSubjectScore = (float) $rowData['raw_score'] - $deduction;
                $previewScore = max(0, $finalSubjectScore);
            }

            $isValid = empty($rowValidationErrors);
            if ($isValid) {
                $validCount++;
            } else {
                $rowErrors[] = "Row {$rowNumber}: " . implode(', ', $rowValidationErrors);
            }

            $previewRows[] = [
                'row'           => $rowNumber,
                'student_id'    => $rowData['student_id'] ?? '',
                'candidate_id'  => $rowData['candidate_id'] ?? '',
                'raw_score'     => $rowData['raw_score'] ?? '',
                'raw_correct'   => $rowData['raw_correct'] ?? null,
                'raw_wrong'     => $rowData['raw_wrong'] ?? null,
                'deduction'     => $rowData['deduction'] ?? null,
                'preview_score' => $previewScore,
                'valid'         => $isValid,
                'errors'        => $rowValidationErrors,
            ];
        }

        // Create import file record
        $importFile = ImportFile::create([
            'campaign_id'       => $campaignId,
            'province_id'       => null,
            'original_filename' => $originalName,
            'stored_path'       => $storedPath,
            'file_type'         => in_array($extension, ['xlsx', 'xls']) ? $extension : 'csv',
            'file_size'         => $file->getSize(),
            'detected_columns'  => $detectedColumns,
            'sample_rows'       => $sampleRows,
            'row_count'         => count($dataRows),
            'status'            => 'pending',
            'imported_by'       => $userId,
        ]);

        return [
            'import_file_id'    => $importFile->id,
            'file_name'         => $originalName,
            'total_rows'        => count($dataRows),
            'valid_rows'        => $validCount,
            'preview'           => $previewRows,
            'errors'            => $rowErrors,
            'detected_columns'  => $detectedColumns,
            'auto_mapping'      => $readableMapping,
            'subject_rules'     => $ruleSummary,
        ];
    }

    /**
     * Validate the full import file with the given column mapping.
     * Returns per-row validation results including score calculation preview.
     *
     * @param int   $importFileId
     * @param array $columnMapping  e.g. [0 => 'student_id', 1 => 'raw_correct', ...]
     * @param int   $campaignId
     * @param int   $subjectId
     *
     * @return array{total_rows: int, valid_rows: int, issues: array, subject_rules: array}
     */
    public function validateImport(
        int $importFileId,
        array $columnMapping,
        int $campaignId,
        int $subjectId
    ): array {
        /** @var ImportFile|null $importFile */
        $importFile = ImportFile::find($importFileId);

        if (! $importFile) {
            throw new Exception('Import file not found.');
        }

        $fullPath = Storage::disk('local')->path($importFile->stored_path);
        if (! file_exists($fullPath)) {
            throw new Exception('The uploaded file no longer exists on the server.');
        }

        $rows = $this->readFile($fullPath, $importFile->file_type);
        $rows = array_values($rows);
        $dataRows = array_slice($rows, 1);

        // Load subject with rules
        $subject = ExamSubject::with('rules')->find($subjectId);
        $subjectRules = $subject ? $subject->rules->toArray() : [];
        $maxScore = $subject ? (float) $subject->max_score : 100;

        $ruleSummary = [];
        foreach ($subjectRules as $rule) {
            $ruleSummary[] = [
                'name'  => $rule['name'],
                'desc'  => $rule['desc'] ?? '',
                'sign'  => $rule['sign'],
                'value' => (float) $rule['value'],
            ];
        }

        $issues = [];
        $validCount = 0;
        $processedCount = 0;

        // Build reverse mapping: dbField => csvColIndex
        $fieldToIndex = [];
        foreach ($columnMapping as $csvColIndex => $dbField) {
            $fieldToIndex[$dbField] = (int) $csvColIndex;
        }

        foreach ($dataRows as $rowIndex => $row) {
            $rowNumber = $rowIndex + 2;
            $rowIssues = [];
            $errors = [];
            $warnings = [];

            // Extract student_id
            $studentId = null;
            if (isset($fieldToIndex['student_id']) && isset($row[$fieldToIndex['student_id']])) {
                $studentId = trim((string) $row[$fieldToIndex['student_id']]);
            }

            if (empty($studentId)) {
                $errors[] = [
                    'row'     => $rowNumber,
                    'column'  => 'Student ID',
                    'message' => 'Student ID is required',
                    'type'    => 'error',
                ];
            } else {
                // Match against candidate list
                $candidate = Candidate::where('student_id', $studentId)->first();
                if (! $candidate) {
                    $errors[] = [
                        'row'     => $rowNumber,
                        'column'  => 'Student ID',
                        'message' => "Student ID '{$studentId}' not found in candidate list",
                        'type'    => 'error',
                    ];
                } elseif ((int) $candidate->campaign_id !== $campaignId) {
                    $errors[] = [
                        'row'     => $rowNumber,
                        'column'  => 'Student ID',
                        'message' => "Student ID '{$studentId}' does not belong to this campaign",
                        'type'    => 'error',
                    ];
                } else {
                    $warnings[] = [
                        'row'     => $rowNumber,
                        'column'  => 'Student ID',
                        'message' => "Student ID '{$studentId}' matched candidate '{$candidate->first_name} {$candidate->last_name}'",
                        'type'    => 'warning',
                    ];
                }
            }

            // Check for raw_score OR raw_correct
            $hasRawScore  = isset($fieldToIndex['raw_score']) && isset($row[$fieldToIndex['raw_score']]) && is_numeric($row[$fieldToIndex['raw_score']]);
            $hasCorrect   = isset($fieldToIndex['raw_correct']) && isset($row[$fieldToIndex['raw_correct']]) && is_numeric($row[$fieldToIndex['raw_correct']]);

            if (! $hasRawScore && ! $hasCorrect) {
                $errors[] = [
                    'row'     => $rowNumber,
                    'column'  => 'Score',
                    'message' => 'Either Raw Score or Correct Count is required',
                    'type'    => 'error',
                ];
            } elseif (empty($errors)) {
                $processedCount++;

                // Calculate score preview
                if ($hasCorrect) {
                    $correctCount = (int) $row[$fieldToIndex['raw_correct']];
                    $wrongCount = (isset($fieldToIndex['raw_wrong']) && isset($row[$fieldToIndex['raw_wrong']]) && is_numeric($row[$fieldToIndex['raw_wrong']]))
                        ? (int) $row[$fieldToIndex['raw_wrong']]
                        : 0;

                    $calcResult = $this->calculateScoreFromRules($correctCount, $wrongCount, $subjectRules);
                    $finalScore = $calcResult['final_score'];

                    $warnings[] = [
                        'row'     => $rowNumber,
                        'column'  => 'Score',
                        'message' => "{$correctCount} correct × rules → score: {$calcResult['raw_score']}, "
                                   . "{$wrongCount} wrong × rules → deduction: -{$calcResult['deduction']}, "
                                   . "final: {$finalScore} / {$maxScore}",
                        'type'    => 'warning',
                    ];
                } else {
                    $rawScoreVal = (float) $row[$fieldToIndex['raw_score']];
                    $deductionVal = (isset($fieldToIndex['deduction']) && isset($row[$fieldToIndex['deduction']]) && is_numeric($row[$fieldToIndex['deduction']]))
                        ? (float) $row[$fieldToIndex['deduction']]
                        : 0;
                    $finalScore = max(0, $rawScoreVal - $deductionVal);

                    $warnings[] = [
                        'row'     => $rowNumber,
                        'column'  => 'Score',
                        'message' => "Raw score: {$rawScoreVal}, deduction: {$deductionVal}, final: {$finalScore} / {$maxScore}",
                        'type'    => 'warning',
                    ];
                }
            }

            $issues = array_merge($issues, $errors, $warnings);

            if (empty($errors)) {
                $validCount++;
            }
        }

        return [
            'total_rows'    => count($dataRows),
            'valid_rows'    => $validCount,
            'issues'        => $issues,
            'subject_rules' => $ruleSummary,
        ];
    }

    /**
     * Confirm and execute the import with user-provided column mapping,
     * calculate scores using subject rules, and store results.
     *
     * @param int   $importFileId
     * @param array $columnMapping  e.g. [0 => 'student_id', 1 => 'raw_correct', ...]
     * @param int   $campaignId
     * @param int   $subjectId
     *
     * @return array{imported_count: int, errors: array}
     */
    public function confirmImport(
        int $importFileId,
        array $columnMapping,
        int $campaignId,
        int $subjectId
    ): array {
        /** @var ImportFile|null $importFile */
        $importFile = ImportFile::find($importFileId);

        if (! $importFile) {
            throw new Exception('Import file not found.');
        }

        if ($importFile->status !== 'pending') {
            throw new Exception('This file has already been imported or has errors.');
        }

        $fullPath = Storage::disk('local')->path($importFile->stored_path);
        if (! file_exists($fullPath)) {
            throw new Exception('The uploaded file no longer exists on the server.');
        }

        $rows = $this->readFile($fullPath, $importFile->file_type);
        $rows = array_values($rows);
        $dataRows = array_slice($rows, 1);

        // Load subject with rules and threshold
        $subject = ExamSubject::with('rules')->find($subjectId);
        if (! $subject) {
            throw new Exception('Subject not found.');
        }
        $subjectRules = $subject->rules->toArray();
        $maxScore = (float) $subject->max_score;

        // Load subject threshold for pass/fail determination
        $threshold = ExamThreshold::where('campaign_id', $campaignId)
            ->where('subject_id', $subjectId)
            ->first();
        $passScore = $threshold ? (float) $threshold->per_subject_min : null;

        // Build reverse mapping: dbField => csvColIndex
        $fieldToIndex = [];
        foreach ($columnMapping as $csvColIndex => $dbField) {
            $fieldToIndex[$dbField] = (int) $csvColIndex;
        }

        $importedCount = 0;
        $errors = [];
        $importedCandidateIds = []; // Track candidates for overall calculation

        DB::beginTransaction();

        try {
            foreach ($dataRows as $rowIndex => $row) {
                $rowNumber = $rowIndex + 2;

                // Extract student_id
                $studentId = null;
                if (isset($fieldToIndex['student_id']) && isset($row[$fieldToIndex['student_id']])) {
                    $studentId = trim((string) $row[$fieldToIndex['student_id']]);
                }

                if (empty($studentId)) {
                    $errors[] = "Row {$rowNumber}: Student ID is required.";
                    continue;
                }

                // Find candidate by student_id
                $candidate = Candidate::where('student_id', $studentId)->first();
                if (! $candidate) {
                    $errors[] = "Row {$rowNumber}: Student ID '{$studentId}' not found in candidate list.";
                    continue;
                }
                if ((int) $candidate->campaign_id !== $campaignId) {
                    $errors[] = "Row {$rowNumber}: Student ID '{$studentId}' does not belong to this campaign.";
                    continue;
                }

                // Extract score data
                $rawCorrect   = (isset($fieldToIndex['raw_correct']) && isset($row[$fieldToIndex['raw_correct']]) && is_numeric($row[$fieldToIndex['raw_correct']]))
                    ? (int) $row[$fieldToIndex['raw_correct']] : null;
                $rawWrong     = (isset($fieldToIndex['raw_wrong']) && isset($row[$fieldToIndex['raw_wrong']]) && is_numeric($row[$fieldToIndex['raw_wrong']]))
                    ? (int) $row[$fieldToIndex['raw_wrong']] : 0;
                $rawUnanswered = (isset($fieldToIndex['raw_unanswered']) && isset($row[$fieldToIndex['raw_unanswered']]) && is_numeric($row[$fieldToIndex['raw_unanswered']]))
                    ? (int) $row[$fieldToIndex['raw_unanswered']] : 0;

                $hasRawScoreDirect = isset($fieldToIndex['raw_score']) && isset($row[$fieldToIndex['raw_score']]) && is_numeric($row[$fieldToIndex['raw_score']]);

                if ($rawCorrect !== null) {
                    // Calculate using subject rules
                    $calcResult = $this->calculateScoreFromRules($rawCorrect, $rawWrong, $subjectRules, $rawUnanswered, $maxScore);

                    $finalRawScore  = $calcResult['raw_score'];
                    $deduction      = $calcResult['deduction'];
                    $finalScore     = $calcResult['final_score'];

                    // Clamp to subject's max_score
                    $finalScore = min($finalScore, $maxScore);

                } elseif ($hasRawScoreDirect) {
                    // Use provided raw_score and deduction
                    $finalRawScore  = (float) $row[$fieldToIndex['raw_score']];
                    $deduction      = (isset($fieldToIndex['deduction']) && isset($row[$fieldToIndex['deduction']]) && is_numeric($row[$fieldToIndex['deduction']]))
                        ? (float) $row[$fieldToIndex['deduction']] : 0;
                    $finalScore     = max(0, min($finalRawScore - $deduction, $maxScore));
                    $rawCorrect     = $rawCorrect ?? 0;

                } else {
                    $errors[] = "Row {$rowNumber}: Either Raw Score or Correct Count is required.";
                    continue;
                }

                // Determine pass/fail for this subject
                $passed = ScoringEngine::determinePassFail($finalScore, $passScore);

                // Prepare result data
                $resultData = [
                    'candidate_id' => $candidate->id,
                    'subject_id'   => $subjectId,
                    'campaign_id'  => $campaignId,
                    'raw_correct'  => $rawCorrect ?? 0,
                    'raw_wrong'    => $rawWrong,
                    'raw_score'    => round($finalRawScore, 2),
                    'deduction'    => round($deduction, 2),
                    'final_score'  => round($finalScore, 2),
                    'passed'       => $passed ?? false,
                    'status'       => 'draft',
                    'version'      => 1,
                ];

                try {
                    // Upsert: update existing or create new
                    ExamResult::updateOrCreate(
                        [
                            'candidate_id' => $candidate->id,
                            'subject_id'   => $subjectId,
                            'campaign_id'  => $campaignId,
                        ],
                        $resultData
                    );
                    $importedCount++;
                    $importedCandidateIds[$candidate->id] = true;
                } catch (Exception $e) {
                    $errors[] = "Row {$rowNumber}: {$e->getMessage()}";
                }
            }

            // Calculate overall results for all imported candidates
            if (! empty($importedCandidateIds)) {
                $this->calculateOverallResults($campaignId, array_keys($importedCandidateIds));
            }

            // Update import file status
            $importFile->update([
                'status'        => count($errors) > 0 ? 'error' : 'imported',
                'error_message' => count($errors) > 0 ? implode('; ', array_slice($errors, 0, 50)) : null,
                'row_count'     => $importedCount,
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $importFile->update([
                'status'        => 'error',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }

        return [
            'imported' => $importedCount,
            'skipped'  => count($errors),
            'errors'   => $errors,
        ];
    }

    /**
     * Calculate score from correct/wrong/unanswered counts using subject rules.
     *
     * Rules with sign '+' contribute points per correct answer.
     * Rules with sign '-' deduct points per wrong answer.
     *
     * @param int   $correctCount
     * @param int   $wrongCount
     * @param array $rules  Array of rule arrays with 'sign' and 'value' keys
     * @param int   $unansweredCount
     *
     * @return array{raw_score: float, deduction: float, final_score: float}
     */
    private function calculateScoreFromRules(
        int $correctCount,
        int $wrongCount,
        array $rules,
        int $unansweredCount = 0,
        float $maxScore = 999999
    ): array {
        $pointsPerCorrect   = 0.0;
        $deductionPerWrong  = 0.0;
        $deductionPerUnanswered = 0.0;
        $negativeMarking    = true;
        $partialCredit      = 1.0;

        foreach ($rules as $rule) {
            $sign  = $rule['sign'] ?? '+';
            $value = (float) ($rule['value'] ?? 0);
            $name  = strtolower($rule['name'] ?? '');
            $desc  = strtolower($rule['desc'] ?? '');

            if ($sign === '+') {
                $pointsPerCorrect += $value;
            } elseif ($sign === '-') {
                // Check if this is for wrong answers or unanswered
                if (str_contains($name, 'unanswered') || str_contains($desc, 'unanswered') ||
                    str_contains($name, 'blank') || str_contains($desc, 'blank') ||
                    str_contains($name, 'skipped') || str_contains($desc, 'skipped')) {
                    $deductionPerUnanswered += $value;
                } else {
                    $deductionPerWrong += $value;
                }
            }

            // Check for special rule names
            if (str_contains($name, 'negative') || str_contains($desc, 'negative')) {
                $negativeMarking = $value > 0;
            }
            if (str_contains($name, 'partial') || str_contains($desc, 'partial')) {
                $partialCredit = $value;
            }
        }

        // Build deductionRules array for ScoringEngine
        $deductionRules = [
            'wrong_answer'     => -$deductionPerWrong,
            'unanswered'       => -$deductionPerUnanswered,
            'negative_marking' => $negativeMarking,
            'partial_credit'   => $partialCredit,
        ];

        // Calculate raw score: correct count * points per correct
        $rawScore = $correctCount * $pointsPerCorrect;

        // Use the existing ScoringEngine for the calculation
        $result = ScoringEngine::calculateSubjectScore(
            rawScore:        $rawScore,
            correctCount:    $correctCount,
            wrongCount:      $wrongCount,
            unansweredCount: $unansweredCount,
            maxScore:        $maxScore,
            deductionRules:  $deductionRules
        );

        return [
            'raw_score'   => $result['raw_score'],
            'deduction'   => abs($result['deduction']),
            'final_score' => $result['final_score'],
        ];
    }

    /**
     * Calculate and store overall weighted results for imported candidates.
     * This makes results immediately available in the Results & Analytics page.
     */
    private function calculateOverallResults(int $campaignId, array $candidateIds): void
    {
        $subjects = ExamSubject::where('campaign_id', $campaignId)->get();
        $totalWeight = $subjects->sum('weight');

        // Get overall threshold
        $overallThreshold = ExamThreshold::where('campaign_id', $campaignId)
            ->whereNull('subject_id')
            ->first();
        $overallPassScore = $overallThreshold ? (float) $overallThreshold->overall_pass_mark : null;

        foreach ($candidateIds as $candidateId) {
            $results = ExamResult::where('campaign_id', $campaignId)
                ->where('candidate_id', $candidateId)
                ->get()
                ->keyBy('subject_id');

            if ($results->isEmpty()) {
                continue;
            }

            $totalWeighted = 0.0;

            foreach ($subjects as $subject) {
                $result = $results->get($subject->id);
                if ($result && $subject->max_score > 0) {
                    $pct = ($result->final_score / $subject->max_score) * 100;
                    $totalWeighted += ($pct * $subject->weight) / 100;
                }
            }

            $overallPct = $totalWeight > 0 ? round(($totalWeighted / $totalWeight) * 100, 2) : 0.0;
            $overallPassed = ScoringEngine::determinePassFail($overallPct, $overallPassScore);

            ExamOverallResult::updateOrCreate(
                [
                    'candidate_id' => $candidateId,
                    'campaign_id'  => $campaignId,
                ],
                [
                    'total_weighted_score' => round($totalWeighted, 2),
                    'overall_percentage'   => $overallPct,
                    'passed'               => $overallPassed ?? false,
                    'status'               => 'draft',
                    'version'              => 1,
                ]
            );
        }
    }

    /**
     * Read a CSV or Excel file and return all rows as arrays.
     */
    private function readFile(string $filePath, string $extension): array
    {
        if (! file_exists($filePath)) {
            throw new Exception("File not found: {$filePath}");
        }

        if (in_array($extension, ['csv', 'txt'])) {
            return $this->readCsv($filePath);
        }

        return $this->readExcel($filePath);
    }

    /**
     * Parse CSV file.
     */
    private function readCsv(string $filePath): array
    {
        $rows = [];

        if (($handle = fopen($filePath, 'r')) === false) {
            throw new Exception("Cannot open file: {$filePath}");
        }

        $firstLine = fgets($handle);
        rewind($handle);
        $delimiter = $this->detectDelimiter($firstLine);

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (count($row) === 1 && (empty($row[0]) || trim($row[0]) === '')) {
                continue;
            }
            $rows[] = $row;
        }

        fclose($handle);

        return $rows;
    }

    /**
     * Parse Excel file (.xlsx, .xls) using PhpSpreadsheet.
     */
    private function readExcel(string $filePath): array
    {
        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet   = $spreadsheet->getActiveSheet();
            $data        = $worksheet->toArray(null, true, true, false);

            while (! empty($data) && $this->isRowEmpty(end($data))) {
                array_pop($data);
            }
            while (! empty($data) && $this->isRowEmpty($data[0])) {
                array_shift($data);
            }

            $spreadsheet->disconnectWorksheets();

            return $data;
        } catch (Exception $e) {
            Log::error('Excel parsing failed: ' . $e->getMessage());
            throw new Exception('Failed to parse Excel file. Ensure it is a valid .xlsx or .xls file.');
        }
    }

    /**
     * Auto-detect CSV delimiter.
     */
    private function detectDelimiter(string $line): string
    {
        $delimiters = [',', ';', "\t", '|'];
        $bestDelimiter = ',';
        $bestCount = 0;

        foreach ($delimiters as $delimiter) {
            $count = count(explode($delimiter, $line));
            if ($count > $bestCount) {
                $bestCount = $count;
                $bestDelimiter = $delimiter;
            }
        }

        return $bestDelimiter;
    }

    /**
     * Check if a row from Excel is empty.
     */
    private function isRowEmpty(array $row): bool
    {
        foreach ($row as $cell) {
            if ($cell !== null && trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * Auto-map a column header to a database field using fuzzy matching.
     */
    private function autoMapColumn(string $header): ?string
    {
        $clean = strtolower(trim($header));
        $clean = preg_replace('/[^a-z0-9\s]/', '', $clean);
        $clean = preg_replace('/\s+/', ' ', $clean);

        $bestMatch  = null;
        $bestLength = 0;

        foreach (self::HEADER_ALIASES as $dbField => $aliases) {
            foreach ($aliases as $alias) {
                $aliasClean = preg_replace('/[^a-z0-9\s]/', '', strtolower($alias));
                $aliasClean = preg_replace('/\s+/', ' ', $aliasClean);

                if ($clean === $aliasClean) {
                    return $dbField;
                }

                if (str_contains($clean, $aliasClean) || str_contains($aliasClean, $clean)) {
                    $aliasLen = strlen($aliasClean);
                    if ($aliasLen > $bestLength) {
                        $bestLength = $aliasLen;
                        $bestMatch  = $dbField;
                    }
                }
            }
        }

        return $bestMatch;
    }
}
