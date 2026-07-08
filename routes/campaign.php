<?php

use App\Http\Controllers\Api\Campaign\CampaignController;
use Illuminate\Support\Facades\Route;

Route::apiResource('campaigns', CampaignController::class);
