<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class School extends Model
{
    protected $fillable = [
        'district_id',
        'name',
    ];

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function candidates()
    {
<<<<<<< HEAD
        return $this->hasMany(Candidate::class);
    }
=======
        return $this->hasMany(Cadidate::class);

>>>>>>> 32949326a3c899c0987e8e0bf1925d80e70b7891

    public function infoSessions()
    {
        return $this->hasMany(InfoSession::class);
    }
}
