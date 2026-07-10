<?php

use App\Http\Controllers\Api\SelectCampaing\SelectCampaingController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::apiResource('selection-campaigns', SelectCampaingController::class);
});
