<?php

use App\Http\Controllers\Api\InformationSession\InfoSessionController;
use App\Http\Controllers\Api\InterestStudent\InterestStudentController;
use App\Http\Controllers\Api\InterestStudent\ConvertToCandidateController;
use App\Http\Controllers\Api\Province\ProvinceController;
use App\Http\Controllers\Api\School\SchoolController;
use App\Http\Controllers\Api\Attendance\AttendanceController;
use App\Http\Controllers\Api\Report\ReportController;
use Illuminate\Support\Facades\Route;

// Province Routes
Route::get('/provinces', [ProvinceController::class, 'index']);
Route::post('/provinces', [ProvinceController::class, 'store']);
Route::get('/provinces/{id}', [ProvinceController::class, 'show']);
Route::put('/provinces/{id}', [ProvinceController::class, 'update']);
Route::delete('/provinces/{id}', [ProvinceController::class, 'destroy']);

// School Routes
Route::get('/schools', [SchoolController::class, 'index']);
Route::post('/schools', [SchoolController::class, 'store']);
Route::get('/schools/{id}', [SchoolController::class, 'show']);
Route::put('/schools/{id}', [SchoolController::class, 'update']);
Route::delete('/schools/{id}', [SchoolController::class, 'destroy']);

// Information Session Routes
Route::get('/info-sessions', [InfoSessionController::class, 'index']);
Route::post('/info-sessions', [InfoSessionController::class, 'store']);
Route::get('/info-sessions/{id}', [InfoSessionController::class, 'show']);
Route::put('/info-sessions/{id}', [InfoSessionController::class, 'update']);
Route::delete('/info-sessions/{id}', [InfoSessionController::class, 'destroy']);

// Attendance Routes
Route::post('/info-sessions/{infoSessionId}/attendance', [AttendanceController::class, 'store']);
Route::get('/info-sessions/{infoSessionId}/attendance', [AttendanceController::class, 'show']);
Route::put('/info-sessions/{infoSessionId}/attendance', [AttendanceController::class, 'update']);

// Interested Student Routes
Route::get('/interested-students', [InterestStudentController::class, 'index']);
Route::post('/interested-students', [InterestStudentController::class, 'store']);
Route::get('/interested-students/{id}', [InterestStudentController::class, 'show']);
Route::put('/interested-students/{id}', [InterestStudentController::class, 'update']);
Route::delete('/interested-students/{id}', [InterestStudentController::class, 'destroy']);

// Convert Interested Student to Candidate
Route::post('/interested-students/{id}/convert-to-candidate', [ConvertToCandidateController::class, '__invoke']);

// Report Routes
Route::get('/reports/sessions', [ReportController::class, 'sessionStatistics']);
Route::get('/reports/attendance', [ReportController::class, 'attendanceStatistics']);
Route::get('/reports/conversion', [ReportController::class, 'conversionStatistics']);
Route::get('/reports/dashboard', [ReportController::class, 'dashboard']);
