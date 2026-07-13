<?php

use App\Http\Controllers\Api\Exam\ExamSubjectController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::get('exam-subjects/validate-weights/{campaignId}', [ExamSubjectController::class, 'validateWeights']);
    Route::apiResource('exam-subjects', ExamSubjectController::class);
});
