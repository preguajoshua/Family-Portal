<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserControllerV2;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\PatientControllerV2;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
*/

Route::group([
    'middleware' => 'auth:sanctum',
], function () {

    Route::prefix('v2')->group(function () {
        // Users...
        Route::post('users', [UserControllerV2::class, 'store']);
        // Patients...
        Route::patch('patients', [PatientControllerV2::class, 'update']);
        Route::delete('patients', [PatientControllerV2::class, 'destroy']);
    });

    Route::prefix('v3')->group(function () {
        // Users...
        Route::post('users', [UserController::class, 'store']);
        // Patients...
        Route::patch('users/patient-contact', [PatientController::class, 'update']);
        Route::delete('users/patient-contact', [PatientController::class, 'destroy']);
    });
});


// Fake API Routes
require __DIR__.'/fake.php';
