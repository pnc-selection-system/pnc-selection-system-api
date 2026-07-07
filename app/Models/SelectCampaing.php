<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SelectCampaing extends Model
{
    protected $table = 'selection_campaigns';

    protected $fillable = [
        'name',
        'year',
        'condidate_total',
        'start_date',
        'end_date',
        'status',
    ];
}
