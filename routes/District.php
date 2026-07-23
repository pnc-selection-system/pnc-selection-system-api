<?php

use App\Http\Controllers\Api\District\DistrictController;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt.auth')->group(function () {
    Route::apiResource('districts', DistrictController::class);
});
