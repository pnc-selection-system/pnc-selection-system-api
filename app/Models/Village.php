<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Village extends Model
{
    protected $fillable = [
        'commune_id',
        'name'
    ];

    public function commune(): BelongsTo
    {
        return $this->belongsTo(Commune::class);
    }

    public function infoSessions(): HasMany
    {
        return $this->hasMany(InfoSession::class);
    }

    public function schools()
    {
        return $this->hasMany(School::class);
    }
}
