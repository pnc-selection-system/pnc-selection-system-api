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
        return $this->hasMany(AssessmentResponse::class, 'assessment_form_id');
    }
}
