<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class School extends Model
{
    protected $fillable = [
<<<<<<< HEAD
        'province_id',
=======
        'village_id',
>>>>>>> e8893d10fffdec2e7c36869599f295c3c32a18e5
        'name',
        'address',
    ];

<<<<<<< HEAD
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function candidates()
    {
        return $this->hasMany(Cadidate::class);
=======
    public function village()
    {
        return $this->belongsTo(Village::class);
    }

    public function infoSessions()
    {
        return $this->hasMany(InfoSession::class);
>>>>>>> e8893d10fffdec2e7c36869599f295c3c32a18e5
    }
}
