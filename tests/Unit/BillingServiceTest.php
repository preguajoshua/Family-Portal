<?php

namespace Tests\Unit;

use Tests\TestCase;
use Tests\assets\BillingServiceFixtures;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BillingServiceTest extends TestCase
{
    /** @test  */
    public function the_expected_client_invoice_list_format_is_returned()
    {
        $result = $this->postJson(config('axxess.billing_service.base_url') . '/client/invoice/List', [
            'patientId' => 'b5d29055-b5e7-4f1a-8d24-facebe7474e7',
            'agencyId' => '122ec20a-705b-4974-9437-8f6233a6e953',
            'unpaid' => '1',
            'startDate' => '2019-01-01',
            'endDate' => '2020-01-01',
            'status' => [],
        ])->json();

        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(1, count($result));
        $this->assertIsArray($result[0]);
        $keys = array_keys($result[0]);
        $this->assertCount(51, $keys);
        $this->assertSame(BillingServiceFixtures::CLIENT_INVOICE_KEYS, $keys);
        $this->assertEquals('b5d29055-b5e7-4f1a-8d24-facebe7474e7', $result[0]['PatientId']);
    }

    /** @test  */
    public function the_expected_invoice_format_is_returned()
    {
        $result = $this->postJson(config('axxess.billing_service.base_url') . '/invoice/Get', [
            'patientId' => 'f9d6fcc7-4fdf-4258-8414-8dbf987a212e',
            'agencyId' => '122ec20a-705b-4974-9437-8f6233a6e953',
            'Id' => 'cc58451d-9b1d-49b7-9ae4-a152e35a4fdd',
        ])->json();

        $this->assertIsArray($result);
        $keys = array_keys($result);
        $this->assertCount(101, $keys);
        $this->assertSame(BillingServiceFixtures::INVOICE_KEYS, $keys);
        $this->assertEquals('f9d6fcc7-4fdf-4258-8414-8dbf987a212e', $result['PatientId']);
        $this->assertEquals('122ec20a-705b-4974-9437-8f6233a6e953', $result['AgencyId']);
        $this->assertEquals('cc58451d-9b1d-49b7-9ae4-a152e35a4fdd', $result['Id']);

        $this->assertIsArray($result['Visits']);
        $this->assertGreaterThanOrEqual(1, count($result['Visits']));
        $visits = $result['Visits'];
        $this->assertIsArray($visits[0]);
        $keys = array_keys($visits[0]);
        $this->assertCount(52, $keys);
        $this->assertSame(BillingServiceFixtures::INVOICE_VISIT_KEYS, $keys);

        $this->assertIsArray($result['BillingAddress']);
        $keys = array_keys((array) $result['BillingAddress']);
        $this->assertCount(15, $keys);
        $this->assertSame(BillingServiceFixtures::INVOICE_BILLING_ADDRESS_KEYS, $keys);
    }

    /** @test  */
    public function the_expected_claims_payment_format_is_returned()
    {
        $result = $this->postJson(config('axxess.billing_service.base_url') . '/getclaimpayments', [
            'patientId' => 'b132310e-d2d0-4ca0-9889-c48970abf2b4',
            'agencyId' => '122ec20a-705b-4974-9437-8f6233a6e953',
            'claimId' => '085215ef-00fe-4654-858a-0454a3e1a7a2',
        ])->json();

        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(1, count($result));
        $this->assertIsArray($result[0]);
        $keys = array_keys($result[0]);
        $this->assertCount(21, $keys);
        $this->assertSame(BillingServiceFixtures::CLAIM_PAYMENT_KEYS, $keys);
        $this->assertEquals('b132310e-d2d0-4ca0-9889-c48970abf2b4', $result[0]['PatientId']);
        $this->assertEquals('122ec20a-705b-4974-9437-8f6233a6e953', $result[0]['AgencyId']);
        $this->assertEquals('085215ef-00fe-4654-858a-0454a3e1a7a2', $result[0]['ClaimId']);
    }
}
