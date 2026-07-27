<?php

use App\Http\Controllers\Api\AssessmentResponse\AssessmentResponseController;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt.auth')->group(function () {
    // List assessment responses
    Route::apiResource('assessment-responses', AssessmentResponseController::class)
        ->only(['index']);

    // Submit a scored assessment response (custom action with scoring logic)
    Route::post('assessment-responses/submit', [AssessmentResponseController::class, 'submit']);
});
