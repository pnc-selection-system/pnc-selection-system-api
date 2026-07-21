<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamSubject extends Model
{
    protected $table = 'exam_subjects';

    protected $fillable = [
        'campaign_id',
        'name',
        'max_score',
        'weight',
        'is_delete',
    ];

    protected $casts = [
        'max_score'       => 'decimal:2',
        'weight'           => 'decimal:2',
        'is_delete'        => 'boolean',
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

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(SelectCampaing::class, 'campaign_id');
    }
}
