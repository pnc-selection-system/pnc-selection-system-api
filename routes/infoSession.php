<?php

use App\Http\Controllers\Api\InfoSession\InfoSessionController;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt.auth')->group(function () {
    Route::apiResource('info-sessions', InfoSessionController::class)
        ->only(['index', 'store', 'show', 'update', 'destroy']);
});
