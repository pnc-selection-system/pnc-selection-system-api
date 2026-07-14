<?php

use App\Http\Controllers\Api\InfoSession\InfoSessionController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::post('/info-sessions', [InfoSessionController::class, 'store']);
});