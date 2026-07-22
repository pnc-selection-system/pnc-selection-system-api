<?php

use App\Http\Controllers\Api\ExamResult\ExamResultImportController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    // Upload file for exam result import
    Route::post('/exam-results/import/upload', [ExamResultImportController::class, 'upload']);
    
    // Confirm exam result import
    Route::post('/exam-results/import/confirm', [ExamResultImportController::class, 'confirm']);
});
