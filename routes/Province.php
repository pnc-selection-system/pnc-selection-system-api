<?php

use App\Http\Controllers\Api\Province\ProvinceController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::apiResource('provinces', ProvinceController::class);
});
