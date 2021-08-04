<?php

namespace App\Services;

use App\Models\User;
use App\Facades\Identity;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Exceptions\HomeCareApiRequestException;
use App\Exceptions\HomeCareApiNotFoundException;
use App\Exceptions\HomeCareApiServiceFeeException;
use App\Exceptions\HomeCareApiAccountExistsException;

class HomeCareApi
{
    /**
     * Country code for USA.
     *
     * @var  integer
     */
    const COUNTRY_USA = 229;

    /**
     * Get the user permissions.
     *
     * @param   string  $agencyId
     * @param   string  $patientPatientContactId
     * @param   string  $patientId
     * @return  object
     *
     * @throws   \App\Exceptions\HomeCareApiRequestException
     */
    public function permissions($agencyId, $patientContactId, $patientId)
    {
        $token = Identity::rawToken();
        $baseUrl = config('axxess.homecare_api.base_url');

        $response = Http::withToken($token)->get("{$baseUrl}/agencies/{$agencyId}/users/{$patientContactId}/settings/{$patientId}");

        if ($response->status() !== 200) {
            throw new HomeCareApiRequestException('Unsuccessful HomeCare API call.');
        }

        return $response->object()->familyPortalUserPermission;
    }

    /**
     * Create a payment profile.
     *
     * @param   \App\Models\User  $user
     * @param   array  $params
     * @return  string
     *
     * @throws   \App\Exceptions\HomeCareApiAccountExistsException
     * @throws   \App\Exceptions\HomeCareApiRequestException
     */
    public function createPaymentProfile(User $user, $params = [])
    {
        $token = Identity::rawToken();
        $baseUrl = config('axxess.homecare_api.base_url');

        $response = Http::withToken($token)
            ->post("{$baseUrl}/patientCustomerAccounts", [
                'patientId' => $user->client()->Id,
                'agencyId' => $user->client()->AgencyId,
                'firstName' => $params['firstName'] ?? $user->getName(),
                'lastName' => $params['lastName'] ?? '.',
                'email' => $params['email'] ?? $user->getEmail(),
                'userId' => $user->id,
                'userFullName' => $user->getName(),
            ]);

        if ($response->status() === 400) {
            throw new HomeCareApiAccountExistsException($response->body());
        }

        if ($response->status() !== 201 || !$response->object()->isSuccessful) {
            throw new HomeCareApiRequestException('Unsuccessful HomeCare API call.');
        }

        return $response->object()->customerAccountId;
    }

    /**
     * Get payment profile.
     *
     * @param   \App\Models\User  $user
     * @return  array
     *
     * @throws   \App\Exceptions\HomeCareApiNotFoundException
     * @throws   \App\Exceptions\HomeCareApiRequestException
     */
    public function getPaymentProfile(User $user)
    {
        $token = Identity::rawToken();
        $baseUrl = config('axxess.homecare_api.base_url');
        $patientId = $user->client()->Id;

        // 1. Get patient
        $response = Http::withToken($token)
            ->get("{$baseUrl}/patientCustomerAccounts/{$patientId}");

        if ($response->status() === 404) {
            throw new HomeCareApiNotFoundException($response->body());
        }

        if ($response->status() !== 200) {
            throw new HomeCareApiRequestException('Unsuccessful HomeCare API call.');
        }

        $accountDetails = $response->object()->accountDetails;

        // 2. Get patient cards
        $response = Http::withToken($token)
            ->get("{$baseUrl}/patients/{$patientId}/patientCreditCards");

        if ($response->status() === 404) {
            Log::info('No cards found for patient.', ['id' => $patientId]);
        }

        if ($response->status() !== 200) {
            throw new HomeCareApiRequestException('Unsuccessful HomeCare API call.');
        }

        $accountDetails->cardDetails = ($response->object())
            ? $response->object()->patientCreditCards
            : [];

        return $accountDetails;
    }

    /**
     * Get payment token.
     *
     * @param   \App\Models\User  $user
     * @return  string
     *
     * @throws   \App\Exceptions\HomeCareApiNotFoundException
     * @throws   \App\Exceptions\HomeCareApiRequestException
     */
    public function getPaymentToken(User $user)
    {
        $token = Identity::rawToken();
        $baseUrl = config('axxess.homecare_api.base_url');
        $patientId = $user->client()->Id;

        $response = Http::withToken($token)
            ->get("{$baseUrl}/patientCustomerAccounts/{$patientId}/token");

        if ($response->status() === 404) {
            throw new HomeCareApiNotFoundException($response->body());
        }

        if ($response->status() !== 200) {
            throw new HomeCareApiRequestException('Unsuccessful HomeCare API call.');
        }

        return $response->object()->clientToken;
    }

    /**
     * Set the default credit card.
     *
     * @param  \App\Models\User  $user
     * @param  string  $cardId
     * @return  boolean
     *
     * @throws   \App\Exceptions\HomeCareApiNotFoundException
     * @throws   \App\Exceptions\HomeCareApiRequestException
     */
    public function setDefaultPaymentCard(User $user, $cardId)
    {
        $token = Identity::rawToken();
        $baseUrl = config('axxess.homecare_api.base_url');
        $patientId = $user->client()->Id;

        $response = Http::withToken($token)
            ->put("{$baseUrl}/patients/{$patientId}/patientCreditCards/{$cardId}/default");

        if ($response->status() === 400) {
            throw new HomeCareApiNotFoundException($response->body());
        }

        if ($response->status() === 404) {
            throw new HomeCareApiNotFoundException($response->body());
        }

        if ($response->status() !== 204) {
            throw new HomeCareApiRequestException('Unsuccessful HomeCare API call.');
        }

        return true;
    }

