<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentQuestion extends Model
{
    protected $fillable = [
        'assessment_form_id',
        'key',
        'label',
        'type',
        'order',
        'weight',
        'options',
        'point_map',
    ];

    protected $casts = [
        'options' => 'array',
        'point_map' => 'array',
        'order' => 'integer',
        'weight' => 'integer',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(AssessmentForm::class, 'assessment_form_id');
    }
}
