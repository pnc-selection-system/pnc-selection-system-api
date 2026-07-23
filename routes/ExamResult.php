<?php

use App\Http\Controllers\Api\ExamResult\ExamResultImportController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\ExamResult\ResultsController;

Route::middleware('auth:api')->group(function () {
    // Upload file for exam result import
    Route::post('/exam-results/import/upload', [ExamResultImportController::class, 'upload']);
    
    // Validate column mapping and data before committing
    Route::post('/exam-results/import/validate', [ExamResultImportController::class, 'validate']);
    
    // Confirm and commit exam result import
    Route::post('/exam-results/import/confirm', [ExamResultImportController::class, 'confirm']);
    
    // Results & Analytics endpoints
    Route::get('/exam-results/rounds', [ResultsController::class, 'rounds']);
    Route::get('/exam-results/provinces', [ResultsController::class, 'provinces']);
    Route::get('/exam-results/summary', [ResultsController::class, 'summary']);
    Route::get('/exam-results/distribution', [ResultsController::class, 'distribution']);
    Route::get('/exam-results/table', [ResultsController::class, 'table']);
});
