<?php

use App\Http\Controllers\Api\InfoSession\InfoSessionController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::get('/info-sessions', [InfoSessionController::class, 'index']);
    Route::post('/info-sessions', [InfoSessionController::class, 'store']);
    Route::get('/info-sessions/{id}', [InfoSessionController::class, 'show']);
    Route::put('/info-sessions/{id}', [InfoSessionController::class, 'update']);
});