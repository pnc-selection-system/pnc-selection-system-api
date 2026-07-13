<?php

use App\Http\Controllers\Api\Campaign\CampaignController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::apiResource('campaigns', CampaignController::class);
});
