<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NgoContactPersion extends Model
{
    protected $table = 'ngo_contact_persons';

    protected $fillable = [
        'ngo_partner_id',
        'full_name',
        'email',
        'phone',
    ];

    public function ngoPartner(): BelongsTo
    {
        return $this->belongsTo(NgoPartner::class);
    }
}
