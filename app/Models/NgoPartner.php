<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NgoPartner extends Model
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'name',
        'type',
        'address',
        'phone',
        'email',
        'active',
        'status',
    ];

    protected $casts = [
        'active' => 'boolean',
        'status' => 'string',
    ];

    public function contactPersons(): HasMany
    {
        return $this->hasMany(NgoContactPersion::class, 'ngo_partner_id');
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class, 'ngo_id');
    }

    public function communicationLogs(): HasMany
    {
        return $this->hasMany(CommunicationLog::class, 'ngo_partner_id');
    }
}
