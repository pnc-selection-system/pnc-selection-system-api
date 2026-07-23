<?php

use App\Http\Controllers\Api\AssessmentForm\AssessmentFormController;
use App\Http\Controllers\Api\AssessmentForm\ResponseController;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt.auth')->group(function () {
    Route::apiResource('assessment-forms', AssessmentFormController::class);

    // Response submission & retrieval
    Route::get('assessment-forms/{assessment_form}/responses', [ResponseController::class, 'index']);
    Route::post('assessment-forms/{assessment_form}/responses', [ResponseController::class, 'store']);
});
