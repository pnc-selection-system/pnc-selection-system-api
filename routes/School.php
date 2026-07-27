<?php

use App\Http\Controllers\Api\School\SchoolController;
use Illuminate\Support\Facades\Route;

// Schools are reference data accessible to all authenticated users
Route::middleware('auth:api')->group(function () {
    Route::get('/schools',              [SchoolController::class, 'index']);
    Route::post('/schools',             [SchoolController::class, 'store']);
    Route::get('/schools/{school}',     [SchoolController::class, 'show']);
    Route::put('/schools/{school}',     [SchoolController::class, 'update']);
    Route::patch('/schools/{school}',  [SchoolController::class, 'update']);
    Route::delete('/schools/{school}',  [SchoolController::class, 'destroy']);
});
