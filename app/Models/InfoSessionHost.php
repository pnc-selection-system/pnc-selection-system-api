<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InfoSessionHost extends Model
{
    protected $fillable = [
        'info_session_id',
        'host_by',
    ];

    public function infoSession(): BelongsTo
    {
        return $this->belongsTo(InfoSession::class);
    }
}