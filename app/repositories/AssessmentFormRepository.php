<?php

namespace App\Repositories;

use App\Models\AssessmentForm;
use App\Models\AssessmentQuestion;
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

    /**
     * Sync the assessment_questions table from the schema.fields array.
     * Deletes existing questions for the form and inserts fresh ones.
     */
    protected function syncQuestions(AssessmentForm $form, array $data): void
    {
        $fields = $data['schema']['fields'] ?? null;
        if ($fields === null) {
            return;
        }

        // Delete existing questions for this form
        $form->questions()->delete();

        // Insert new questions from schema fields
        $questions = [];
        foreach ($fields as $i => $field) {
            $questions[] = [
                'assessment_form_id' => $form->id,
                'key' => $field['key'] ?? 'field_' . $i,
                'label' => $field['label'] ?? '',
                'type' => $field['type'] ?? 'text',
                'order' => $i + 1,
                'weight' => $field['weight'] ?? 1,
                'options' => isset($field['options']) ? json_encode($field['options']) : null,
                'point_map' => isset($field['point_map']) ? json_encode($field['point_map']) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($questions)) {
            AssessmentQuestion::insert($questions);
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
        $form = AssessmentForm::create($data);
        $this->syncQuestions($form, $data);
        return $form;
    }

    public function find(int $id): AssessmentForm
    {
        $this->ensureFormTableSchema();
        return AssessmentForm::findOrFail($id);
    }

    public function update(int $id, array $data)
    {
        $this->ensureFormTableSchema();
        $assessmentForm = AssessmentForm::findOrFail($id);
        $assessmentForm->update($data);
        $this->syncQuestions($assessmentForm, $data);
        return $assessmentForm;
    }

    public function delete(AssessmentForm $assessmentForm): void
    {
        $assessmentForm->delete();
    }
}
