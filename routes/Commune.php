<?php

use App\Http\Controllers\Api\Commune\CommuneController;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt.auth')->group(function () {
    Route::get('communes', [CommuneController::class, 'index']);
});


