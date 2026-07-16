<?php

use App\Http\Controllers\Api\HomeInvestigation\HomeInvestigationController;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt.auth')->group(function () {
    Route::apiResource('home-investigations', HomeInvestigationController::class);
    
    // Additional custom routes
    Route::post('home-investigations/{homeInvestigation}/submit', [HomeInvestigationController::class, 'submit']);
    Route::post('home-investigations/{homeInvestigation}/files', [HomeInvestigationController::class, 'uploadFile']);
    Route::delete('home-investigations/{homeInvestigation}/files/{file}', [HomeInvestigationController::class, 'deleteFile']);
});