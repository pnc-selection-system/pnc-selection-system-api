<?php

use App\Http\Controllers\Api\SelectCampaing\SelectCampaingController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::get('/selection-campaigns',                    [SelectCampaingController::class, 'index'])  ->middleware('permission:campaigns.view');
    Route::post('/selection-campaigns',                   [SelectCampaingController::class, 'store'])  ->middleware('permission:campaigns.create');
    Route::get('/selection-campaigns/{selectCampaing}',   [SelectCampaingController::class, 'show'])   ->middleware('permission:campaigns.view');
    Route::put('/selection-campaigns/{selectCampaing}',   [SelectCampaingController::class, 'update']) ->middleware('permission:campaigns.edit');
    Route::patch('/selection-campaigns/{selectCampaing}',[SelectCampaingController::class, 'update'])->middleware('permission:campaigns.edit');
    Route::delete('/selection-campaigns/{selectCampaing}',[SelectCampaingController::class, 'destroy'])->middleware('permission:campaigns.delete');
});
