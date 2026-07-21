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
<<<<<<< HEAD
        return $this->hasMany(Candidate::class);
=======
        return $this->hasMany(Cadidate::class);
    public function village()
    {
        return $this->belongsTo(Village::class);
    }

    public function infoSessions()
    {
        return $this->hasMany(InfoSession::class);
>>>>>>> 4f773a29f9f06f0ee19adec429b00e34e57959cb
    }
}

}
