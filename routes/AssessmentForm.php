<?php

use App\Http\Controllers\Api\AssessmentForm\AssessmentFormController;
use Illuminate\Support\Facades\Route;

// Public routes - no authentication required
Route::get('assessment-forms', [AssessmentFormController::class, 'index'])->name('assessment-forms.index');
Route::get('assessment-forms/{assessmentForm}', [AssessmentFormController::class, 'show'])->name('assessment-forms.show');
Route::get('assessment-forms/{assessmentForm}/questions', [AssessmentFormController::class, 'questions'])->name('assessment-forms.questions');

// Protected routes - authentication required for CRUD operations
Route::middleware('auth:api')->group(function () {
    Route::apiResource('assessment-forms', AssessmentFormController::class)->except(['index', 'show']);
    Route::post('assessment-forms/{assessmentForm}/questions', [AssessmentFormController::class, 'addQuestion'])->name('assessment-forms.questions.add');
    Route::delete('assessment-forms/{assessmentForm}/questions/{question}', [AssessmentFormController::class, 'removeQuestion'])->name('assessment-forms.questions.remove');
});
