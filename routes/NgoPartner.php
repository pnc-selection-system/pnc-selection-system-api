<?php

use App\Http\Controllers\Api\NgoPartner\ContactPersonController;
use App\Http\Controllers\Api\NgoPartner\NgoPartnerController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::apiResource('ngo-partners', NgoPartnerController::class);

    // Contact persons sub-resource
    Route::apiResource('ngo-partners.contact-persons', ContactPersonController::class)
        ->only(['index', 'store', 'show', 'update', 'destroy']);

    // Reporting: list candidates linked to an NGO
    Route::get('ngo-partners/{ngo_partner}/candidates', [NgoPartnerController::class, 'candidates']);
});
