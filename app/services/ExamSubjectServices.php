<?php

namespace Services;

use App\Models\ExamSubject;
use Illuminate\Validation\ValidationException;
use Repositories\ExamSubjectRepository;

class ExamSubjectServices
{
    public function __construct(protected ExamSubjectRepository $examSubjectRepository) {}

    /**
     * List exam subjects, optionally filtered by campaign_id.
     */
    public function list(array $filters = [])
    {
        return $this->examSubjectRepository->list($filters);
    }

    /**
     * Create a new exam subject with validated deduction_rules.
     */
    public function create(array $data): ExamSubject
    {
        $data = $this->validateDeductionRules($data);

        return $this->examSubjectRepository->create($data);
    }

    /**
     * Find an exam subject by model.
     */
    public function find(ExamSubject $examSubject): ExamSubject
    {
        return $this->examSubjectRepository->find($examSubject);
    }

    /**
     * Update an exam subject with validated deduction_rules.
     */
    public function update(ExamSubject $examSubject, array $data): ExamSubject
    {
        $data = $this->validateDeductionRules($data);

        return $this->examSubjectRepository->update($examSubject, $data);
    }

    /**
     * Delete an exam subject.
     */
    public function delete(ExamSubject $examSubject): void
    {
        $this->examSubjectRepository->delete($examSubject);
    }

    /**
     * Validate and normalise deduction_rules JSONB payload.
     *
     * Expected shape:
     * {
     *   "wrong_answer": -0.25,       // Penalty per wrong answer (≤ 0)
     *   "unanswered": 0,             // Penalty per unanswered question (≤ 0)
     *   "negative_marking": true,    // Whether negative scores are allowed
     *   "partial_credit": 0.5        // Partial credit multiplier (0-1)
     * }
     *
     * All keys are optional; only provided keys are validated.
     */
    public function validateDeductionRules(array $data): array
    {
        if (! isset($data['deduction_rules']) || ! is_array($data['deduction_rules'])) {
            return $data; // No deduction rules to validate
        }

        $rules = $data['deduction_rules'];
        $errors = [];

        // wrong_answer must be ≤ 0 (or absent)
        if (array_key_exists('wrong_answer', $rules)) {
            if (! is_numeric($rules['wrong_answer'])) {
                $errors['deduction_rules.wrong_answer'] = 'wrong_answer must be a numeric value.';
            } elseif ((float) $rules['wrong_answer'] > 0) {
                $errors['deduction_rules.wrong_answer'] = 'wrong_answer must be ≤ 0 (e.g. -0.25).';
            }
        }

        // unanswered must be ≤ 0 (or absent)
        if (array_key_exists('unanswered', $rules)) {
            if (! is_numeric($rules['unanswered'])) {
                $errors['deduction_rules.unanswered'] = 'unanswered must be a numeric value.';
            } elseif ((float) $rules['unanswered'] > 0) {
                $errors['deduction_rules.unanswered'] = 'unanswered must be ≤ 0 (e.g. 0 or -0.5).';
            }
        }

        // negative_marking must be boolean (or absent)
        if (array_key_exists('negative_marking', $rules)) {
            if (! is_bool($rules['negative_marking']) && ! in_array($rules['negative_marking'], [0, 1, '0', '1', true, false], true)) {
                $errors['deduction_rules.negative_marking'] = 'negative_marking must be a boolean.';
            }
        }

        // partial_credit must be between 0 and 1 (or absent)
        if (array_key_exists('partial_credit', $rules)) {
            if (! is_numeric($rules['partial_credit'])) {
                $errors['deduction_rules.partial_credit'] = 'partial_credit must be a numeric value.';
            } else {
                $pc = (float) $rules['partial_credit'];
                if ($pc < 0 || $pc > 1) {
                    $errors['deduction_rules.partial_credit'] = 'partial_credit must be between 0 and 1.';
                }
            }
        }

        // Custom / additional rule keys are allowed but must be numeric values
        $allowedKeys = ['wrong_answer', 'unanswered', 'negative_marking', 'partial_credit'];
        foreach ($rules as $key => $value) {
            if (! in_array($key, $allowedKeys, true)) {
                if (! is_numeric($value) && ! is_bool($value)) {
                    $errors["deduction_rules.{$key}"] = "Custom rule '{$key}' must be a numeric or boolean value.";
                }
            }
        }

        if (! empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        // Cast values to proper types
        if (array_key_exists('wrong_answer', $rules)) {
            $data['deduction_rules']['wrong_answer'] = (float) $rules['wrong_answer'];
        }
        if (array_key_exists('unanswered', $rules)) {
            $data['deduction_rules']['unanswered'] = (float) $rules['unanswered'];
        }
        if (array_key_exists('negative_marking', $rules)) {
            $data['deduction_rules']['negative_marking'] = (bool) $rules['negative_marking'];
        }
        if (array_key_exists('partial_credit', $rules)) {
            $data['deduction_rules']['partial_credit'] = (float) $rules['partial_credit'];
        }

        return $data;
    }

    /**
     * Validate that all subjects for a given campaign have weights summing to 100%.
     *
     * @throws ValidationException
     */
    public function validateWeightsSumTo100(int $campaignId): void
    {
        $totalWeight = ExamSubject::where('campaign_id', $campaignId)
            ->sum('weight');

        if (abs($totalWeight - 100) > 0.01) {
            throw ValidationException::withMessages([
                'weight' => "Subject weights sum to {$totalWeight}%, but they must sum to exactly 100% before publishing.",
            ]);
        }
    }

    /**
     * Publish validation: check weights sum before allowing publish.
     */
    public function validateForPublish(int $campaignId): void
    {
        $this->validateWeightsSumTo100($campaignId);
    }
}
