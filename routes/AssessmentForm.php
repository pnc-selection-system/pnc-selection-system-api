<?php

use App\Http\Controllers\Api\AssessmentForm\AssessmentFormController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::get('/assessment-forms', [AssessmentFormController::class, 'index']);
    Route::post('/assessment-forms', [AssessmentFormController::class, 'store']);
    Route::get('/assessment-forms/{id}', [AssessmentFormController::class, 'show']);
    Route::put('/assessment-forms/{id}', [AssessmentFormController::class, 'update']);
    Route::get('/assessment-forms/{id}/questions', [AssessmentFormController::class, 'questions']);
});
