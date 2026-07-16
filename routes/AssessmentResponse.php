<?php

use App\Http\Controllers\Api\AssessmentResponse\AssessmentResponseController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::post('assessment-responses/submit', [AssessmentResponseController::class, 'submit'])->name('assessment-responses.submit');
    Route::apiResource('assessment-responses', AssessmentResponseController::class);
});
