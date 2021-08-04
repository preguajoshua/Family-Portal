<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
|
*/

Route::redirect('login', config('axxess.sso'))->name('login');

Route::get('SingleSignOn', [AuthenticatedSessionController::class, 'store']);
Route::post('auth/logout', [AuthenticatedSessionController::class, 'destroy']);
