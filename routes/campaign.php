<?php

use App\Http\Controllers\Campaign\CampaignController;
use Illuminate\Support\Facades\Route;

Route::apiResource('campaigns', CampaignController::class);