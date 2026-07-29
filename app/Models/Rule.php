<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rule extends Model
{
    protected $table = 'rules';

    protected $fillable = [
        'exam_subject_id',
        'name',
        'desc',
        'sign',
        'value',
        'status',
        'is_delete',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'is_delete' => 'boolean',
    ];

    /**
     * Boot the model and register a global scope to exclude soft-deleted records.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('not_deleted', function (Builder $builder) {
            $builder->where('is_delete', false);
        });
    }

    public function examSubject(): BelongsTo
    {
        return $this->belongsTo(ExamSubject::class, 'exam_subject_id');
    }
}
