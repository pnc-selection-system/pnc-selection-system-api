<?php

use App\Http\Controllers\Api\AssessmentResponse\AssessmentResponseController;
use Illuminate\Support\Facades\Route;

// Public routes - no authentication required
Route::get('assessment-responses', [AssessmentResponseController::class, 'index'])->name('assessment-responses.index');
Route::get('assessment-responses/candidate/{candidateId}', [AssessmentResponseController::class, 'show'])->name('assessment-responses.show');
Route::post('assessment-responses/submit', [AssessmentResponseController::class, 'submit'])->name('assessment-responses.submit');

// Protected routes
Route::middleware('auth:api')->group(function () {
    Route::delete('assessment-responses/candidate/{candidateId}', [AssessmentResponseController::class, 'destroy'])->name('assessment-responses.destroy');
});
