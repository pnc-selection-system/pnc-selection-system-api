<?php

use App\Http\Controllers\Api\PncOfficer\PncOfficerController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::apiResource('pnc-officers', PncOfficerController::class)
        ->only(['show']);
});