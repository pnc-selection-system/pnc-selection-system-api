<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    protected $fillable = ['province_id', 'district_id', 'commune_id', 'village_id', 'name', 'address'];

    public function province() { return $this->belongsTo(Province::class); }
    public function district() { return $this->belongsTo(District::class); }
    public function commune()  { return $this->belongsTo(Commune::class); }
    public function village()  { return $this->belongsTo(Village::class); }
}
