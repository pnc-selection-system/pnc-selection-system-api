<?php

use App\Http\Controllers\Api\HomeInvestigation\HomeInvestigationController;
use Illuminate\Support\Facades\Route;

// ── Public endpoints (no auth required) ─────────────────────────────────
Route::prefix('home-investigation')->group(function () {

    // Page Metadata
    Route::get('meta', [HomeInvestigationController::class, 'meta']);

    // Selection Lists
    Route::get('campaigns', [HomeInvestigationController::class, 'campaigns']);
    Route::get('investigators', [HomeInvestigationController::class, 'investigators']);
    Route::get('statuses', [HomeInvestigationController::class, 'statuses']);
});

// ── Protected endpoints (require JWT auth) ─────────────────────────────
Route::middleware('jwt.auth')->prefix('home-investigation')->group(function () {

    // Candidates List (paginated, filterable)
    Route::get('candidates', [HomeInvestigationController::class, 'candidates']);

    // Single Candidate Form / Draft / Submit
    Route::get('candidates/{candidateId}', [HomeInvestigationController::class, 'showByCandidate']);
    Route::put('candidates/{candidateId}/draft', [HomeInvestigationController::class, 'saveDraft']);
    Route::put('candidates/{candidateId}/submit', [HomeInvestigationController::class, 'submit']);

    // Attachments
    Route::get('candidates/{candidateId}/attachments', [HomeInvestigationController::class, 'listAttachments']);
    Route::post('candidates/{candidateId}/attachments', [HomeInvestigationController::class, 'uploadAttachment']);
    Route::delete('candidates/{candidateId}/attachments/{attachmentId}', [HomeInvestigationController::class, 'deleteAttachment']);

    // =========================================================================
    // 3.8: Legacy 5-Status System Endpoints
    // =========================================================================

    // CRUD (uses apiResource for standard operations)
    Route::apiResource('investigations', HomeInvestigationController::class)
        ->only(['index', 'store', 'show', 'update', 'destroy']);

    // Status transitions
    Route::put('investigations/{id}/draft', [HomeInvestigationController::class, 'saveDraftLegacy']);
    Route::put('investigations/{id}/submit', [HomeInvestigationController::class, 'submitLegacy']);
    Route::put('investigations/{id}/approve', [HomeInvestigationController::class, 'approveInvestigation']);
    Route::put('investigations/{id}/reject', [HomeInvestigationController::class, 'rejectInvestigation']);

    // History
    Route::get('investigations/{id}/history', [HomeInvestigationController::class, 'getInvestigationHistory']);

    // Dashboard
    Route::get('dashboard/stats', [HomeInvestigationController::class, 'dashboardStats']);
    Route::get('dashboard/workload', [HomeInvestigationController::class, 'dashboardWorkload']);
    Route::get('dashboard/chart', [HomeInvestigationController::class, 'dashboardChart']);
});
