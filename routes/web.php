<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AssetController;


// Authentication Routes
require __DIR__.'/auth.php';


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
*/

Route::middleware('auth')->group(function () {
    Route::get('/', HomeController::class)->name('home');

    Route::get('pdf/calendar', [PdfController::class, 'calendar']);
    Route::get('pdf/medications', [PdfController::class, 'medications']);

    Route::get('assets/{assetId}/{agencyId}', [AssetController::class, 'get']);

    Route::get('/{catchall?}', HomeController::class);
});
