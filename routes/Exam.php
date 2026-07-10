<?php

use App\Http\Controllers\Api\Exam\ExamController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::apiResource('exams', ExamController::class);
});
