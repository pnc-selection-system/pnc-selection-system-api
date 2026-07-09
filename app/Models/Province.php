<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    protected $fillable = [
        'name',
    ];

    public function candidates()
    {
        return $this->hasMany(Cadidate::class);
    }
}
