<?php

namespace App\Queries;

use App\Models\HomeCare\Invoice;

class PatientInvoiceQuery extends QueryObject
{
    /**
     * {@inheritdoc}
     */
    public function fetch()
    {
        return $this->fetchQueryByApplication();
    }

    /**
     * Fetch HomeCare query.
     *
     * @return  Collection
     */
    protected function fetchHomeCareQuery()
    {
        $model = new Invoice;
        $model->setConnection('homecare_cluster_1');

        $result = $model->where('PatientId', '=', $this->getParam('patientId'))
            ->where('Id', '=', $this->getParam('invoiceId'))
            ->first();

        return $result;
    }

    /**
     * Fetch AgencyCore query.
     *
     * @return  Collection
     */
    protected function fetchAgencyCoreQuery()
    {
        return collect();
    }

    /**
     * Fetch Hospice query.
     *
     * @return  Collection
     */
    protected function fetchHospiceQuery()
    {
        return collect();
    }
}
