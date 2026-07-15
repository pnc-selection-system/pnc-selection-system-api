<?php

use App\Http\Controllers\Api\Commune\CommuneController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::get('communes', [CommuneController::class, 'index']);
});
