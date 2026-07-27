<?php

use App\Http\Controllers\Api\UserRole\UserRoleController;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt.auth')->prefix('setup/users-roles')->group(function () {
    Route::get('/users',            [UserRoleController::class, 'index']);
    Route::get('/users/all',        [UserRoleController::class, 'allUsers']);
    Route::post('/users',           [UserRoleController::class, 'store'])           ->middleware('permission:users.create');
    Route::post('/users/{id}/deactivate', [UserRoleController::class, 'deactivate'])->middleware('permission:users.deactivate');
    Route::post('/users/{id}/activate',   [UserRoleController::class, 'activate'])  ->middleware('permission:users.deactivate');
    Route::get('/roles',            [UserRoleController::class, 'roles']);
    Route::get('/permission-matrix', [UserRoleController::class, 'permissionMatrix']);
});
