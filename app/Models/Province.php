<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
