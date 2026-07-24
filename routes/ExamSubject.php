<?php

use App\Http\Controllers\Api\ExamSubject\ExamSubjectController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::apiResource('exam-subjects', ExamSubjectController::class);
});
