<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Facades\Identity;
use Illuminate\Support\Facades\Http;

/**
 * @group development
 */
class HomecareApiTest extends TestCase
{
    /* User Permissions */

    /** @test  */
    public function a_users_permissions_can_be_retrieved()
    {
        $token = Identity::rawToken();
        $baseUrl = config('axxess.homecare_api.base_url');
        $agencyId = '138ec21c-e8be-449d-8c35-18dc66b30929';
        $userId = '2f617af2-71a5-4974-bb91-4549942bb3e2';
        $patientId = '00079603-b265-46d8-b88b-89e3007bce09';

        $response = Http::withToken($token)->get("{$baseUrl}/agencies/{$agencyId}/users/{$userId}/settings/{$patientId}");

        $this->assertEquals(200, $response->status(), 'Response from Home Care API was unsuccessful');
        $this->assertTrue(property_exists($response->object(), 'familyPortalUserPermission'));
        $this->assertEquals('138ec21c-e8be-449d-8c35-18dc66b30929', $response->object()->familyPortalUserPermission->agencyId);
        $this->assertEquals('2f617af2-71a5-4974-bb91-4549942bb3e2', $response->object()->familyPortalUserPermission->familyPortalUserId);
        $this->assertEquals('00079603-b265-46d8-b88b-89e3007bce09', $response->object()->familyPortalUserPermission->patientId);
    }

    /* Create Payment Profile */

    /** @test  */
    public function the_payment_profile_can_be_created_for_a_user()
    {
        $token = Identity::rawToken();
        $baseUrl = config('axxess.homecare_api.base_url');

        $response = Http::withToken($token)->post("{$baseUrl}/patientCustomerAccounts", [
            'patientId' => '000a14b8-78ce-4f41-99cb-3be6d1157c70',
            'agencyId' => '6e27a5e1-e555-4ee0-84b8-ab21332e9757',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john@example.com',
            'userId' => '00000000-0000-0000-0000-000000000000',
            'userFullName' => 'Ann Onymous',
        ]);

        $this->assertEquals(201, $response->status(), 'Response from Home Care API was unsuccessful');
        $this->assertTrue($response->object()->isSuccessful);
        $this->assertTrue(property_exists($response->object(), 'customerAccountId'));
    }

    /** @test  */
    public function the_payment_profile_can_not_be_created_for_a_user_if_it_already_exists()
    {
        $token = Identity::rawToken();
        $baseUrl = config('axxess.homecare_api.base_url');

        $response = Http::withToken($token)->post("{$baseUrl}/patientCustomerAccounts", [
            'patientId' => '00079603-b265-46d8-b88b-89e3007bce09',
            'agencyId' => '138ec21c-e8be-449d-8c35-18dc66b30929',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john@example.com',
            'userId' => '00000000-0000-0000-0000-000000000000',
            'userFullName' => 'Ann Onymous',
        ]);

        $this->assertEquals(400, $response->status());
    }

    /* Retrieve Payment Profile */

    /** @test  */
    public function the_users_account_can_be_retrieved()
    {
        $token = Identity::rawToken();
        $baseUrl = config('axxess.homecare_api.base_url');
        $patientId = '0066ad95-9a13-11e5-965d-52540089632e';

        $response = Http::withToken($token)->get("{$baseUrl}/patientCustomerAccounts/{$patientId}");

        $this->assertEquals(200, $response->status(), 'Response from Home Care API was unsuccessful');
        $this->assertTrue(property_exists($response->object(), 'accountDetails'));
        $this->assertEquals('00f01923-aa7a-4448-af74-3cd41bf4dad6', $response->object()->accountDetails->patientId);
    }

    /** @test  */
    public function the_users_with_no_account_returns_a_404()
    {
        $token = Identity::rawToken();
        $baseUrl = config('axxess.homecare_api.base_url');
        $patientId = '4b2bbb1d-9d03-11e5-965d-52540089632e';

        $response = Http::withToken($token)->get("{$baseUrl}/patientCustomerAccounts/{$patientId}");

        $this->assertEquals(404, $response->status());
    }

