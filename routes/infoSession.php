<?php

use App\Http\Controllers\Api\InfoSession\InfoSessionController;
use Illuminate\Support\Facades\Route;

<<<<<<< HEAD
Route::middleware('auth:api')->group(function () {
    Route::get('/info-sessions', [InfoSessionController::class, 'index']);
    Route::post('/info-sessions', [InfoSessionController::class, 'store']);
    Route::get('/info-sessions/{id}', [InfoSessionController::class, 'show']);
    Route::put('/info-sessions/{id}', [InfoSessionController::class, 'update']);
    Route::delete('/info-sessions/{id}', [InfoSessionController::class, 'destroy']);
});
=======
Route::middleware('jwt.auth')->group(function () {
    Route::apiResource('info-sessions', InfoSessionController::class)
        ->only(['index', 'store', 'show', 'update', 'destroy']);
});
>>>>>>> 8cd37ab8412aabb483c5b30e8829b8975c19875a
