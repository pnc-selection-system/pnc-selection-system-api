<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestigationHistory extends Model
{
    protected $table = 'investigation_history';

    /**
     * Indicates if the model should be timestamped.
     * We use our own 'timestamp' column.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'investigation_id',
        'action',
        'user_id',
        'user_name',
        'notes',
        'timestamp',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'timestamp' => 'datetime',
    ];

    /**
     * Get the investigation that owns this history entry.
     */
    public function investigation(): BelongsTo
    {
        return $this->belongsTo(HomeInvestigation::class, 'investigation_id');
    }
}
