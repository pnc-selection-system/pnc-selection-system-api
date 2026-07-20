<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentQuestion extends Model
{
    protected $fillable = [
        'form_id',
        'key',
        'label',
        'type',
        'options',
        'point_map',
        'rules',
        'weight',
        'order',
    ];

    protected $casts = [
        'options'   => 'array',
        'point_map' => 'array',
        'rules'     => 'array',
        'weight'    => 'decimal:2',
        'order'     => 'integer',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(AssessmentForm::class, 'form_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(AssessmentRespone::class, 'question_id');
    }
}
