<?php

use App\Http\Controllers\Api\Exam\ExamResultController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    // Ranking & stats
    Route::get('exam-results/{campaignId}/ranking', [ExamResultController::class, 'ranking']);
    Route::get('exam-results/{campaignId}/stats', [ExamResultController::class, 'stats']);

    // Threshold application
    Route::post('exam-results/{campaignId}/apply-thresholds', [ExamResultController::class, 'applyThresholds']);

    // Publish / Lock / Recalculate
    Route::post('exam-results/{campaignId}/publish', [ExamResultController::class, 'publish']);
    Route::post('exam-results/{campaignId}/recalculate', [ExamResultController::class, 'recalculate']);
});
