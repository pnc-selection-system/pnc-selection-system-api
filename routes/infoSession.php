<?php

use App\Http\Controllers\Api\InfoSession\InfoSessionController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::get('/info-sessions',        [InfoSessionController::class, 'index'])   ->middleware('permission:sessions.view');
    Route::post('/info-sessions',       [InfoSessionController::class, 'store'])   ->middleware('permission:sessions.create');
    Route::get('/info-sessions/{id}',   [InfoSessionController::class, 'show'])    ->middleware('permission:sessions.view');
    Route::put('/info-sessions/{id}',   [InfoSessionController::class, 'update'])  ->middleware('permission:sessions.edit');
    Route::delete('/info-sessions/{id}',[InfoSessionController::class, 'destroy']) ->middleware('permission:sessions.delete');
});