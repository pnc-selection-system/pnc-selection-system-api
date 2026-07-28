<?php

use App\Http\Controllers\Api\UserRole\UserRoleController;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt.auth')->prefix('setup/users-roles')->group(function () {
    Route::get('/users',            [UserRoleController::class, 'index'])           ->middleware('permission:users.view');
    Route::get('/users/all',        [UserRoleController::class, 'allUsers'])        ->middleware('permission:users.view');
    Route::post('/users',           [UserRoleController::class, 'store'])           ->middleware('permission:users.create');
    Route::post('/users/{id}/deactivate', [UserRoleController::class, 'deactivate'])->middleware('permission:users.deactivate');
    Route::post('/users/{id}/activate',   [UserRoleController::class, 'activate'])  ->middleware('permission:users.deactivate');
    Route::get('/roles',            [UserRoleController::class, 'roles'])           ->middleware('permission:roles.manage');
    Route::get('/permission-matrix',       [UserRoleController::class, 'permissionMatrix'])->middleware('permission:roles.manage');
    Route::post('/permission-matrix/toggle', [UserRoleController::class, 'togglePermission'])->middleware('permission:roles.manage');
});
