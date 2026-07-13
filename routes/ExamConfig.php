<?php

use App\Http\Controllers\Api\Exam\ExamConfigController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::post('exam-config/{campaignId}/preview', [ExamConfigController::class, 'preview']);
    Route::get('exam-config/{campaignId}', [ExamConfigController::class, 'show']);
    Route::put('exam-config/{campaignId}', [ExamConfigController::class, 'update']);
});
