<?php

use App\Http\Controllers\Api\Village\VillageController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::get('villages', [VillageController::class, 'index']);
});
