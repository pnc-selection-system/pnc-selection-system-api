<?php

use App\Http\Controllers\Api\InterestStudent\ConvertToCandidateController;
use App\Http\Controllers\Api\InterestStudent\InterestStudentController;
use Illuminate\Support\Facades\Route;

// Interested Student Routes
Route::middleware('auth:api')->group(function () {
    Route::apiResource('interested-students', InterestStudentController::class);
    
    // Convert Interested Student to Candidate
    Route::post('/interested-students/{id}/convert-to-candidate', [ConvertToCandidateController::class, '__invoke']);
});
