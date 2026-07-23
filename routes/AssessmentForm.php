<?php

use App\Http\Controllers\Api\AssessmentForm\AssessmentFormController;
use App\Http\Controllers\Api\AssessmentForm\ResponseController;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt.auth')->group(function () {
    Route::apiResource('assessment-forms', AssessmentFormController::class);

<<<<<<< HEAD
    // Custom routes for assessment form questions
    Route::get('assessment-forms/{assessmentForm}/questions', [AssessmentFormController::class, 'questions']);
=======
    // Response submission & retrieval
    Route::get('assessment-forms/{assessment_form}/responses', [ResponseController::class, 'index']);
    Route::post('assessment-forms/{assessment_form}/responses', [ResponseController::class, 'store']);
>>>>>>> 50619f08d75aa655de6a2e9ec74408cc5b52580a
});
