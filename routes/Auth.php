<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\ProfileController;

Route::post('/Auth/login',    [LoginController::class, 'login']);
Route::middleware('auth:api')->group(function () {
    Route::post('/Auth/logout', [LogoutController::class, 'logout']);
    Route::get('/Auth/profile',      [ProfileController::class, 'profile']);
});
