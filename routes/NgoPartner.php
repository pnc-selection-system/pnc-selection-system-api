<?php

use App\Http\Controllers\Api\NgoPartner\NgoPartnerController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::apiResource('ngo-partners', NgoPartnerController::class);
});
