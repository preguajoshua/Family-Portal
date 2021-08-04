<?php

namespace App\Api\Transformers;

use Crypt;

class SourceTransformer extends TransformerAbstract
{
    public function transform($data)
    {
        return [
            'id' => $data->get('id'),
            'token' => Crypt::encrypt($data->get('id')),
            'first_name' => $data->get('firstName'),
            'last_name' => $data->get('lastName'),
            'brand' => $data->get('brand'),
            'last4' => $data->get('last4'),
            'expiry_month' => $data->get('expiryMonth'),
            'expiry_year' => $data->get('expiryYear'),
            'billing_address1' => $data->get('billingAddress')->line1,
            'billing_address2' => $data->get('billingAddress')->line2,
            'billing_city' => $data->get('billingAddress')->locality,
            'billing_state' => $data->get('billingAddress')->administrativeArea,
            'billing_postcode' => $data->get('billingAddress')->postalCode,
            'billing_country' => $data->get('billingAddress')->country,
            'billing_phone' => $data->get('phone'),
        ];
    }
}
