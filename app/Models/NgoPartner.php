<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NgoPartner extends Model
{
    protected $fillable = [
        'name',
        'type',
        'address',
        'phone',
        'email',
        'image',
        'active',
        'status',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function contactPersons(): HasMany
    {
        return $this->hasMany(NgoContactPersion::class, 'ngo_partner_id');
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(Cadidate::class, 'ngo_id');
    }
}
