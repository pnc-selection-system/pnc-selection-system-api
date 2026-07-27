<?php

use App\Http\Controllers\Api\Village\VillageController;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt.auth')->group(function () {
    Route::apiResource('villages', VillageController::class);
});
