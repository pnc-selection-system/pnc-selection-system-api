<?php

use App\Http\Controllers\Api\ExamSubject\ExamSubjectController;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt.auth')->group(function () {
    Route::apiResource('exam-subjects', ExamSubjectController::class);
});
