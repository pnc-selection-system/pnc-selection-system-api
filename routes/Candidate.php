<?php

use App\Http\Controllers\Api\Candidate\CandidateController;
use Illuminate\Support\Facades\Route;

// Public routes - no authentication required
Route::get('candidates/search', [CandidateController::class, 'search'])->name('candidates.search');
Route::get('candidates/{id}', [CandidateController::class, 'show'])->name('candidates.show')->where('id', '[0-9]+');