    /* Retrieve Payment Token */

    /** @test  */
    public function a_users_token_can_be_retrieved()
    {
        $token = Identity::rawToken();
        $baseUrl = config('axxess.homecare_api.base_url');
        $patientId = '00079603-b265-46d8-b88b-89e3007bce09';

        $response = Http::withToken($token)->get("{$baseUrl}/patientCustomerAccounts/{$patientId}/token");

        $this->assertEquals(200, $response->status());
        $this->assertTrue(property_exists($response->object(), 'clientToken'));
    }

    /** @test  */
    public function a_token_can_not_be_retrieved_for_users_with_no_account_and_returns_a_404()
    {
        $token = Identity::rawToken();
        $baseUrl = config('axxess.homecare_api.base_url');
        $patientId = '4b2bbb1d-9d03-11e5-965d-52540089632e';

        $response = Http::withToken($token)->get("{$baseUrl}/patientCustomerAccounts/{$patientId}/token");

        $this->assertEquals(404, $response->status());
    }

    /* Set Default Credit Card */

    /** @test  */
    public function a_users_credit_card_can_be_set_as_default()
    {
        $token = Identity::rawToken();
        $baseUrl = config('axxess.homecare_api.base_url');
        $patientId = '00f01923-aa7a-4448-af74-3cd41bf4dad6';
        $cardId = '56245abf-578e-4843-baac-7451306e8c79';

        $response = Http::withToken($token)->put("{$baseUrl}/patients/{$patientId}/patientCreditCards/{$cardId}/default");

        $this->assertEquals(204, $response->status());
    }

    /** @test  */
    public function a_bad_credit_card_can_not_be_set_as_default_and_returns_a_400()
    {
        $token = Identity::rawToken();
        $baseUrl = config('axxess.homecare_api.base_url');
        $patientId = '00f01923-aa7a-4448-af74-3cd41bf4dad6';
        $cardId = '00000000-0000-0000-0000-000000000000';

        $response = Http::withToken($token)->put("{$baseUrl}/patients/{$patientId}/patientCreditCards/{$cardId}/default");

        $this->assertEquals(400, $response->status());
    }

    /** @test  */
    public function a_credit_card_that_does_not_belong_to_the_user_can_not_be_set_as_default_and_returns_a_404()
    {
        $token = Identity::rawToken();
        $baseUrl = config('axxess.homecare_api.base_url');
        $patientId = '4b2bbb1d-9d03-11e5-965d-52540089632e';
        $cardId = '56245abf-578e-4843-baac-7451306e8c79';

        $response = Http::withToken($token)->put("{$baseUrl}/patients/{$patientId}/patientCreditCards/{$cardId}/default");

        $this->assertEquals(404, $response->status());
    }

    /* Add Credit Card */

    /** @test  */
    public function a_credit_card_can_be_added_to_the_users_account()
    {
        $token = Identity::rawToken();
        $baseUrl = config('axxess.homecare_api.base_url');

        $response = Http::withToken($token)->post("{$baseUrl}/patientCreditCards", [
            'patientId' => '00f01923-aa7a-4448-af74-3cd41bf4dad6',
            'agencyId' => '122ec20a-705b-4974-9437-8f6233a6e953',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '5551231234',
            'billingAddress' => [
                'line1' => '101 Main Street',
                'locality' => 'Dallas',
                'administrativeArea' => 'TX',
                'postalCode' => '75000',
                'country' => 229,
            ],
            'paymentMethodNonce' => 'fake-valid-nonce',
            'userId' => '00000000-0000-0000-0000-000000000000',
            'userFullName' => 'Ann Onymous',
        ]);

        $this->assertEquals(201, $response->status(), 'Response from Home Care API was unsuccessful');
        $this->assertTrue($response->object()->isSuccessful);
        $this->assertTrue(property_exists($response->object(), 'cardId'));
    }

