<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Candidate extends Model
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
        'photo_url',
        'status',
    ];

    protected $casts = [
        'dob' => 'date',
    ];

    protected $appends = ['code', 'exam_score', 'exam_result'];

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

    /**
     * Get the candidate's average exam score across all subjects.
     */
    public function getExamScoreAttribute(): ?float
    {
        // Average final_score across all exam results for this candidate
        $avg = \Illuminate\Support\Facades\DB::table('exam_results')
            ->where('candidate_id', $this->id)
            ->avg('final_score');

        return $avg ? round((float) $avg, 2) : null;
    }

    /**
     * Get the candidate's exam result (pass/fail) based on thresholds.
     */
    public function getExamResultAttribute(): ?string
    {
        $score = $this->exam_score;
        if ($score === null) {
            return null;
        }

        // Check if there's an overall threshold for this campaign
        $threshold = \App\Models\ExamThreshold::where('campaign_id', $this->campaign_id)
            ->whereNull('subject_id')
            ->first();

        $passMark = $threshold ? (float) $threshold->overall_pass_mark : 50;

        return $score >= $passMark ? 'pass' : 'fail';
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
    public function ngoPartner()
    {
        return $this->belongsTo(NgoPartner::class, 'ngo_id');
    }

    public function homeInvestigation(): HasOne
    {
        return $this->hasOne(HomeInvestigation::class, 'candidate_id');
    }

    public function votingRounds(): BelongsToMany
    {
        return $this->belongsToMany(VotingRound::class, 'voting_round_candidates', 'candidate_id', 'voting_round_id')
            ->withTimestamps();
    }
}
