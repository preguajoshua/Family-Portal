<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Single Sign-on URL
    |--------------------------------------------------------------------------
    |
    */

    'sso' => env('APP_SSO'),

    /*
    |--------------------------------------------------------------------------
    | Axxess Identity
    |--------------------------------------------------------------------------
    |
    */

    'identity' => [
        'url' => env('IDENTITY_URL'),
        'client_id' => env('IDENTITY_CLIENT_ID'),
        'client_secret' => env('IDENTITY_CLIENT_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Encryption Keys
    |--------------------------------------------------------------------------
    |
    */

    'cipher' => [
        'key' => env('CIPHER_KEY'),
        'iv' => env('CIPHER_IV'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Assets API
    |--------------------------------------------------------------------------
    |
    */

    'assets' => [
        'url' => env('ASSETS_URL'),
        'key' => env('ASSETS_KEY'),
        'secret' => env('ASSETS_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | PDF Service API
    |--------------------------------------------------------------------------
    |
    */

    'pdf_service_api' => env('PDF_SERVICE_API'),

    /*
    |--------------------------------------------------------------------------
    | Payment Gateway
    |--------------------------------------------------------------------------
    |
    */

    'payment_gateway' => env('PAYMENT_GATEWAY'),

    /*
    |--------------------------------------------------------------------------
    | Billing Service API
    |--------------------------------------------------------------------------
    |
    */

    'billing_service' => [
        'base_url' => env('BILLING_SERVICE_BASE'),
        'payment_type' => env('BILLING_SERVICE_PAYMENT_TYPE'),
        'private_payor' => env('BILLING_SERVICE_PAYMENT_PAYOR'),
    ],

    /*
    |--------------------------------------------------------------------------
    | AgencyCore API
    |--------------------------------------------------------------------------
    |
    */

    'agencycore_api' => [
        'base_url' => env('AGENCYCORE_API_BASE'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Home Care API
    |--------------------------------------------------------------------------
    |
    */

    'homecare_api' => [
        'base_url' => env('HOMECARE_API_BASE'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Hospice API
    |--------------------------------------------------------------------------
    |
    */

    'hospice_api' => [
        'base_url' => env('HOSPICE_API_BASE'),
    ],
];