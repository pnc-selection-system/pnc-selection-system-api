<?php

use App\Http\Controllers\Api\District\DistrictController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::get('districts', [DistrictController::class, 'index']);
});
