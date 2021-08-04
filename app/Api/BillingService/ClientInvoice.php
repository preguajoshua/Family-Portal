<?php

namespace App\Api\BillingService;

use App\Queries\QueryObject;
use Illuminate\Support\Facades\Http;
use App\Api\Transformers\InvoiceTransformer;

class ClientInvoice extends QueryObject
{
    public function fetch()
    {
        $response = Http::post(config('axxess.billing_service.base_url') . '/invoice/Get', $this->getParams());
        $data = json_decode(removeUtf8Bom($response->body()), $assoc = false);

        return $this->transform($data);
    }

    private function transform($data)
    {
        $transformers = new InvoiceTransformer;

        return $transformers->_transform($data);
    }
}
