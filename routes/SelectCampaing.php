<?php

use App\Http\Controllers\Api\SelectCampaing\SelectCampaingController;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt.auth')->group(function () {
    Route::apiResource('selection-campaigns', SelectCampaingController::class)
        ->parameters(['selection-campaigns' => 'selectCampaing']);
});
