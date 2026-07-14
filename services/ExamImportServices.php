<?php

namespace Services;

use App\Models\ImportFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ExamImportServices
{
    private const SAMPLE_ROW_COUNT = 5;
    private const ALLOWED_MIMES = [
        'text/csv',
        'text/plain',
        'application/csv',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    /**
     * System fields available for column mapping.
     * Frontend uses these to let staff map detected columns → system fields.
     */
    public function getSystemFields(): array
    {
        return [z
            ['key' => 'student_name',    'label' => 'Student Name',     'required' => true,  'description' => 'Full name of the student'],
            ['key' => 'student_id',      'label' => 'Student ID',       'required' => true,  'description' => 'Unique student identifier'],
            ['key' => 'subject_name',    'label' => 'Subject Name',     'required' => true,  'description' => 'Name of the exam subject'],
            ['key' => 'score',           'label' => 'Score',            'required' => true,  'description' => 'Raw score achieved'],
            ['key' => 'total_questions', 'label' => 'Total Questions',  'required' => false, 'description' => 'Total number of questions'],
            ['key' => 'correct_count',   'label' => 'Correct Answers',  'required' => false, 'description' => 'Number of correct answers'],
            ['key' => 'wrong_count',     'label' => 'Wrong Answers',    'required' => false, 'description' => 'Number of incorrect answers'],
            ['key' => 'unanswered_count','label' => 'Unanswered',       'required' => false, 'description' => 'Number of unanswered questions'],
            ['key' => 'percentage',      'label' => 'Percentage',       'required' => false, 'description' => 'Score as a percentage'],
            ['key' => 'grade',           'label' => 'Grade',            'required' => false, 'description' => 'Letter grade (A, B, C, etc.)'],
            ['key' => 'exam_date',       'label' => 'Exam Date',        'required' => false, 'description' => 'Date of the exam'],
        ];
    }

    /**
     * Upload and parse a ZipGrade-exported CSV or XLSX file.
     *
     * @param UploadedFile $file       The uploaded file
     * @param int          $campaignId The campaign to associate the import with
     * @return array {
     *     import_id: int,
     *     original_filename: string,
     *     file_type: string,
     *     row_count: int,
     *     detected_columns: array,
     *     sample_rows: array,
     *     system_fields: array,
     * }
     * @throws ValidationException
     */
    public function upload(UploadedFile $file, int $campaignId): array
    {
        // Validate mime type
        $mime = $file->getMimeType();
        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($mime, self::ALLOWED_MIMES, true) && ! in_array($extension, ['csv', 'xlsx'], true)) {
            throw ValidationException::withMessages([
                'file' => 'Unsupported file type. Only CSV and XLSX files are accepted.',
            ]);
        }

        $fileType = in_array($extension, ['xlsx'], true) ? 'xlsx' : 'csv';

        // Parse the file
        $parsed = $this->parseFile($file, $fileType);
        $headers = $parsed['headers'];
        $rows = $parsed['rows'];

        if (empty($headers)) {
            throw ValidationException::withMessages([
                'file' => 'Could not read any columns from the file. The file may be empty or corrupted.',
            ]);
        }

        if (empty($rows)) {
            throw ValidationException::withMessages([
                'file' => 'The file contains headers but no data rows.',
            ]);
        }

        // Build detected columns with sample values
        $detectedColumns = [];
        foreach ($headers as $index => $name) {
            $samples = [];
            foreach (array_slice($rows, 0, self::SAMPLE_ROW_COUNT) as $row) {
                if (isset($row[$index])) {
                    $samples[] = $row[$index];
                }
            }
            $detectedColumns[] = [
                'index'         => $index,
                'name'          => trim($name),
                'sample_values' => $samples,
            ];
        }

        // Build sample rows (first 5 records as associative arrays)
        $sampleRows = [];
        foreach (array_slice($rows, 0, self::SAMPLE_ROW_COUNT) as $row) {
            $assoc = [];
            foreach ($headers as $i => $header) {
                $assoc[trim($header)] = $row[$i] ?? '';
            }
            $sampleRows[] = $assoc;
        }

        // Store file for audit
        $storedPath = $file->store('imports', 'local');
        if ($storedPath === false) {
            throw ValidationException::withMessages([
                'file' => 'Failed to store the uploaded file.',
            ]);
        }

        // Create import file record
        $importFile = ImportFile::create([
            'campaign_id'      => $campaignId,
            'original_filename' => $file->getClientOriginalName(),
            'stored_path'      => $storedPath,
            'file_type'        => $fileType,
            'file_size'        => $file->getSize(),
            'detected_columns' => $detectedColumns,
            'sample_rows'      => $sampleRows,
            'row_count'        => count($rows),
            'status'           => 'pending',
            'imported_by'      => Auth::id(),
        ]);

        return [
            'import_id'        => $importFile->id,
            'original_filename' => $importFile->original_filename,
            'file_type'        => $fileType,
            'row_count'        => $importFile->row_count,
            'detected_columns' => $detectedColumns,
            'sample_rows'      => $sampleRows,
            'system_fields'    => $this->getSystemFields(),
        ];
    }

    /**
     * Validate imported rows against existing data.
     *
     * Reads the previously uploaded file, applies the column mapping,
     * and returns a per-row error/warning report for frontend review.
     *
     * @param int   $importId      The import file record ID
     * @param array $columnMapping Map of system field keys → column indices.
     *                             E.g. {"student_name": 0, "student_id": 1, "subject_name": 2, "score": 3}
     * @return array {
     *     import_id: int,
     *     column_mapping: array,
     *     summary: array,
     *     validation_report: array,
     * }
     */
    public function validate(int $importId, array $columnMapping): array
    {
        $importFile = ImportFile::findOrFail($importId);
        $campaignId = $importFile->campaign_id;

        // Read all rows from the stored file
        $filePath = Storage::disk('local')->path($importFile->stored_path);
        $parsed = $this->parseFileFromPath($filePath, $importFile->file_type);
        $headers = $parsed['headers'];
        $rows = $parsed['rows'];

        // Load campaign subjects for max_score lookups
        $subjects = \App\Models\ExamSubject::where('campaign_id', $campaignId)->get()->keyBy('name');

        // Load candidates for the campaign for student matching
        $candidates = \App\Models\Cadidate::where('campaign_id', $campaignId)->get();

        // Build column mapping lookup: system_field → column_index
        $mapping = $columnMapping;

        // Track duplicates: key = "normalized_name|subject_name" → count
        $seen = [];
        $normalizedName = '';

        $report = [];
        $errorCount = 0;
        $warningCount = 0;
        $errorTypes = [];
        $warningTypes = [];

        foreach ($rows as $rowIndex => $row) {
            $rowData = $this->extractRowData($row, $headers, $mapping);
            $errors = [];
            $warnings = [];

            $studentName = $this->resolveMappingValue('student_name', $row, $headers, $mapping);
            $subjectName = $this->resolveMappingValue('subject_name', $row, $headers, $mapping);
            $scoreRaw = $this->resolveMappingValue('score', $row, $headers, $mapping);

            // --- 1. Subject match validation ---
            $subjectMatch = null;
            if ($subjectName !== null && $subjectName !== '') {
                $subject = $subjects->get($subjectName);
                if ($subject) {
                    $subjectMatch = [
                        'matched'      => true,
                        'subject_id'   => $subject->id,
                        'subject_name' => $subject->name,
                        'max_score'    => (float) $subject->max_score,
                    ];
                } else {
                    $subjectMatch = ['matched' => false, 'subject_name' => $subjectName];
                    $errors[] = $this->makeError(
                        'unmatched_subject',
                        "Subject '{$subjectName}' not found in campaign {$campaignId}."
                    );
                }
            }

            // --- 2. Score validation ---
            if ($scoreRaw !== null && $scoreRaw !== '') {
                if (! is_numeric($scoreRaw)) {
                    $errors[] = $this->makeError(
                        'non_numeric_score',
                        "Score '{$scoreRaw}' is not a valid number."
                    );
                } else {
                    $score = (float) $scoreRaw;
                    if ($score < 0) {
                        $errors[] = $this->makeError(
                            'negative_score',
                            "Score {$score} cannot be negative."
                        );
                    } elseif ($subjectMatch && $subjectMatch['matched'] && $score > $subjectMatch['max_score']) {
                        $errors[] = $this->makeError(
                            'score_exceeds_max',
                            "Score {$score} exceeds max score {$subjectMatch['max_score']} for subject '{$subjectName}'."
                        );
                    }
                }
            }

            // --- 3. Candidate matching ---
            $studentMatch = null;
            if ($studentName !== null && $studentName !== '') {
                $normalizedName = $this->normalizeName($studentName);

                // Try to find a matching candidate by name within this campaign
                $matchedCandidate = $this->matchCandidate($candidates, $studentName);

                if ($matchedCandidate) {
                    $studentMatch = [
                        'matched'       => true,
                        'candidate_id'  => $matchedCandidate->id,
                        'full_name'     => $matchedCandidate->first_name . ' ' . $matchedCandidate->last_name,
                    ];
                } else {
                    $studentMatch = ['matched' => false, 'provided_name' => $studentName];
                    $errors[] = $this->makeError(
                        'unmatched_student',
                        "Student '{$studentName}' does not match any candidate in campaign {$campaignId}."
                    );
                }
            }

            // --- 4. Duplicate detection ---
            $dedupKey = $normalizedName ?? $studentName ?? '';
            if ($subjectName) {
                $dedupKey .= '|' . $subjectName;
            }
            if ($dedupKey !== '') {
                if (isset($seen[$dedupKey])) {
                    $warnings[] = $this->makeWarning(
                        'duplicate_row',
                        "Duplicate entry for '{$studentName}' in subject '{$subjectName}' (also at row {$seen[$dedupKey]})."
                    );
                }
                $seen[$dedupKey] = $rowIndex + 2; // 1-indexed (skip header)
            }

            // --- 5. Build row status ---
            $status = 'valid';
            if (! empty($errors)) {
                $status = 'error';
                $errorCount++;
                foreach ($errors as $e) {
                    $type = $e['type'];
                    $errorTypes[$type] = ($errorTypes[$type] ?? 0) + 1;
                }
            } elseif (! empty($warnings)) {
                $status = 'warning';
                $warningCount++;
                foreach ($warnings as $w) {
                    $type = $w['type'];
                    $warningTypes[$type] = ($warningTypes[$type] ?? 0) + 1;
                }
            }

            $report[] = [
                'row_index'      => $rowIndex + 2, // 1-indexed (skip header row)
                'data'           => $rowData,
                'student_match'  => $studentMatch,
                'subject_match'  => $subjectMatch,
                'status'         => $status,
                'errors'         => $errors,
                'warnings'       => $warnings,
            ];
        }

        return [
            'import_id'        => $importId,
            'column_mapping'   => $mapping,
            'summary'          => [
                'total_rows'     => count($rows),
                'valid_rows'     => count($rows) - $errorCount - $warningCount,
                'rows_with_errors'   => $errorCount,
                'rows_with_warnings' => $warningCount,
                'error_types'    => $errorTypes,
                'warning_types'  => $warningTypes,
            ],
            'validation_report' => $report,
        ];
    }

    /**
     * Extract a readable row data array using the column mapping.
     */
    private function extractRowData(array $row, array $headers, array $mapping): array
    {
        $data = [];
        foreach ($mapping as $field => $colIndex) {
            $data[$field] = $row[$colIndex] ?? '';
        }
        return $data;
    }

    /**
     * Resolve the value for a system field from the row using the mapping.
     */
    private function resolveMappingValue(string $field, array $row, array $headers, array $mapping): ?string
    {
        if (! isset($mapping[$field])) {
            return null;
        }
        $colIndex = $mapping[$field];
        return $row[$colIndex] ?? null;
    }

    /**
     * Try to match a student name against candidates in the campaign.
     *
     * Matching strategy:
     * 1. Exact case-insensitive match on full name (first_name + ' ' + last_name)
     * 2. Fallback to first_name-only match for imports with single-column names
     *
     * Note: Only name-based matching is supported. The candidate schema does not
     * have a student_id field. If DOB-based stricter matching is needed later,
     * add a 'student_dob' system field to getSystemFields() and extend this method.
     */
    private function matchCandidate($candidates, string $studentName): ?\App\Models\Cadidate
    {
        $parts = explode(' ', trim($studentName), 2);
        $firstName = $parts[0] ?? '';
        $inputFullName = strtolower(trim($studentName));

        // Try exact first_name + last_name match (case-insensitive)
        foreach ($candidates as $candidate) {
            $candidateFullName = strtolower(trim($candidate->first_name . ' ' . $candidate->last_name));

            if ($candidateFullName === $inputFullName) {
                return $candidate;
            }
        }

        // Fallback: match by first_name alone (handles imports with only one name column)
        foreach ($candidates as $candidate) {
            if (strtolower(trim($candidate->first_name)) === strtolower(trim($firstName))) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Normalise a name string for duplicate matching.
     */
    private function normalizeName(string $name): string
    {
        return strtolower(preg_replace('/\s+/', ' ', trim($name)));
    }

    /**
     * Create an error entry.
     */
    private function makeError(string $type, string $message): array
    {
        return ['type' => $type, 'message' => $message];
    }

    /**
     * Create a warning entry.
     */
    private function makeWarning(string $type, string $message): array
    {
        return ['type' => $type, 'message' => $message];
    }

    /**
     * Parse a CSV or XLSX file and return headers + data rows.
     */
    private function parseFile(UploadedFile $file, string $fileType): array
    {
        return match ($fileType) {
            'xlsx' => $this->parseXlsx($file),
            default => $this->parseCsv($file),
        };
    }

    /**
     * Parse a file from a stored path (used during validation re-read).
     */
    private function parseFileFromPath(string $filePath, string $fileType): array
    {
        return match ($fileType) {
            'xlsx' => $this->parseXlsxFromPath($filePath),
            default => $this->parseCsvFromPath($filePath),
        };
    }

    /**
     * Parse a CSV file via UploadedFile — delegates to path-based parser.
     */
    private function parseCsv(UploadedFile $file): array
    {
        return $this->parseCsvFromPath($file->getRealPath());
    }

    /**
     * Parse a CSV file from a file path string.
     */
    private function parseCsvFromPath(string $filePath): array
    {
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw ValidationException::withMessages([
                'file' => 'Failed to open CSV file.',
            ]);
        }

        // Detect BOM and remove it
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        // Auto-detect delimiter by sampling first line
        $firstLine = fgets($handle);
        rewind($handle);
        if ($firstLine !== false) {
            $delimiter = $this->detectDelimiter($firstLine);
        } else {
            $delimiter = ',';
        }

        $headers = [];
        $rows = [];
        $lineNumber = 0;

        while (($line = fgetcsv($handle, 0, $delimiter)) !== false) {
            $line = array_map(fn ($cell) => trim($cell), $line);

            // Skip completely empty rows
            if (count(array_filter($line, fn ($v) => $v !== '' && $v !== null)) === 0) {
                continue;
            }

            if ($lineNumber === 0) {
                $headers = $line;
            } else {
                $rows[] = $line;
            }

            $lineNumber++;
        }

        fclose($handle);

        return [
            'headers' => $headers,
            'rows'    => $rows,
        ];
    }

    /**
     * Parse an XLSX file using built-in PHP extensions (ZipArchive + SimpleXML).
     * No external packages required.
     */
    private function parseXlsx(UploadedFile $file): array
    {
        return $this->parseXlsxFromPath($file->getRealPath());
    }

    /**
     * Parse an XLSX file from a file path string.
     */
    private function parseXlsxFromPath(string $filePath): array
    {
        if (! class_exists('ZipArchive')) {
            throw ValidationException::withMessages([
                'file' => 'XLSX parsing requires the PHP Zip extension.',
            ]);
        }

        $zip = new \ZipArchive();
        $openResult = $zip->open($filePath);

        if ($openResult !== true) {
            throw ValidationException::withMessages([
                'file' => 'Failed to open XLSX file (invalid or corrupted).',
            ]);
        }

        // Read shared strings (used by XLSX for text values)
        // Handles both simple (<si><t>value</t></si>) and rich text (<si><r><t>value</t></r></si>) formats
        $sharedStrings = [];
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedStringsXml !== false) {
            $xml = simplexml_load_string($sharedStringsXml);
            if ($xml !== false) {
                foreach ($xml->si as $si) {
                    // Simple format: <si><t>value</t></si>
                    if ((string) $si->t !== '') {
                        $sharedStrings[] = (string) $si->t;
                    } else {
                        // Rich text format: <si><r><t>value</t></r></si>
                        $text = '';
                        foreach ($si->r as $r) {
                            $text .= (string) $r->t;
                        }
                        $sharedStrings[] = $text;
                    }
                }
            }
        }

        // Read the first sheet
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheetXml === false) {
            throw ValidationException::withMessages([
                'file' => 'XLSX file does not contain a sheet (sheet1.xml not found).',
            ]);
        }

        $xml = simplexml_load_string($sheetXml);
        if ($xml === false) {
            throw ValidationException::withMessages([
                'file' => 'Failed to parse XLSX sheet data.',
            ]);
        }

        // Register the default namespace used by SpreadsheetML
        $namespaces = $xml->getNamespaces(true);
        $sheetData = $xml->sheetData;

        if (! $sheetData) {
            return ['headers' => [], 'rows' => []];
        }

        $rows = [];
        $maxColIndex = 0;

        foreach ($sheetData->row as $row) {
            $rowData = [];
            foreach ($row->c as $cell) {
                $attributes = $cell->attributes();
                $colRef = (string) $attributes['r']; // e.g. "A1", "B2"
                $colIndex = $this->xlsxColIndex($colRef);

                $value = '';
                $type = (string) $attributes['t'];

                if ($type === 's' && isset($cell->v)) {
                    // Shared string reference
                    $ssIndex = (int) $cell->v;
                    $value = $sharedStrings[$ssIndex] ?? '';
                } elseif (isset($cell->v)) {
                    $value = (string) $cell->v;
                }

                $rowData[$colIndex] = $value;
            }

            // Fill gaps with empty strings
            if (! empty($rowData)) {
                $maxCol = max(array_keys($rowData));
                $maxColIndex = max($maxColIndex, $maxCol);
                $padded = [];
                for ($i = 0; $i <= $maxCol; $i++) {
                    $padded[$i] = $rowData[$i] ?? '';
                }
                $rows[] = $padded;
            }
        }

        if (empty($rows)) {
            return ['headers' => [], 'rows' => []];
        }

        $headers = $rows[0];
        $dataRows = array_slice($rows, 1);

        return [
            'headers' => $headers,
            'rows'    => $dataRows,
        ];
    }

    /**
     * Convert an XLSX column reference like "A1" or "AB3" to a zero-based index.
     */
    private function xlsxColIndex(string $ref): int
    {
        preg_match('/^([A-Z]+)/', strtoupper($ref), $matches);
        $colLetters = $matches[1] ?? 'A';

        $index = 0;
        $len = strlen($colLetters);
        for ($i = 0; $i < $len; $i++) {
            $index = $index * 26 + (ord($colLetters[$i]) - ord('A') + 1);
        }

        return $index - 1; // zero-based
    }

    /**
     * Auto-detect CSV delimiter by analysing the first line.
     */
    private function detectDelimiter(string $line): string
    {
        $candidates = [',', ';', "\t", '|'];
        $best = ',';
        $bestCount = 0;

        foreach ($candidates as $delim) {
            $count = substr_count($line, $delim);
            if ($count > $bestCount) {
                $bestCount = $count;
                $best = $delim;
            }
        }

        return $best;
    }
}
