<?php

use App\Http\Controllers\Api\AssessmentForm\AssessmentFormController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::get('/assessment-forms',                   [AssessmentFormController::class, 'index'])  ->middleware('permission:assessment.view');
    Route::post('/assessment-forms',                  [AssessmentFormController::class, 'store'])  ->middleware('permission:assessment.manage');
    Route::get('/assessment-forms/{assessment_form}', [AssessmentFormController::class, 'show'])   ->middleware('permission:assessment.view');
    Route::put('/assessment-forms/{assessment_form}', [AssessmentFormController::class, 'update']) ->middleware('permission:assessment.manage');
    Route::patch('/assessment-forms/{assessment_form}', [AssessmentFormController::class, 'update'])->middleware('permission:assessment.manage');
    Route::delete('/assessment-forms/{assessment_form}', [AssessmentFormController::class, 'destroy'])->middleware('permission:assessment.manage');
});
