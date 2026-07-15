<?php

use App\Http\Controllers\Api\School\SchoolController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::apiResource('schools', SchoolController::class);
});
