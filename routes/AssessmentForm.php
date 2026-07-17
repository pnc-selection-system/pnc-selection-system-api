<?php

use App\Http\Controllers\Api\AssessmentForm\AssessmentFormController;
use Illuminate\Support\Facades\Route;

// Public route - no authentication required for viewing form schema
Route::get('assessment-forms/{assessmentForm}', [AssessmentFormController::class, 'show'])->name('assessment-forms.show');

// Protected routes - authentication required for CRUD operations
Route::middleware('auth:api')->group(function () {
    Route::apiResource('assessment-forms', AssessmentFormController::class)->except(['show']);
});
