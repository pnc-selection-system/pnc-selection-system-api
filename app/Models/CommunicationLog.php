<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunicationLog extends Model
{
    protected $fillable = [
        'ngo_partner_id',
        'date',
        'channel',
        'summary',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
    ];

    public function ngoPartner(): BelongsTo
    {
        return $this->belongsTo(NgoPartner::class);
    }
}
