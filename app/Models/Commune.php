<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commune extends Model
{
    protected $fillable = ['district_id', 'name'];

    public function district() { return $this->belongsTo(District::class); }
    public function villages() { return $this->hasMany(Village::class); }
}
