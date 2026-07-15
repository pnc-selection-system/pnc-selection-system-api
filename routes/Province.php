<?php

use App\Http\Controllers\Api\Province\ProvinceController;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt.auth')->group(function () {
    Route::apiResource('provinces', ProvinceController::class);
});
