<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

require __DIR__.'/Auth.php';
require __DIR__.'/Exam.php';
require __DIR__.'/AssessmentForm.php';
require __DIR__.'/NgoPartner.php';
require __DIR__.'/SelectCampaing.php';
require __DIR__.'/Candidate.php';
require __DIR__.'/Province.php';
