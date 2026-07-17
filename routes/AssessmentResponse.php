<?php

use App\Http\Controllers\Api\AssessmentResponse\AssessmentResponseController;
use Illuminate\Support\Facades\Route;

// Public route - no authentication required for submitting responses
Route::post('assessment-responses/submit', [AssessmentResponseController::class, 'submit'])->name('assessment-responses.submit');

// Protected routes - authentication required for CRUD operations
Route::middleware('auth:api')->group(function () {
    Route::apiResource('assessment-responses', AssessmentResponseController::class);
});