    /**
     * Add a payment card.
     *
     * @param   \App\Models\User  $user
     * @param   array  $params
     * @return  object
     *
     * @throws   \App\Exceptions\HomeCareApiNotFoundException
     * @throws   \App\Exceptions\HomeCareApiRequestException
     */
    public function addPaymentCard(User $user, $params = [])
    {
        $token = Identity::rawToken();
        $baseUrl = config('axxess.homecare_api.base_url');

        // 1. Add card
        $response = Http::withToken($token)->post("{$baseUrl}/patientCreditCards", [
            'patientId' => $user->client()->Id,
            'agencyId' => $user->client()->AgencyId,
            'firstName' => $params['card_first_name'],
            'lastName' => $params['card_last_name'],
            'phone' => $params['billing_phone'],
            'billingAddress' => [
                'line1' => $params['billing_address_line_1'],
                'locality' => $params['billing_address_city'],
                'administrativeArea' => $params['billing_address_state'],
                'postalCode' => $params['billing_address_zipcode'],
                'country' => self::COUNTRY_USA,
            ],
            'paymentMethodNonce' => $params['payment_method_token'],
            'userId' => $user->id,
            'userFullName' => $user->getName(),
        ]);

        if ($response->status() === 404) {
            throw new HomeCareApiNotFoundException($response->body());
        }

        if ($response->status() !== 201) {
            throw new HomeCareApiRequestException('Unsuccessful HomeCare API call.');
        }

        $cardId = $response->object()->cardId;

        // 2. Get card
        $response = Http::withToken($token)
            ->get("{$baseUrl}/patientcreditcards/{$cardId}");

        if ($response->status() === 404) {
            throw new HomeCareApiNotFoundException($response->body());
        }

        if ($response->status() !== 200) {
            throw new HomeCareApiRequestException('Unsuccessful HomeCare API call.');
        }

        return $response->object()->cardDetails;
    }

    /**
     * Remove a payment card.
     *
     * @param   \App\Models\User  $user
     * @return  boolean
     *
     * @throws   \App\Exceptions\HomeCareApiNotFoundException
     * @throws   \App\Exceptions\HomeCareApiRequestException
     */
    public function removePaymentCard(User $user, $cardId)
    {
        $token = Identity::rawToken();
        $baseUrl = config('axxess.homecare_api.base_url');

        $response = Http::withToken($token)->delete("{$baseUrl}/patientcreditcards/{$cardId}", [
            'userId' => $user->id,
            'userFullName' => $user->getName(),
        ]);

        if ($response->status() === 500) {
            throw new HomeCareApiNotFoundException($response->body());
        }

        if ($response->status() !== 204) {
            throw new HomeCareApiRequestException('Unsuccessful HomeCare API call.');
        }

        return true;
    }

    /**
     * List payment cards.
     *
     * @param   \App\Models\User  $user
     * @return  object
     *
     * @throws   \App\Exceptions\HomeCareApiNotFoundException
     * @throws   \App\Exceptions\HomeCareApiRequestException
     */
    public function listPaymentCards(User $user)
    {
        $token = Identity::rawToken();
        $baseUrl = config('axxess.homecare_api.base_url');
        $patientId = $user->client()->Id;

        $response = Http::withToken($token)
            ->get("{$baseUrl}/patients/{$patientId}/patientCreditCards");

        if ($response->status() === 404) {
            throw new HomeCareApiNotFoundException($response->body());
        }

        if ($response->status() !== 200) {
            throw new HomeCareApiRequestException('Unsuccessful HomeCare API call.');
        }

        return $response->object()->patientCreditCards;
    }

    /**
     * Charge a users card.
     *
     * @param   \App\Models\User  $user
     * @param   array  $params
     * @return  object
     *
     * @throws   \App\Exceptions\HomeCareApiNotFoundException
     * @throws   \App\Exceptions\HomeCareApiServiceFeeException
     * @throws   \App\Exceptions\HomeCareApiRequestException
     */
    public function charge(User $user, $params = [])
    {
        $token = Identity::rawToken();
        $baseUrl = config('axxess.homecare_api.base_url');

        $response = Http::withToken($token)->post("{$baseUrl}/patientInvoiceTransactions", [
            'patientId' => $user->client()->Id,
            'agencyId' => $user->client()->AgencyId,
            'agencyLocationId' => $user->client()->AgencyLocationId,
            'invoiceId' => $params['invoice_id'],
            'amount' => floatval($params['amount']),
            'serviceFee' => floatval($params['fee']),
            'creditCardId' => $params['source_id'],
            'userId' => $user->id,
            'userFullName' => $user->getName(),
        ]);

        if ($response->status() === 404) {
            throw new HomeCareApiNotFoundException($response->body());
        }

        if ($response->status() === 400) {
            throw new HomeCareApiServiceFeeException($response->body());
        }

        if ($response->status() !== 201) {
            throw new HomeCareApiRequestException('Unsuccessful HomeCare API call.');
        }

        return $response->object();
    }
}
