<?php
namespace App\Api\Transformers;

class InvoiceTransformer extends TransformerAbstract
{

    public function transform($data)
    {
        return [
            'Id'                       => $data->get('Id'),
            'AddressCity'              => $data->get('AddressCity'),
            'AddressLine1'             => $data->get('AddressLine1'),
            'AddressStateCode'         => $data->get('AddressStateCode'),
            'AddressZipCode'           => $data->get('AddressZipCode'),
            'AdjustmentAmount'         => $data->get('AdjustmentAmount'),
            'ClaimDate'                => $data->get('ClaimDate'),
            'CustomLineItems'          => $data->get('CustomLineItems'),
            'StartDate'                => $data->get('StartDate'),
            'DueDate'                  => $data->get('DueDate'),
            'EndDate'                  => $data->get('EndDate'),
            'FirstName'                => $data->get('FirstName'),
            'InvoiceNumber'            => $data->get('InvoiceNumber'),
            'LastName'                 => $data->get('LastName'),
            'NegativeAdjustmentAmount' => $data->get('NegativeAdjustmentAmount'),
            'NetDue'                   => $data->get('NetDue'),
            'PaidAmount'               => $data->get('PaidAmount'),
            'ProspectivePay'           => $data->get('ProspectivePay'),
            'Status'                   => $data->get('Status'),
            'StatusName'               => $data->get('StatusName'),
            'TotalTax'                 => $data->get('TotalTax'),
            'Visits'                   => $data->get('Visits'),
            'Billing'                  => $data->get('BillingAddress'),
        ];
    }

}
