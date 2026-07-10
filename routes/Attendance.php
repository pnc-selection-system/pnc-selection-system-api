<?php

use App\Http\Controllers\Api\Attendance\AttendanceController;
use Illuminate\Support\Facades\Route;

// Attendance Routes (Nested resource under Info Sessions)
// Using apiResource for nested resource with only needed methods
Route::middleware('auth:api')->group(function () {
    Route::apiResource('info-sessions.attendance', AttendanceController::class)->only(['store', 'show', 'update']);
});
