<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Client;
use Illuminate\Support\Facades\Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

/**
 * @group development
 */
class BillingTest extends TestCase
{
    use WithoutMiddleware;

    /**
     * Run before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $clientWithPayor = Client::factory()->states('payor')->make();

        $this->login($clientWithPayor);
    }

    /** @test  */
    public function a_user_must_be_authenticated_to_access_the_billing_services()
    {
        $this->logout();

        $response = $this->getJson('/spa/billing/status');

        $response->assertStatus(401);
    }

    /** @test  */
    public function a_user_must_be_a_payor_to_access_the_billing_services()
    {
        $this->logout();
        $this->login($clientWithoutPayor =  Client::factory()->make());

        $response = $this->getJson('/spa/billing/status');

        $response->assertStatus(404);
    }

    /** @test  */
    public function the_disabled_state_of_the_payment_gateway_can_be_reported()
    {
        Config::set('axxess.payment_gateway', false);

        $response = $this->getJson('/spa/billing/status');

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => false,
            ]);
    }

    /** @test  */
    public function the_enabled_state_of_the_payment_gateway_can_be_reported()
    {
        Config::set('axxess.payment_gateway', true);

        $response = $this->getJson('/spa/billing/status');

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
            ]);
    }

    /** @test  */
    public function the_users_customer_id_matches_the_customer_id_in_the_response()
    {
        $response = $this->getJson('/spa/billing/sources');

        $response->assertStatus(200);
        $this->assertEquals($this->getUsersCustomer(), $response->getData()->id);
    }

    /** @test  */
    public function a_customers_credit_card_can_be_retrieved_from_the_payment_gateway()
    {
        $this->setUsersCustomer('fake-customer-with-one-source');

        $response = $this->getJson('/spa/billing/sources');

        $response
            ->assertStatus(200)
            ->assertJsonCount(1, 'sources');
    }

    /** @test  */
    public function a_customers_credit_cards_can_be_retrieved_from_the_payment_gateway()
    {
        $this->setUsersCustomer('fake-customer-with-two-sources');

        $response = $this->getJson('/spa/billing/sources');

        $response
            ->assertStatus(200)
            ->assertJsonCount(2, 'sources');
    }

    /** @test  */
    public function an_empty_array_is_returned_when_a_customer_has_no_sources()
    {
        $this->setUsersCustomer('fake-customer-with-no-sources');

        $response = $this->getJson('/spa/billing/sources');

        $response
            ->assertStatus(200)
            ->assertSimilarJson([]);
    }

    /** @test  */
    public function a_bad_request_resets_the_customer_id_and_returns_gracfully()
    {
        $this->setUsersCustomer('fake-bad-request');
        $this->assertEquals('fake-bad-request', $this->getUsersCustomer());

        $response = $this->getJson('/spa/billing/sources');

        $this->assertNull($this->getUsersCustomer());
        $response
            ->assertStatus(200)
            ->assertSimilarJson([]);
    }

    /** @test  */
    public function a_customer_profile_is_automatically_created_when_users_customer_is_missing()
    {
        $this->setUsersCustomer(null);

        $response = $this->getJson('/spa/billing/sources');

        $response->assertStatus(200);
        $this->assertEquals($this->getUsersCustomer(), $response->getData()->id);
    }

    /** @test  */
    public function a_credit_card_can_be_set_as_default()
    {
        $customerId = 'fake-customer-with-two-sources';

        $response = $this->postJson("/spa/billing/customers/{$customerId}", [
            'default_source' => '1111111-1111-1111-1111-111111111111',
        ]);

        $this->assertEquals('1111111-1111-1111-1111-111111111111', $response->getData()->default_source);
    }

    /** @test  */
    public function any_errors_when_setting_a_credit_card_as_default_are_handled()
    {
        $customerId = 'fake-error';

        $response = $this->postJson("/spa/billing/customers/{$customerId}", [
            'default_source' => '1111111-1111-1111-1111-111111111111',
        ]);

        $response
            ->assertStatus(500)
            ->assertJson([
                'status' => 'error',
                'message' => 'This is a fake error.'
            ]);
    }

    /** @test  */
    public function a_credit_card_can_be_added()
    {
        $response = $this->postJson('/spa/billing', [
            'billing_first_name' => 'John',
            'billing_last_name' => 'Doe',
            'billing_address_line_1' => '101 Main Street',
            'billing_address_city' => 'Dallas',
            'billing_address_state' => 'TX',
            'billing_address_zipcode' => '75000',
            'billing_phone' => '(555) 123-1234',
            'payment_method_token' => 'fake-valid-nonce',
            'card_first_name' => 'John',
            'card_last_name' => 'Doe',
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'first_name' => 'John',
                'last_name' => 'Doe',
                'billing_address1' => '101 Main Street',
                'billing_address2' => null,
                'billing_city' => 'Dallas',
                'billing_postcode' => '75000',
                'billing_state' => 'TX',
                'billing_country' => 'US',
                'billing_phone' => '(555) 123-1234',
            ]);
    }

    /** @test  */
    public function a_specific_credit_card_can_be_added()
    {
        $response = $this->postJson('/spa/billing', [
            'billing_first_name' => 'John',
            'billing_last_name' => 'Doe',
            'billing_address_line_1' => '101 Main Street',
            'billing_address_city' => 'Dallas',
            'billing_address_state' => 'TX',
            'billing_address_zipcode' => '75000',
            'billing_phone' => '(555) 123-1234',
            'payment_method_token' => 'fake-mastercard-4444-11-2021',
            'card_first_name' => 'John',
            'card_last_name' => 'Doe',
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'first_name' => 'John',
                'last_name' => 'Doe',
                'brand' => 'mastercard',
                'last4' => '4444',
                'expiry_month' => '11',
                'expiry_year' => '2021',
                'billing_address1' => '101 Main Street',
                'billing_address2' => null,
                'billing_city' => 'Dallas',
                'billing_postcode' => '75000',
                'billing_state' => 'TX',
                'billing_country' => 'US',
                'billing_phone' => '(555) 123-1234',
            ]);
    }

    /**
     * Credit card form validation provider.
     *
     * @return  array
     */
    public function creditCardFormValidationProvider()
    {
        return [
            ['billing_first_name'],
            ['billing_last_name'],
            ['billing_address_line_1'],
            ['billing_address_city'],
            ['billing_address_state'],
            ['billing_address_zipcode'],
            ['billing_phone'],
            ['payment_method_token'],
            ['card_first_name'],
            ['card_last_name'],
        ];
    }

    /**
     * @test
     * @dataProvider  creditCardFormValidationProvider
     */
    public function all_missing_fields_are_reported_in_validation_errors($formInput)
    {
        $creditCardAttributes = [
            'billing_first_name' => 'John',
            'billing_last_name' => 'Doe',
            'billing_address_line_1' => '101 Main Street',
            'billing_address_city' => 'Dallas',
            'billing_address_state' => 'TX',
            'billing_address_zipcode' => '75000',
            'billing_phone' => '(555) 123-1234',
            'payment_method_token' => 'fake-valid-mastercard-nonce',
            'card_first_name' => 'John',
            'card_last_name' => 'Doe',
        ];
        $creditCardAttributes[$formInput] = '';

        $response = $this->postJson('/spa/billing', $creditCardAttributes);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([$formInput], 'data');
    }

    /** @test  */
    public function a_customer_profile_is_automatically_created_when_users_customer_is_missing_and_a_credit_card_can_be_added()
    {
        $this->setUsersCustomer(null);

        $response = $this->postJson('/spa/billing', [
            'billing_first_name' => 'John',
            'billing_last_name' => 'Doe',
            'billing_address_line_1' => '101 Main Street',
            'billing_address_city' => 'Dallas',
            'billing_address_state' => 'TX',
            'billing_address_zipcode' => '75000',
            'billing_phone' => '(555) 123-1234',
            'payment_method_token' => 'fake-valid-nonce',
            'card_first_name' => 'John',
            'card_last_name' => 'Doe',
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'first_name' => 'John',
                'last_name' => 'Doe',
                'billing_address1' => '101 Main Street',
                'billing_address2' => null,
                'billing_city' => 'Dallas',
                'billing_postcode' => '75000',
                'billing_state' => 'TX',
                'billing_country' => 'US',
                'billing_phone' => '(555) 123-1234',
            ]);
    }

    /** @test  */
    public function an_error_is_returned_from_a_bad_credit_card_number()
    {
        $response = $this->postJson('/spa/billing', [
            'billing_first_name' => 'John',
            'billing_last_name' => 'Doe',
            'billing_address_line_1' => '101 Main Street',
            'billing_address_city' => 'Dallas',
            'billing_address_state' => 'TX',
            'billing_address_zipcode' => '75000',
            'billing_phone' => '(555) 123-1234',
            'payment_method_token' => 'fake-card-error',
            'card_first_name' => 'John',
            'card_last_name' => 'Doe',
        ]);

        $response
            ->assertStatus(500)
            ->assertJsonFragment(['status' => 'error'])
            ->assertJsonStructure([
                'status', 'message',
            ]);
    }

    /** @test  */
    public function an_error_is_returned_from_a_bad_credit_cvv_number()
    {
        $response = $this->postJson('/spa/billing', [
            'billing_first_name' => 'John',
            'billing_last_name' => 'Doe',
            'billing_address_line_1' => '101 Main Street',
            'billing_address_city' => 'Dallas',
            'billing_address_state' => 'TX',
            'billing_address_zipcode' => '75000',
            'billing_phone' => '(555) 123-1234',
            'payment_method_token' => 'fake-cvv-error',
            'card_first_name' => 'John',
            'card_last_name' => 'Doe',
        ]);

        $response
            ->assertStatus(500)
            ->assertJsonFragment(['status' => 'error'])
            ->assertJsonStructure([
                'status', 'message',
            ]);
    }

    /** @test  */
    public function a_client_token_can_be_requested()
    {
        $response = $this->getJson('/spa/payments/token');

        $response->assertStatus(200);
        $clientToken = $response->getContent();
        $this->assertNotEmpty($clientToken);
        $this->assertIsString($clientToken);
    }

    /** @test  */
    public function requesting_a_client_token_for_an_invalid_customer_is_handled()
    {
        $this->setUsersCustomer('fake-bad-customer');

        $response = $this->getJson('/spa/payments/token');

        $response->assertStatus(500)
            ->assertJsonFragment(['status' => 'error'])
            ->assertJsonStructure([
                'status', 'message',
            ]);
    }

    /** @test  */
    public function a_credit_card_can_be_removed()
    {
        $sourceId = '1111111-1111-1111-1111-111111111111';

        $response = $this->deleteJson("/spa/billing/{$sourceId}");

        $response
            ->assertStatus(200)
            ->assertJson([
                'deleted' => true,
                'id' => $sourceId,
            ]);
    }

    /** @test  */
    public function an_invalid_credit_card_can_not_be_removed()
    {
        $sourceId = 'fake-bad-card';

        $response = $this->deleteJson("/spa/billing/{$sourceId}");

        $response->assertStatus(500)
            ->assertJsonFragment(['status' => 'error'])
            ->assertJsonStructure([
                'status', 'message',
            ]);
    }
}
