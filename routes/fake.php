<?php

use App\Fakes\AxxessSsoFake;
use App\Fakes\HospiceApiFake;
use App\Fakes\HomeCareApiFake;
use App\Fakes\AgencyCoreApiFake;
use App\Fakes\BillingServiceFake;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Fake API Routes
|--------------------------------------------------------------------------
|
*/

Route::prefix('fake')->group(function () {

    // Axxess Single Sign-On
    Route::prefix('axxess-sso')->group(function () {
        Route::get('/', [AxxessSsoFake::class, 'index']);
        Route::get('login/{user}', [AxxessSsoFake::class, 'login'])->name('fake-sso-login');
    });

    // Home Health API
    Route::prefix('home-health')->group(function () {
        Route::get('clients/{loginId}', [AgencyCoreApiFake::class, 'clients']);
        Route::get('location/{locationId}', [AgencyCoreApiFake::class, 'location']);
        Route::get('patient-contacts/{patientId}', [AgencyCoreApiFake::class, 'patientContacts']);
        Route::get('patient-medications/{patientId}', [AgencyCoreApiFake::class, 'patientMedications']);
        Route::get('patient-physicians/{patientId}', [AgencyCoreApiFake::class, 'patientPhysicians']);
        Route::get('patient-tasks/{patientId}', [AgencyCoreApiFake::class, 'patientTasks']);
    });

    // Home Care API
    Route::prefix('home-care')->group(function () {
        Route::get('patients-by-contact-ids', [HomeCareApiFake::class, 'patients']);
        Route::get('agencylocation', [HomeCareApiFake::class, 'location']);
        Route::get('patientTasksByDate', [HomeCareApiFake::class, 'patientTasks']);
        Route::get('patientMedications', [HomeCareApiFake::class, 'patientMedications']);
        Route::get('patientContacts', [HomeCareApiFake::class, 'patientContacts']);
        Route::get('patientPhysicians', [HomeCareApiFake::class, 'patientPhysicians']);

        Route::get('agencies/{agencyId}/users/{patientContactId}/settings/{patientId}', [HomeCareApiFake::class, 'permissions']);

        Route::post('patientCustomerAccounts', [HomeCareApiFake::class, 'createPaymentProfile']);
        Route::get('patientCustomerAccounts/{patientId}', [HomeCareApiFake::class, 'getPaymentProfile']);
        Route::get('patientCustomerAccounts/{patientId}/token', [HomeCareApiFake::class, 'getPaymentToken']);

        Route::get('patients/{patientId}/patientCreditCards', [HomeCareApiFake::class, 'getPatientCreditCards']);
        Route::post('patientCreditCards', [HomeCareApiFake::class, 'addPaymentCard']);
        Route::get('patientcreditcards/{cardId}', [HomeCareApiFake::class, 'getPaymentCard']);
        Route::put('patients/{patientId}/patientCreditCards/{cardId}/default', [HomeCareApiFake::class, 'setDefaultPaymentCard']);
        Route::put('patientcreditcards/{cardId}', [HomeCareApiFake::class, 'updatePaymentCard']);
        Route::delete('patientcreditcards/{cardId}', [HomeCareApiFake::class, 'removePaymentCard']);

        Route::post('patientInvoiceTransactions', [HomeCareApiFake::class, 'charge']);
        Route::get('patients/{patientId}/invoiceTransactions/{invoiceId}', [HomeCareApiFake::class, 'getInvoice']);

        Route::get('document/print', [HomeCareApiFake::class, 'documentPrint']);

        // Home Care Billing Service
        Route::prefix('billing-service')->group(function () {
            Route::post('client/invoice/List', [BillingServiceFake::class, 'invoices']);
            Route::post('invoice/Get', [BillingServiceFake::class, 'invoice']);
            Route::post('invoice/Stream', [BillingServiceFake::class, 'downloadInvoice']);

            Route::post('getclaimpayments', [BillingServiceFake::class, 'claimPayments']);
            Route::post('Payment/Add/Electronic', [BillingServiceFake::class, 'addElectronicPayment']);
        });
    });

    // Hospice API
    Route::prefix('hospice')->group(function () {
        Route::get('patients-by-contact-ids', [HospiceApiFake::class, 'patients']);
        Route::get('patient-location', [HospiceApiFake::class, 'location']);
        Route::get('patient-tasks-by-date', [HospiceApiFake::class, 'patientTasks']);
        Route::get('patient-medications', [HospiceApiFake::class, 'patientMedications']);
        Route::get('patient-contacts', [HospiceApiFake::class, 'patientContacts']);
        Route::get('patient-physicians', [HospiceApiFake::class, 'patientPhysicians']);
    });

});
