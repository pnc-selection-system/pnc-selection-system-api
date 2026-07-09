<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'year', 'start_date', 'end_date', 'status'];

    public function infoSessions(): HasMany
    {
        return $this->hasMany(InfoSession::class);
    }
}
