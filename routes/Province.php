<?php

use App\Http\Controllers\Api\Province\ProvinceController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
<<<<<<< HEAD
    Route::apiResource('provinces', ProvinceController::class);
=======
    Route::get('provinces', [ProvinceController::class, 'index']);
>>>>>>> origin/feat-address
});
