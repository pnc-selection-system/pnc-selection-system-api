<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Province extends Model
{
    protected $fillable = [
        'name',
    ];

<<<<<<< HEAD
    public function candidates()
    {
        return $this->hasMany(Candidate::class);

    }
=======
>>>>>>> 4f773a29f9f06f0ee19adec429b00e34e57959cb
    public function districts(): HasMany
    {
        return $this->hasMany(District::class);
    }

    public function schools(): HasMany
    {
        return $this->hasMany(School::class);
    }
}
