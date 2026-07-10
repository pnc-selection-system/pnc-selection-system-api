<?php

use App\Http\Controllers\Api\InformationSession\InfoSessionController;
use Illuminate\Support\Facades\Route;

// Information Session Routes
Route::middleware('auth:api')->group(function () {
    Route::apiResource('info-sessions', InfoSessionController::class);
});
