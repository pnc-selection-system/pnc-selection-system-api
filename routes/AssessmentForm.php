<?php

use App\Http\Controllers\Api\AssessmentForm\AssessmentFormController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::apiResource('assessment-forms', AssessmentFormController::class);
});
