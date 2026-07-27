<?php

use App\Http\Controllers\Api\Role\RoleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'permission:roles.manage'])->group(function () {
    Route::get('/roles',           [RoleController::class, 'index']);
    Route::post('/roles',          [RoleController::class, 'store']);
    Route::get('/roles/{role}',    [RoleController::class, 'show']);
    Route::put('/roles/{role}',    [RoleController::class, 'update']);
    Route::delete('/roles/{role}', [RoleController::class, 'destroy']);
});
