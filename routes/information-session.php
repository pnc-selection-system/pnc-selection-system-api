<?php

use App\Http\Controllers\Api\InformationSession\InfoSessionController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {

    // CRUD routes
    Route::apiResource('sessions', InfoSessionController::class);

    // Attendance
    Route::put(
        'sessions/{session}/attendance',
        [InfoSessionController::class, 'updateAttendance']
    );

    // Participants
    Route::post(
        'sessions/{session}/participants',
        [InfoSessionController::class, 'storeParticipant']
    );

    Route::get(
        'sessions/{session}/participants',
        [InfoSessionController::class, 'listParticipants']
    );

    // Convert participant to candidate
    Route::post(
        'participants/{participant}/convert-to-candidate',
        [InfoSessionController::class, 'convertToCandidate']
    );
});