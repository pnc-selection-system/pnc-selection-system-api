<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    protected $fillable = ['province_id', 'name', 'address'];

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function infoSessions(): HasMany
    {
        return $this->hasMany(InfoSession::class);
    }
}