<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    protected $fillable = [
        'village_id',
        'name',
        'address',
    ];

    public function village()
    {
        return $this->belongsTo(Village::class);
    }

    public function infoSessions()
    {
        return $this->hasMany(InfoSession::class);
    }
}
