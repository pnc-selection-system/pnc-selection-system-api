<?php

use App\Http\Controllers\Api\Candidate\CandidateController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::apiResource('candidates', CandidateController::class);
});
