<?php

namespace Repositories;

use App\Models\ExamSubject;
use App\Models\Rule;

class ExamSubjectRepository
{
    public function list(array $filters = [])
    {
        return ExamSubject::select('id', 'campaign_id', 'name', 'max_score', 'weight', 'is_delete', 'created_at')
            ->with('campaign:id,name,status')
            ->with('rules')
            ->when(
                !empty($filters['campaign_id']),
                fn($q) => $q->where('campaign_id', (int) $filters['campaign_id'])
            )
            ->latest('id')
            ->get();
    }

    public function create(array $data): ExamSubject
    {
        $rules = $data['rules'] ?? null;
        unset($data['rules']);

        $examSubject = ExamSubject::create($data);

        if ($rules && is_array($rules)) {
            foreach ($rules as $rule) {
                $examSubject->rules()->create($rule);
            }
        }

        $examSubject->load('rules');

        return $examSubject;
    }

    public function find(ExamSubject $examSubject): ExamSubject
    {
        $examSubject->load('campaign:id,name,status');
        $examSubject->load('rules');

        return $examSubject;
    }

    public function update(ExamSubject $examSubject, array $data): ExamSubject
    {
        $rules = $data['rules'] ?? null;
        unset($data['rules']);

        $examSubject->update($data);

        if ($rules !== null) {
            $this->syncRules($examSubject, $rules);
        }

        $examSubject->refresh();
        $examSubject->load('campaign:id,name,status');
        $examSubject->load('rules');

        return $examSubject;
    }

    /**
     * Soft delete: set is_delete = true instead of removing the record.
     */
    public function delete(ExamSubject $examSubject): void
    {
        $examSubject->update(['is_delete' => true]);
        $examSubject->rules()->update(['is_delete' => true]);
    }

    /**
     * Sync rules for an exam subject.
     * Creates new rules, updates existing ones, and soft-deletes removed ones.
     */
    private function syncRules(ExamSubject $examSubject, array $rules): void
    {
        $requestRuleIds = [];

        foreach ($rules as $rule) {
            if (isset($rule['id'])) {
                $requestRuleIds[] = $rule['id'];
            }
        }

        foreach ($rules as $rule) {
            if (isset($rule['id'])) {
                $existingRule = Rule::withoutGlobalScope('not_deleted')
                    ->where('id', $rule['id'])
                    ->where('exam_subject_id', $examSubject->id)
                    ->first();

                if ($existingRule) {
                    $existingRule->update($rule);
                }
            } else {
                $newRule = $examSubject->rules()->create($rule);
                // Track the newly created rule's ID so it won't be soft-deleted below
                $requestRuleIds[] = $newRule->id;
            }
        }

        $rulesToDelete = Rule::withoutGlobalScope('not_deleted')
            ->where('exam_subject_id', $examSubject->id)
            ->whereNotIn('id', $requestRuleIds)
            ->get();

        foreach ($rulesToDelete as $rule) {
            $rule->update(['is_delete' => true]);
        }
    }
}
