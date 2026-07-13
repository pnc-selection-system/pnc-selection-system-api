<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InfoSession extends Model
{
    use SoftDeletes;

    protected $table = 'information_sessions';

    protected $fillable = [
        'campaign_id', 'province_id', 'district_id', 'commune_id', 'village_id',
        'school_id', 'partner_type', 'ngo_name', 'ngo_contact',
        'pnc_department_id', 'officer_id', 'date', 'time',
        'hosted_by', 'attendance_count', 'total_participants', 'status',
    ];

    public function province()    { return $this->belongsTo(Province::class); }
    public function district()    { return $this->belongsTo(District::class); }
    public function commune()     { return $this->belongsTo(Commune::class); }
    public function village()     { return $this->belongsTo(Village::class); }
    public function school()      { return $this->belongsTo(School::class); }
    public function participants(){ return $this->hasMany(InterestStudent::class, 'info_session_id'); }
}
