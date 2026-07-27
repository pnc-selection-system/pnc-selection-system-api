<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\ProfileController;
use App\Http\Controllers\Api\Auth\RefreshController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [LoginController::class, 'login']);
Route::post('/auth/refresh', [RefreshController::class, 'refresh']);
Route::middleware('jwt.auth')->group(function () {
    Route::post('/auth/logout', [LogoutController::class, 'logout']);
    Route::get('/auth/profile', [ProfileController::class, 'profile']);
});
