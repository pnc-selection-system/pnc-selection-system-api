<?php

namespace App\Repositories;

use App\Models\AssessmentForm;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AssessmentFormRepository
{
    protected static bool $schemaFixAttempted = false;

    /**
     * Auto-fix: Ensure the assessment_forms table has all expected columns.
     * Runs only once per request.
     */
    protected function ensureFormTableSchema(): void
    {
        if (static::$schemaFixAttempted) {
            return;
        }
        static::$schemaFixAttempted = true;

        try {
            // Get existing columns
            $schemaName = DB::connection()->getDatabaseName();
            $existingColumns = DB::select(
                "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'assessment_forms'",
                [$schemaName]
            );
            $existingColNames = array_column($existingColumns, 'COLUMN_NAME');

            // Add schema column if missing (critical for saving form fields)
            if (!in_array('schema', $existingColNames)) {
                Schema::table('assessment_forms', function ($table) {
                    $table->json('schema')->nullable();
                });
            }
        } catch (\Exception $e) {
            // Silently fail
        }
    }

    public function list(array $filters = [])
    {
        $this->ensureFormTableSchema();

        return AssessmentForm::select('id', 'campaign_id', 'name')
            ->with('campaign:id,name,year,status')
            ->when(
                !empty($filters['campaign_id']),
                fn($q) => $q->where('campaign_id', (int) $filters['campaign_id'])
            )
            ->latest('id')
            ->paginate($filters['per_page'] ?? 10);
    }

    public function create(array $data): AssessmentForm
    {
        $this->ensureFormTableSchema();
        return AssessmentForm::create($data);
    }

    public function find(AssessmentForm $assessmentForm): AssessmentForm
    {
        $this->ensureFormTableSchema();
        return $assessmentForm;
    }

    public function update(int $id, array $data)
    {
        $this->ensureFormTableSchema();
        $assessmentForm = AssessmentForm::findOrFail($id);
        $assessmentForm->update($data);
        return $assessmentForm;
    }

    public function delete(AssessmentForm $assessmentForm): void
    {
        $assessmentForm->delete();
    }
}
