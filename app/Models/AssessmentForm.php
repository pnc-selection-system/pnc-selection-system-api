<?php

namespace App\Models;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Validator as ValidatorFacade;

class AssessmentForm extends Model
{
    protected $fillable = [
        'campaign_id',
        'name',
        'schema',
    ];

    protected $casts = [
        'schema' => 'array',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(SelectCampaing::class, 'campaign_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(AssessmentRespone::class, 'form_id');
    }

    public function fields(): array
    {
        return $this->schema['fields'] ?? [];
    }

    public function totalWeight(): float
    {
        return (float) collect($this->fields())->sum('weight');
    }

    public function responseRules(): array
    {
        $rules = [];

        foreach ($this->fields() as $field) {
            $key = $field['key'] ?? null;

            if ($key === null) {
                continue;
            }

            $fieldRules = [];
            $fieldRulesConfig = $field['rules'] ?? [];
            $type = $field['type'] ?? 'text';

            if (! empty($fieldRulesConfig['required'])) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            $numericTypes = ['number', 'rating'];

            if (in_array($type, $numericTypes, true)) {
                $fieldRules[] = 'numeric';

                if (isset($fieldRulesConfig['min'])) {
                    $fieldRules[] = 'min:'.$fieldRulesConfig['min'];
                }

                if (isset($fieldRulesConfig['max'])) {
                    $fieldRules[] = 'max:'.$fieldRulesConfig['max'];
                }
            } elseif (in_array($type, ['select', 'radio', 'checkbox'], true)) {
                $fieldRules[] = 'in:'.implode(',', (array) ($field['options'] ?? $fieldRulesConfig['in'] ?? []));
            } else {
                $fieldRules[] = 'string';

                if (isset($fieldRulesConfig['min'])) {
                    $fieldRules[] = 'min:'.$fieldRulesConfig['min'];
                }

                if (isset($fieldRulesConfig['max'])) {
                    $fieldRules[] = 'max:'.$fieldRulesConfig['max'];
                }
            }

            if (isset($fieldRulesConfig['regex'])) {
                $fieldRules[] = 'regex:'.$fieldRulesConfig['regex'];
            }

            $rules[$key] = $fieldRules;
        }

        return $rules;
    }

    public function validateResponse(array $data): Validator
    {
        return ValidatorFacade::make($data, $this->responseRules());
    }

    public function scoreResponse(array $data): float
    {
        $totalWeight = $this->totalWeight();

        if ($totalWeight <= 0) {
            return 0.0;
        }

        $weightedScore = 0.0;

        foreach ($this->fields() as $field) {
            $key = $field['key'] ?? null;

            if ($key === null || ! array_key_exists($key, $data)) {
                continue;
            }

            $weight = (float) ($field['weight'] ?? 0);

            if ($weight <= 0) {
                continue;
            }

            $value = $data[$key];
            $normalized = $this->normalizeValue($field, $value);

            $weightedScore += $weight * $normalized;
        }

        return round($weightedScore / $totalWeight * 100, 2);
    }

    protected function normalizeValue(array $field, $value): float
    {
        if (! is_numeric($value)) {
            return 0.0;
        }

        $config = $field['rules'] ?? [];
        $min = isset($config['min']) ? (float) $config['min'] : 0;
        $max = isset($config['max']) ? (float) $config['max'] : null;

        if ($max !== null && $max > $min) {
            $normalized = ((float) $value - $min) / ($max - $min);
        } else {
            $normalized = (float) $value;
        }

        return max(0.0, min(1.0, $normalized));
    }
}
