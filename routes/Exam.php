<?php

use App\Http\Controllers\Api\Exam\ExamController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::get('/exams',          [ExamController::class, 'index'])  ->middleware('permission:exam.view');
    Route::post('/exams',         [ExamController::class, 'store'])  ->middleware('permission:exam.configure');
    Route::get('/exams/{exam}',   [ExamController::class, 'show'])   ->middleware('permission:exam.view');
    Route::put('/exams/{exam}',   [ExamController::class, 'update']) ->middleware('permission:exam.configure');
    Route::patch('/exams/{exam}',[ExamController::class, 'update'])->middleware('permission:exam.configure');
    Route::delete('/exams/{exam}',[ExamController::class, 'destroy'])->middleware('permission:exam.configure');
});
