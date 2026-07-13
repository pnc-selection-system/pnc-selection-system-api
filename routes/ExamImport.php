<?php

use App\Http\Controllers\Api\Exam\ExamImportController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::post('exam-import/upload', [ExamImportController::class, 'upload']);
    Route::post('exam-import/{importFile}/validate', [ExamImportController::class, 'validate']);
});