    /** @test  */
    public function a_credit_card_can_not_be_added_to_the_users_with_no_payment_profile()
    {
        $token = Identity::rawToken();
        $baseUrl = config('axxess.homecare_api.base_url');

        $response = Http::withToken($token)->post("{$baseUrl}/patientCreditCards", [
            'patientId' => '4b2bbb1d-9d03-11e5-965d-52540089632e',
            'agencyId' => '122ec20a-705b-4974-9437-8f6233a6e953',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '5551231234',
            'billingAddress' => [
                'line1' => '101 Main Street',
                'locality' => 'Dallas',
                'administrativeArea' => 'TX',
                'postalCode' => '75000',
                'country' => 229,
            ],
            'paymentMethodNonce' => 'fake-valid-nonce',
            'userId' => '00000000-0000-0000-0000-000000000000',
            'userFullName' => 'Ann Onymous',
        ]);

        $this->assertEquals(404, $response->status(), 'Response from Home Care API was unsuccessful');
    }

    /* Remove Credit Card */

    /** @test  */
    public function a_credit_card_can_be_removed_from_the_users_account()
    {
        $token = Identity::rawToken();
        $baseUrl = config('axxess.homecare_api.base_url');
        $cardId = '843a56ea-e86d-4265-90c0-c50006c22f7c';

        $response = Http::withToken($token)->delete("{$baseUrl}/patientcreditcards/{$cardId}", [
            'userId' => '00000000-0000-0000-0000-000000000000',
            'userFullName' => 'Ann Onymous',
        ]);

        $this->assertEquals(204, $response->status());
    }

    /** @test  */
    public function an_error_is_returned_when_trying_to_remove_a_credit_card_that_does_not_exist()
    {
        $token = Identity::rawToken();
        $baseUrl = config('axxess.homecare_api.base_url');
        $cardId = '56245abf-578e-4843-baac-7451306e8c79';

        $response = Http::withToken($token)->delete("{$baseUrl}/patientcreditcards/{$cardId}", [
            'userId' => '00000000-0000-0000-0000-000000000000',
            'userFullName' => 'Ann Onymous',
        ]);

        $this->assertEquals(500, $response->status());
    }

    /* List Credit Cards */

    /** @test  */
    public function a_users_credit_cards_can_be_retrieved()
    {
        $token = Identity::rawToken();
        $baseUrl = config('axxess.homecare_api.base_url');
        $patientId = '00f01923-aa7a-4448-af74-3cd41bf4dad6';

        $response = Http::withToken($token)->get("{$baseUrl}/patients/{$patientId}/patientCreditCards");

        $this->assertEquals(200, $response->status());
        $this->assertIsArray($response->object()->patientCreditCards);
    }

    /** @test  */
    public function a_users_with_no_credit_cards_results_in_a_404()
    {
        $token = Identity::rawToken();
        $baseUrl = config('axxess.homecare_api.base_url');
        $patientId = '0026ac03-8711-49fb-ae60-9d79a59af437';

        $response = Http::withToken($token)->get("{$baseUrl}/patients/{$patientId}/patientCreditCards");

        $this->assertEquals(404, $response->status());
    }

    /* Retrieve Credit Card */

    /** @test  */
    public function a_users_specific_credit_card_can_be_retrieved()
    {
        $token = Identity::rawToken();
        $baseUrl = config('axxess.homecare_api.base_url');
        $cardId = '086c6d56-eadf-4fd2-845f-57d3bc200a5d';

        $response = Http::withToken($token)->get("{$baseUrl}/patientcreditcards/{$cardId}");

        $this->assertEquals(200, $response->status(), 'Response from Home Care API was unsuccessful');
        $this->assertTrue(property_exists($response->object(), 'cardDetails'));
        $this->assertEquals('086c6d56-eadf-4fd2-845f-57d3bc200a5d', $response->object()->cardDetails->id);
    }

