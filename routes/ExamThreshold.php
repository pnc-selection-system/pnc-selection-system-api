<?php

use App\Http\Controllers\Api\ExamThreshold\ExamThresholdController;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt.auth')->group(function () {
    // Get thresholds for a campaign
    Route::get('/campaigns/{campaignId}/exam-thresholds', [ExamThresholdController::class, 'index']);
    
    // Store or update overall threshold
    Route::post('/campaigns/{campaignId}/exam-thresholds/overall', [ExamThresholdController::class, 'storeOverall']);
    
    // Store or update subject threshold
    Route::post('/campaigns/{campaignId}/exam-thresholds/subjects/{subjectId}', [ExamThresholdController::class, 'storeSubject']);
    
    // Delete threshold
    Route::delete('/exam-thresholds/{id}', [ExamThresholdController::class, 'destroy']);
});
