<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentForm extends Model
{
    protected $fillable = [
        'campaign_id',
        'name',
        'pass_threshold',
        'schema',
        'pass_threshold',
    ];

    protected $casts = [
        'schema' => 'array',
        'pass_threshold' => 'integer',
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(AssessmentQuestion::class, 'assessment_form_id');
    }

    public function responses(): HasMany
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

    /**
     * Normalize a raw answer value to a 0.0–1.0 scale for scoring.
     *
     * Priority order:
     * 1. If the field has a `point_map` (e.g. { "1": 0, "2": 0.25, "3": 0.5, "4": 0.75, "5": 1 }),
     *    the exact mapped value is used. This allows non-linear scoring (e.g.
     *    rating 1 = 0 pts, rating 5 = full pts).
     * 2. Otherwise, linear interpolation between `rules.min` and `rules.max`
     *    is applied: (value - min) / (max - min).
     * 3. If no min/max are set in rules, defaults for the field type are used:
     *    - rating (scale 1-5): min=1, max=5
     *    - number: raw value clamped to [0,1]
     */
    protected function normalizeValue(array $field, $value): float
    {
        if (! is_numeric($value)) {
            return 0.0;
        }

        $floatVal = (float) $value;

        // Priority 1: Use point_map if defined for the field
        $pointMap = $field['point_map'] ?? null;
        if (is_array($pointMap) && ! empty($pointMap)) {
            $strVal = (string) $floatVal;
            if (array_key_exists($strVal, $pointMap)) {
                $mapped = (float) $pointMap[$strVal];
                return max(0.0, min(1.0, $mapped));
            }
        }

        // Priority 2: Linear interpolation between min and max
        $config = $field['rules'] ?? [];
        
        // Determine min/max: from rules first, then type defaults
        $type = $field['type'] ?? null;
        
        if (isset($config['min'])) {
            $min = (float) $config['min'];
        } elseif ($type === 'rating') {
            $min = 1.0;
        } else {
            $min = 0.0;
        }

        if (isset($config['max'])) {
            $max = (float) $config['max'];
        } elseif ($type === 'rating') {
            $max = 5.0;
        } else {
            $max = null;
        }

        if ($max !== null && $max > $min) {
            $normalized = ($floatVal - $min) / ($max - $min);
        } else {
            $normalized = $floatVal;
        }

        return max(0.0, min(1.0, $normalized));
    }
}
