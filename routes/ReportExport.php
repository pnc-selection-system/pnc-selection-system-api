<?php

use App\Http\Controllers\Api\ReportExport\ReportExportController;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt.auth')->group(function () {
    Route::get('/report-exports',           [ReportExportController::class, 'index'])    ->middleware('permission:reports.view');
    Route::post('/report-exports',          [ReportExportController::class, 'store'])    ->middleware('permission:reports.export');
    Route::get('/report-exports/{reportExport}', [ReportExportController::class, 'show'])   ->middleware('permission:reports.view');
    Route::delete('/report-exports/{reportExport}', [ReportExportController::class, 'destroy'])->middleware('permission:reports.export');
    Route::get('/report-exports/{reportExport}/download', [ReportExportController::class, 'download'])->middleware('permission:reports.export');
});
