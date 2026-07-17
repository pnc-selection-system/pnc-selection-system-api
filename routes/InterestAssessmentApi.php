<?php

use App\Http\Controllers\Api\InterestAssessment\InterestAssessmentApiController;
use Illuminate\Support\Facades\Route;

// Public API routes - no authentication required
Route::get('/interest-assessment/{sessionId}', [InterestAssessmentApiController::class, 'show'])
    ->name('api.interest-assessment.show');

Route::post('/interest-assessment/{sessionId}', [InterestAssessmentApiController::class, 'store'])
    ->name('api.interest-assessment.store');
