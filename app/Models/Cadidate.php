<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Cadidate extends Model
{
    protected $table = 'candidates';

    protected $fillable = [
        'student_id',
        'campaign_id',
        'province_id',
        'school_name',
        'ngo_id',
        'first_name',
        'last_name',
        'first_name_kh',
        'last_name_kh',
        'gender',
        'dob',
        'phone',
        'status',
    ];

    protected $casts = [
        'dob' => 'date',
    ];

    protected $appends = ['code'];

    /**
     * Get the candidate's code (alias for student_id).
     */
    public function getCodeAttribute(): string
    {
        return $this->student_id ?? str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Boot the model and register events.
     */
    protected static function booted(): void
    {
        static::creating(function ($candidate) {
            if (empty($candidate->student_id)) {
                $candidate->student_id = self::generateStudentId();
            }
        });
    }

    /**
     * Generate a unique student ID in format 0001, 0002, etc.
     */
    public static function generateStudentId(): string
    {
        // Get the highest student_id from the database
        $lastStudent = self::whereNotNull('student_id')
            ->orderBy('student_id', 'desc')
            ->first();

        if ($lastStudent) {
            // Extract the numeric part and increment
            $lastNumber = (int) $lastStudent->student_id;
            $nextNumber = $lastNumber + 1;
        } else {
            // Start from 1 if no students exist
            $nextNumber = 1;
        }

        // Format as 4-digit number with leading zeros
        return str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(SelectCampaing::class, 'campaign_id');
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function referringNgo(): BelongsTo
    {
        return $this->belongsTo(NgoPartner::class, 'ngo_id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(CandidateStatusHistory::class, 'candidate_id');
    }
}
