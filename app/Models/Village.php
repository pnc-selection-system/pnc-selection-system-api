<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Village extends Model
{
    protected $fillable = ['commune_id', 'name'];

    public function commune() { return $this->belongsTo(Commune::class); }
    public function schools() { return $this->hasMany(School::class); }
}
