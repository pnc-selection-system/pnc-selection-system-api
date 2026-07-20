<?php

use App\Http\Controllers\Api\InterestAssessment\InterestAssessmentController;
use Illuminate\Support\Facades\Route;

// Public routes - no authentication required
Route::get('/interest-assessment/{sessionId}', [InterestAssessmentController::class, 'show'])
    ->name('interest-assessment.show');

Route::post('/interest-assessment/{sessionId}', [InterestAssessmentController::class, 'store'])
    ->name('interest-assessment.store');
