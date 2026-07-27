<?php

use App\Http\Controllers\Api\NgoPartner\ContactPersonController;
use App\Http\Controllers\Api\NgoPartner\NgoPartnerController;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt.auth')->group(function () {
    // NGO CRUD with permission enforcement
    Route::get(    '/ngo-partners',                          [NgoPartnerController::class, 'index'])     ->middleware('permission:ngos.view');
    Route::post(   '/ngo-partners',                          [NgoPartnerController::class, 'store'])    ->middleware('permission:ngos.create');
    Route::get(    '/ngo-partners/{ngo_partner}',            [NgoPartnerController::class, 'show'])     ->middleware('permission:ngos.view');
    Route::put(    '/ngo-partners/{ngo_partner}',            [NgoPartnerController::class, 'update'])   ->middleware('permission:ngos.edit');
    Route::patch(  '/ngo-partners/{ngo_partner}',            [NgoPartnerController::class, 'update'])   ->middleware('permission:ngos.edit');
    Route::delete( '/ngo-partners/{ngo_partner}',            [NgoPartnerController::class, 'destroy'])  ->middleware('permission:ngos.delete');

    // Contact persons sub-resource
    Route::get(    '/ngo-partners/{ngo_partner}/contact-persons',                         [ContactPersonController::class, 'index'])   ->middleware('permission:ngos.view');
    Route::post(   '/ngo-partners/{ngo_partner}/contact-persons',                        [ContactPersonController::class, 'store'])   ->middleware('permission:ngos.create');
    Route::get(    '/ngo-partners/{ngo_partner}/contact-persons/{contact_person}',        [ContactPersonController::class, 'show'])    ->middleware('permission:ngos.view');
    Route::put(    '/ngo-partners/{ngo_partner}/contact-persons/{contact_person}',        [ContactPersonController::class, 'update'])  ->middleware('permission:ngos.edit');
    Route::patch(  '/ngo-partners/{ngo_partner}/contact-persons/{contact_person}',        [ContactPersonController::class, 'update'])  ->middleware('permission:ngos.edit');
    Route::delete( '/ngo-partners/{ngo_partner}/contact-persons/{contact_person}',        [ContactPersonController::class, 'destroy']) ->middleware('permission:ngos.delete');

    // Reporting: list candidates linked to an NGO
    Route::get('ngo-partners/{ngo_partner}/candidates', [NgoPartnerController::class, 'candidates'])  ->middleware('permission:ngos.view');
});
