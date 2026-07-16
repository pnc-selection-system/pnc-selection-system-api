<?php

use App\Http\Controllers\Api\AssessmentForm\AssessmentFormController;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt.auth')->group(function () {
    Route::apiResource('assessment-forms', AssessmentFormController::class);
});
