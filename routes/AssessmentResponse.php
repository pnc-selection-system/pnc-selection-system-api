<?php

use App\Http\Controllers\Api\AssessmentResponse\AssessmentResponseController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::get('/assessment-responses', [AssessmentResponseController::class, 'index']);
    Route::post('/assessment-responses/submit', [AssessmentResponseController::class, 'submit']);
});
