<?php

use App\Http\Controllers\Api\School\SchoolController;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt.auth')->group(function () {
    Route::apiResource('schools', SchoolController::class);
});
