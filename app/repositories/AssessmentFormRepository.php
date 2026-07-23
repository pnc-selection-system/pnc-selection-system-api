<?php

namespace App\Repositories;

use App\Models\AssessmentForm;

class AssessmentFormRepository
{
    public function list(array $filters = [])
    {
        return AssessmentForm::with('questions')
            ->when($filters['campaign_id'] ?? null, fn($q, $v) => $q->where('campaign_id', $v))
            ->orderBy('id', 'desc')
            ->get();
    }

    public function find(int $id)
    {
        return AssessmentForm::with('questions')->findOrFail($id);
    }

    public function store(array $data)
    {
        $questions = $data['schema']['fields'] ?? [];
        unset($data['schema']);

        $form = AssessmentForm::create($data);

        foreach ($questions as $i => $field) {
            $form->questions()->create([
                'key' => $field['key'] ?? 'question_' . $i,
                'label' => $field['label'],
                'type' => $field['type'],
                'order' => $i + 1,
                'weight' => $field['weight'] ?? 1,
                'options' => $field['options'] ?? null,
                'point_map' => $field['point_map'] ?? null,
            ]);
        }

        return $form->load('questions');
    }

    public function update(int $id, array $data)
    {
        $form = AssessmentForm::findOrFail($id);
        $form->update($data);

        if (isset($data['schema']['fields'])) {
            $form->questions()->delete();
            foreach ($data['schema']['fields'] as $i => $field) {
                $form->questions()->create([
                    'key' => $field['key'] ?? 'question_' . $i,
                    'label' => $field['label'],
                    'type' => $field['type'],
                    'order' => $i + 1,
                    'weight' => $field['weight'] ?? 1,
                    'options' => $field['options'] ?? null,
                    'point_map' => $field['point_map'] ?? null,
                ]);
            }
        }

        return $form->load('questions');
    }
}
