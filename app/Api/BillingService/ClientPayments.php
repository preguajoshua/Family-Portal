<?php
namespace App\Api\BillingService;

use App\Queries\QueryObject;
use Illuminate\Support\Facades\Http;

class ClientPayments extends QueryObject
{
    protected $timestamp;

    public function __construct()
    {
        $this->timestamp = time();
    }

    public function fetch()
    {
        $response = Http::post(config('axxess.billing_service.base_url') . '/getclaimpayments');
        $data = json_decode(removeUtf8Bom($response->body()), $assoc = false);

        if (! $data) {
            return [];
        }

        usort($data, [$this, 'sortDate']);

        return $data;
    }

    private function sortDate($a, $b)
    {
        $t1 = strtotime($a->Date);
        $t2 = strtotime($b->Date);

        return $t2 <=> $t1;
    }
}
