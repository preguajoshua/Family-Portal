<?php

namespace App\Api\BillingService;

use Api;
use App\Queries\QueryObject;
use Illuminate\Support\Facades\Http;
use App\Api\Transformers\InvoiceTransformer;
use Illuminate\Database\Eloquent\Collection;

class ClientInvoices extends QueryObject
{
    protected $timestamp;

    public function __construct()
    {
        $this->timestamp = time();
    }

    public function fetch()
    {
        $this->setParam('status', []);
        $this->setParam('startDate', date('Y-m-d', strtotime('-1 YEAR')));
        $this->setParam('endDate', date('Y-m-d'));

        $response = Http::post(config('axxess.billing_service.base_url') . '/client/invoice/List', $this->getParams());
        $data = json_decode(removeUtf8Bom($response->body()), $assoc = false);

        if (! $data) {
            return [];
        }

        $data = array_values(array_filter($data, function ($item) {
            return $item->StatusName != 'Created';
        }));

        if (! $data) {
            return [];
        }

        if ($this->getParam('unpaid') != 1) {

            $data = array_map(function ($item) {
                $item->points = 0;

                if ($item->Status == 3030) {
                    $item->points = 0;
                    return $item;
                }

                $balance = ($item->NetDue - $item->PaidAmount + $item->AdjustmentAmount) - $item->NegativeAdjustmentAmount;

                if ($balance > 0) {
                    $item->points = .7;
                } else if ($balance < 0) {
                    $item->points = .6;
                    return $item;
                }

                if (strtotime($item->DueDate) < time()) {
                    $item->points += .05;
                }

                return $item;
            }, $data);

            usort($data, [$this, 'duePaymentSort']);

            return $this->transform($data);
        }

        $patientId = $this->getParam('patientId');
        $agencyId  = $this->getParam('agencyId');

        return $this->transform(array_map(function ($item) use ($patientId, $agencyId) {
            $Id = $item->Id;

            return Api::request('BillingService\ClientInvoice', compact('patientId', 'agencyId', 'Id'));
        }, $data));
    }

    private function transform($data)
    {
        $transformers = new InvoiceTransformer;

        return $transformers->collection(new Collection($data));
    }

    private function duePaymentSort($a, $b)
    {
        return $a->points <=> $b->points;
    }
}
