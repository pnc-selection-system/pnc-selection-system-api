<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class School extends Model
{
    protected $fillable = [

        'village_id',
        'name',
    ];

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function candidates()
    {
        return $this->hasMany(Cadidate::class);


    public function infoSessions()
    {
        return $this->hasMany(InfoSession::class);
    }
}

}
