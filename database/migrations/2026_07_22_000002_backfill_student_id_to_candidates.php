<?php

use App\Models\Cadidate;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Get the highest existing student_id to continue the sequence
        $maxStudent = Cadidate::whereNotNull('student_id')
            ->orderByRaw('CAST(student_id AS UNSIGNED) DESC')
            ->first();

        $nextNumber = $maxStudent
            ? (int) $maxStudent->student_id + 1
            : 1;

        // Backfill all candidates with NULL student_id, ordered by id ascending
        $candidates = Cadidate::whereNull('student_id')
            ->orderBy('id')
            ->get();

        foreach ($candidates as $candidate) {
            $studentId = str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
            $candidate->student_id = $studentId;
            $candidate->save();
            $nextNumber++;
        }
    }

    public function down(): void
    {
        // Cannot undo individual student_id assignments without data loss
        // This migration only backfills NULL student_ids
    }
};
