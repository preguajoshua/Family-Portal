<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Front\AuthController;
use App\Http\Controllers\Front\NoteController;
use App\Http\Controllers\Front\ClientController;
use App\Http\Controllers\Front\BillingController;
use App\Http\Controllers\Front\ContactController;
use App\Http\Controllers\Front\InvoiceController;
use App\Http\Controllers\Front\CalendarController;
use App\Http\Controllers\Front\PhysicianController;
use App\Http\Controllers\Front\MedicationController;

/*
|--------------------------------------------------------------------------
| Front Routes
|--------------------------------------------------------------------------
|
*/

Route::middleware('auth')->group(function () {

    Route::post('session/client', [AuthController::class, 'client']);
    Route::resource('session', AuthController::class);

    Route::resource('clients', ClientController::class);

    Route::get('calendar/events', [CalendarController::class, 'events']);
    Route::get('calender/event/{taskId}/pdf', [CalendarController::class, 'downloadDocument'])->middleware('documentationAcess');
    Route::resource('calendar', CalendarController::class);

    Route::resource('notes', NoteController::class);

    Route::resource('medications', MedicationController::class);

    Route::middleware('payor')->group(function () {
        Route::post('invoices/process_mass_payment', [InvoiceController::class, 'processMassPayment']);
        Route::post('invoices/{id}/process', [InvoiceController::class, 'processPayment']);
        Route::get('invoices/{id}/payments', [InvoiceController::class, 'payments']);
        Route::get('invoices/{id}/pdf', [InvoiceController::class, 'download']);
        Route::resource('invoices', InvoiceController::class);

        Route::get('payments/token', [BillingController::class, 'clientToken']);
        Route::post('billing/customers/{id}', [BillingController::class, 'updateCustomer']);
        Route::get('billing/sources', [BillingController::class, 'sources']);
        Route::get('billing/status', [BillingController::class, 'paymentGatewayStatus']);
        Route::resource('billing', BillingController::class);
    });

    Route::resource('physicians', PhysicianController::class);

    Route::resource('contacts', ContactController::class);

});
