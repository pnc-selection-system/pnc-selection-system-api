<?php

use App\Http\Controllers\Api\Location\ProvinceController;
use App\Http\Controllers\Api\Location\DistrictController;
use App\Http\Controllers\Api\Location\CommuneController;
use App\Http\Controllers\Api\Location\VillageController;
use App\Http\Controllers\Api\Location\SchoolController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::apiResource('provinces', ProvinceController::class);
    Route::apiResource('districts', DistrictController::class);
    Route::apiResource('communes', CommuneController::class);
    Route::apiResource('villages', VillageController::class);
    Route::apiResource('schools', SchoolController::class);
});