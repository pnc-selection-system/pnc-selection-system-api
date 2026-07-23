<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('jwt.auth');

require __DIR__.'/Auth.php';
require __DIR__.'/AssessmentForm.php';
require __DIR__.'/AssessmentResponse.php';
require __DIR__.'/NgoPartner.php';
require __DIR__.'/SelectCampaing.php';
require __DIR__.'/Village.php';
require __DIR__.'/Province.php';
require __DIR__.'/District.php';
require __DIR__.'/Commune.php';
require __DIR__.'/Candidate.php';
require __DIR__.'/infoSession.php';
require __DIR__.'/HomeInvestigation.php';
