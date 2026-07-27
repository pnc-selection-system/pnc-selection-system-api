<?php

use App\Http\Controllers\Api\Candidate\CandidateController;
use App\Http\Controllers\Api\Candidate\ImportCandidateController;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt.auth')->group(function () {
    // Candidate CRUD
    Route::get('candidates/stats', [CandidateController::class, 'stats']);
    Route::post('candidates/{candidate}/photo', [CandidateController::class, 'uploadPhoto']);
    Route::apiResource('candidates', CandidateController::class);

    // Candidate import from CSV/Excel
    Route::prefix('candidates/import')->name('candidates.import.')->group(function () {
        Route::post('upload', [ImportCandidateController::class, 'upload'])->name('upload');
        Route::post('confirm', [ImportCandidateController::class, 'confirm'])->name('confirm');
    });
});
