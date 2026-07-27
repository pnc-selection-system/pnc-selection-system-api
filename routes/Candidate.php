<?php

use App\Http\Controllers\Api\Candidate\CandidateController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::get('/candidates',              [CandidateController::class, 'index'])  ->middleware('permission:candidates.view');
    Route::post('/candidates',             [CandidateController::class, 'store'])  ->middleware('permission:candidates.create');
    Route::get('/candidates/{candidate}',  [CandidateController::class, 'show'])   ->middleware('permission:candidates.view');
    Route::put('/candidates/{candidate}',  [CandidateController::class, 'update']) ->middleware('permission:candidates.edit');
    Route::patch('/candidates/{candidate}', [CandidateController::class, 'update'])->middleware('permission:candidates.edit');
    Route::delete('/candidates/{candidate}', [CandidateController::class, 'destroy'])->middleware('permission:candidates.delete');
});
