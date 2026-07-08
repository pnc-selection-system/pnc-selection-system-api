<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\ProfileController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [LoginController::class, 'login']);
Route::middleware('auth:api')->group(function () {
    Route::post('/auth/logout', [LogoutController::class, 'logout']);
    Route::get('/auth/profile', [ProfileController::class, 'profile']);
});
