<?php

namespace Services;

use App\Models\Cadidate;
use App\Models\ImportFile;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ImportCandidateServices
{
    /**
     * Known database column names mapped to common CSV header variations.
     */
    private const HEADER_ALIASES = [
        'student_id'    => ['student id', 'student_id', 'studentid', 'id', 'student number', 'student no'],
        'first_name'    => ['first name', 'firstname', 'first_name', 'given name', 'givenname', 'fname', 'name'],
        'last_name'     => ['last name', 'lastname', 'last_name', 'surname', 'family name', 'familyname', 'lname'],
        'first_name_kh' => ['first name kh', 'firstname kh', 'first_name_kh', 'khmer first name', 'khmer given name'],
        'last_name_kh'  => ['last name kh', 'lastname kh', 'last_name_kh', 'khmer last name', 'khmer surname'],
        'gender'        => ['gender', 'sex', 'gender of student'],
        'dob'           => ['dob', 'date of birth', 'birthdate', 'birth date', 'birthday', 'date_of_birth'],
        'phone'         => ['phone', 'telephone', 'tel', 'mobile', 'phone number', 'contact number', 'cellphone'],
        'school_name'   => ['school', 'school name', 'school_name', 'schoolname', 'institution', 'high school'],
        'ngo_id'        => ['ngo', 'ngo id', 'ngo_id', 'ngo name', 'referring ngo', 'partner'],
        'status'        => ['status', 'candidate status', 'registration status'],
    ];

    /**
     * Parse an uploaded file and return preview data.
     *
     * @return array{import_file_id: int, original_filename: string, detected_columns: array, sample_rows: array, row_count: int, auto_mapping: array}
     */
    public function parseUploadedFile(
        \Illuminate\Http\UploadedFile $file,
        int $campaignId,
        int $provinceId,
        int $userId,
        ?int $ngoId = null
    ): array {
        // Store the file
        $storedPath = $file->store('imports/candidates');
        $extension = strtolower($file->getClientOriginalExtension());
        $originalName = $file->getClientOriginalName();

        // Parse the file
        $rows = $this->readFile(Storage::disk('local')->path($storedPath), $extension);
        $rows = array_values($rows); // Reset indices

        if (count($rows) < 1) {
            throw new Exception('The file is empty or has no data rows.');
        }

        // First row is the header
        $headers = $rows[0];
        $dataRows = array_slice($rows, 1);

        // Detect columns and auto-mapping
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

        // Generate sample rows (first 10)
        $sampleRows = array_slice($dataRows, 0, 10);

        // Build index-based column mapping [colIndex => dbField]
        $indexToField = [];
        foreach ($detectedColumns as $index => $colName) {
            $indexToField[$index] = $autoMapping[$index] ?? null;
        }

        // Build readable auto_mapping (column_name => db_field)
        $readableMapping = [];
        foreach ($autoMapping as $index => $dbField) {
            if ($dbField !== null && isset($detectedColumns[$index])) {
                $readableMapping[$detectedColumns[$index]] = $dbField;
            }
        }

        // Transform sample rows into structured preview objects matching ImportPreviewRow
        $previewRows = [];
        $validCount = 0;
        $rowErrors = [];
        foreach ($sampleRows as $rowIndex => $row) {
            $rowData = [];
            $rowValidationErrors = [];

            foreach ($row as $colIndex => $value) {
                $dbField = $indexToField[$colIndex] ?? null;
                if ($dbField !== null) {
                    // Convert DateTime objects / serial dates to strings
                    if ($value instanceof \DateTimeInterface) {
                        $value = $value->format('Y-m-d');
                    } elseif (is_numeric($value) && $dbField === 'dob') {
                        try {
                            $value = Date::excelToDateTimeObject((float) $value)->format('Y-m-d');
                        } catch (Exception $e) {
                            // Keep original
                        }
                    }
                    $rowData[$dbField] = $value;
                }
            }

            $rowNumber = $rowIndex + 2; // 1-indexed + header

            // Normalize gender for preview
            $gender = isset($rowData['gender']) ? strtolower(trim($rowData['gender'])) : '';
            if (in_array($gender, ['m', 'male', 'ប្រុស'])) {
                $gender = 'Male';
            } elseif (in_array($gender, ['f', 'female', 'ស្រី'])) {
                $gender = 'Female';
            } else {
                $gender = $rowData['gender'] ?? '';
            }

            // Validation
            $requiredFields = ['first_name', 'last_name', 'gender', 'dob', 'phone'];
            foreach ($requiredFields as $field) {
                if (empty($rowData[$field]) && $field !== 'phone') {
                    $rowValidationErrors[] = "{$field} is required";
                }
            }

            $isValid = empty($rowValidationErrors);
            if ($isValid) {
                $validCount++;
            } else {
                $rowErrors[] = "Row {$rowNumber}: " . implode(', ', $rowValidationErrors);
            }

            $previewRows[] = [
                'row'           => $rowNumber,
                'first_name'    => $rowData['first_name'] ?? '',
                'last_name'     => $rowData['last_name'] ?? '',
                'first_name_kh' => $rowData['first_name_kh'] ?? null,
                'last_name_kh'  => $rowData['last_name_kh'] ?? null,
                'gender'        => $gender,
                'dob'           => $rowData['dob'] ?? '',
                'phone'         => $rowData['phone'] ?? null,
                'province_name' => $rowData['province_name'] ?? '',
                'school_name'   => $rowData['school_name'] ?? '',
                'ngo_name'      => $rowData['ngo_name'] ?? '',
                'status'        => $rowData['status'] ?? 'Register',
                'valid'         => $isValid,
                'errors'        => $rowValidationErrors,
            ];
        }

        // Create import file record
        $importFile = ImportFile::create([
            'campaign_id'       => $campaignId,
            'province_id'       => $provinceId,
            'ngo_id'            => $ngoId,
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
        ];
    }

    /**
     * Confirm and execute the import with user-provided column mapping.
     *
     * @param int   $importFileId
     * @param array $columnMapping  e.g. [0 => 'first_name', 1 => 'last_name', 2 => 'gender', ...]
     * @param int   $campaignId
     * @param int   $provinceId
     *
     * @return array{imported_count: int, errors: array}
     */
    public function confirmImport(
        int $importFileId,
        array $columnMapping,
        int $campaignId,
        int $provinceId
    ): array {
        /** @var ImportFile|null $importFile */
        $importFile = ImportFile::find($importFileId);

        if (! $importFile) {
            throw new Exception('Import file not found.');
        }

        if ($importFile->status !== 'pending') {
            throw new Exception('This file has already been imported or has errors.');
        }

        // Read the stored file
        $fullPath = Storage::disk('local')->path($importFile->stored_path);

        if (! file_exists($fullPath)) {
            throw new Exception('The uploaded file no longer exists on the server.');
        }

        $rows = $this->readFile($fullPath, $importFile->file_type);
        $rows = array_values($rows);
        $dataRows = array_slice($rows, 1); // Skip header row

        $importedCount = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($dataRows as $rowIndex => $row) {
                $rowNumber = $rowIndex + 2; // +2 because 1-indexed + header row
                $candidateData = [
                    'campaign_id' => $campaignId,
                    'province_id' => $provinceId,
                ];

                // Apply the NGO from the import context (if selected)
                if ($importFile->ngo_id) {
                    $candidateData['ngo_id'] = $importFile->ngo_id;
                }

                // Protected fields that cannot be set via CSV column mapping
            $protectedFields = ['id', 'campaign_id', 'province_id', 'ngo_id', 'created_at', 'updated_at'];

            // Map columns using user's mapping (skip protected fields)
            foreach ($columnMapping as $csvColIndex => $dbField) {
                $csvColIndex = (int) $csvColIndex;
                if (! isset($row[$csvColIndex])) {
                    continue;
                }

                // Skip protected system fields
                if (in_array($dbField, $protectedFields, true)) {
                    continue;
                }

                $value = $row[$csvColIndex];

                // Convert PhpSpreadsheet date objects to string
                if ($value instanceof \DateTimeInterface) {
                    $value = $value->format('Y-m-d');
                } elseif (is_numeric($value) && $dbField === 'dob') {
                    // Excel serial date number
                    try {
                        $value = Date::excelToDateTimeObject((float) $value)->format('Y-m-d');
                    } catch (Exception $e) {
                        // Keep original value
                    }
                }

                $candidateData[$dbField] = $value;
            }

            // Campaign and province are always set from the import context (not from file)
            $candidateData['campaign_id'] = $campaignId;
            $candidateData['province_id'] = $provinceId;

                // Ensure required fields
                if (empty($candidateData['first_name'])) {
                    $errors[] = "Row {$rowNumber}: First name is required.";
                    continue;
                }
                if (empty($candidateData['last_name'])) {
                    $errors[] = "Row {$rowNumber}: Last name is required.";
                    continue;
                }
                if (empty($candidateData['gender'])) {
                    $errors[] = "Row {$rowNumber}: Gender is required.";
                    continue;
                }
                if (empty($candidateData['dob'])) {
                    $errors[] = "Row {$rowNumber}: Date of birth is required.";
                    continue;
                }
                if (empty($candidateData['phone'])) {
                    $errors[] = "Row {$rowNumber}: Phone is required.";
                    continue;
                }

                // Normalize gender
                $gender = strtolower(trim($candidateData['gender']));
                if (in_array($gender, ['m', 'male', 'ប្រុស'])) {
                    $candidateData['gender'] = 'Male';
                } elseif (in_array($gender, ['f', 'female', 'ស្រី'])) {
                    $candidateData['gender'] = 'Female';
                } elseif (! in_array($candidateData['gender'], ['Male', 'Female', 'Other'])) {
                    $candidateData['gender'] = 'Other';
                }

                // Set default status if not provided
                if (empty($candidateData['status'])) {
                    $candidateData['status'] = 'Register';
                }

                try {
                    // Generate student_id if not provided
                    if (empty($candidateData['student_id'])) {
                        $candidateData['student_id'] = Cadidate::generateStudentId();
                    }
                    
                    Cadidate::create($candidateData);
                    $importedCount++;
                } catch (Exception $e) {
                    $errors[] = "Row {$rowNumber}: {$e->getMessage()}";
                }
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
     * Read a CSV or Excel file and return all rows as arrays.
     */
    private function readFile(string $filePath, string $extension): array
    {
        if (! file_exists($filePath)) {
            throw new Exception("File not found: {$filePath}");
        }

        // For CSV files, use PHP's built-in fgetcsv for simplicity and speed
        if (in_array($extension, ['csv', 'txt'])) {
            return $this->readCsv($filePath);
        }

        // For Excel files, use PhpSpreadsheet
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

        // Auto-detect delimiter
        $firstLine = fgets($handle);
        rewind($handle);
        $delimiter = $this->detectDelimiter($firstLine);

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            // Skip completely empty rows
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
            $worksheet = $spreadsheet->getActiveSheet();
            $data = $worksheet->toArray(null, true, true, false);

            // Remove empty trailing rows
            while (! empty($data) && $this->isRowEmpty(end($data))) {
                array_pop($data);
            }

            // Clean empty leading rows
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
     *
     * Strategy:
     * 1. Exact match (case-insensitive) → immediate return (highest confidence).
     * 2. Contains match → pick the alias with the most characters,
     *    so "Last Name" matches "last name" (9 chars) instead of "name" (4 chars).
     */
    private function autoMapColumn(string $header): ?string
    {
        $clean = strtolower(trim($header));
        // Remove extra spaces and special chars for comparison
        $clean = preg_replace('/[^a-z0-9\s]/', '', $clean);
        // Normalize multiple spaces
        $clean = preg_replace('/\s+/', ' ', $clean);

        $bestMatch = null;
        $bestLength = 0;

        foreach (self::HEADER_ALIASES as $dbField => $aliases) {
            foreach ($aliases as $alias) {
                $aliasClean = preg_replace('/[^a-z0-9\s]/', '', strtolower($alias));
                $aliasClean = preg_replace('/\s+/', ' ', $aliasClean);

                // Exact match (case-insensitive) — immediate return, highest confidence
                if ($clean === $aliasClean) {
                    return $dbField;
                }

                // Contains match — only keep it if it's more specific (longer) than the current best
                if (str_contains($clean, $aliasClean) || str_contains($aliasClean, $clean)) {
                    $aliasLen = strlen($aliasClean);
                    if ($aliasLen > $bestLength) {
                        $bestLength = $aliasLen;
                        $bestMatch = $dbField;
                    }
                }
            }
        }

        return $bestMatch;
    }
}