    /** @test  */
    public function trying_to_retrieve_a_credit_card_with_an_invalid_id_yeilds_a_404()
    {
        $token = Identity::rawToken();
        $baseUrl = config('axxess.homecare_api.base_url');
        $cardId = '0026ac03-8711-49fb-ae60-9d79a59af437';

        $response = Http::withToken($token)->get("{$baseUrl}/patientcreditcards/{$cardId}");

        $this->assertEquals(404, $response->status(), 'Response from Home Care API was unsuccessful');
    }

    /* Transactions */

    /** @test  */
    public function a_users_card_can_be_charged()
    {
        $token = Identity::rawToken();
        $baseUrl = config('axxess.homecare_api.base_url');

        $response = Http::withToken($token)->post("{$baseUrl}/patientInvoiceTransactions", [
            'patientId' => '77a6ad5d-ca1c-4f21-a129-870b663d3671',
            'agencyId' => '122ec20a-705b-4974-9437-8f6233a6e953',
            'agencyLocationId' => 'bf0df4e3-5536-406e-b82b-36c793a5fc4a',
            'invoiceId' => 'd7718029-79d8-424b-8688-aa2a953215d2',
            'amount' => '150.00',
            'serviceFee' => '4.65',
            'creditCardId' => '5817ef4d-a6a5-4592-9281-e65b287a63df',
            'userId' => '00000000-0000-0000-0000-000000000000',
            'userFullName' => 'Ann Onymous',
        ]);

        $this->assertEquals(201, $response->status(), 'Response from Home Care API was unsuccessful');
        $this->assertTrue($response->object()->isSuccessful);
        $this->assertTrue(property_exists($response->object(), 'transactionRefId'));
        $this->assertTrue(property_exists($response->object(), 'confirmationId'));
    }

    /** @test  */
    public function a_users_card_can_not_be_charged_if_the_service_fee_is_not_2_percent()
    {
        $token = Identity::rawToken();
        $baseUrl = config('axxess.homecare_api.base_url');

        $response = Http::withToken($token)->post("{$baseUrl}/patientInvoiceTransactions", [
            'patientId' => '77a6ad5d-ca1c-4f21-a129-870b663d3671',
            'agencyId' => '122ec20a-705b-4974-9437-8f6233a6e953',
            'agencyLocationId' => 'bf0df4e3-5536-406e-b82b-36c793a5fc4a',
            'invoiceId' => 'd7718029-79d8-424b-8688-aa2a953215d2',
            'amount' => '150.00',
            'serviceFee' => '1.00',
            'creditCardId' => '5817ef4d-a6a5-4592-9281-e65b287a63df',
            'userId' => '00000000-0000-0000-0000-000000000000',
            'userFullName' => 'Ann Onymous',
        ]);

        $this->assertEquals(400, $response->status(), 'Service fee is not equal to 2.9% of the amount + $0.30');
    }

    /** @test  */
    public function a_different_users_card_can_not_be_charged()
    {
        $token = Identity::rawToken();
        $baseUrl = config('axxess.homecare_api.base_url');

        $response = Http::withToken($token)->post("{$baseUrl}/patientInvoiceTransactions", [
            'patientId' => '77a6ad5d-ca1c-4f21-a129-870b663d3671',
            'agencyId' => '122ec20a-705b-4974-9437-8f6233a6e953',
            'agencyLocationId' => 'bf0df4e3-5536-406e-b82b-36c793a5fc4a',
            'invoiceId' => 'd7718029-79d8-424b-8688-aa2a953215d2',
            'amount' => '150.00',
            'serviceFee' => '4.65',
            'creditCardId' => '086c6d56-eadf-4fd2-845f-57d3bc200a5d',
            'userId' => '00000000-0000-0000-0000-000000000000',
            'userFullName' => 'Ann Onymous',
        ]);

        $this->assertEquals(404, $response->status(), 'Response from Home Care API was unsuccessful');
    }
}
