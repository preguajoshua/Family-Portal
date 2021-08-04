<?php

namespace App\Queries;

use App\Facades\Identity;
use App\Exceptions\EmrApiException;
use Illuminate\Support\Facades\Http;
use App\Http\Payloads\AgencyLocationPayload;

class PatientAgencyLocationQuery extends QueryObject
{
    /**
     * {@inheritdoc}
     */
    public function fetch(): AgencyLocationPayload
    {
        return $this->fetchQueryByApplication();
    }

    /**
     * Fetch Location from Home Health.
     *
     * @return  \App\Http\Payloads\AgencyLocationPayload
     *
     * @throws  \App\Exceptions\EmrApiException
     */
    protected function fetchAgencyCoreQuery(): AgencyLocationPayload
    {
        $token = Identity::rawToken();
        $url = resolve('AgencyCoreApiService')->url($slug = "location/{$this->getParam('locationId')}");

        $response = Http::withToken($token)->get($url);

        if ($response->failed()) {
            throw new EmrApiException('Failed to fetch Location from Home Health');
        }

        return AgencyLocationPayload::fromHomeHealthApi($response->object());
    }

    /**
     * Fetch Location from Home Care.
     *
     * @return  \App\Http\Payloads\AgencyLocationPayload
     *
     * @throws  \App\Exceptions\EmrApiException
     */
    protected function fetchHomeCareQuery(): AgencyLocationPayload
    {
        $token = Identity::rawToken();
        $url = config('axxess.homecare_api.base_url') . '/agencylocation';

        $response = Http::withToken($token)->get($url, [
            'LocationId' => $this->getParam('locationId'),
        ]);

        if ($response->failed()) {
            throw new QueryException('Failed to fetch Location from Home Care');
        }

        return AgencyLocationPayload::fromHomeCareApi($response->object()->agencyLocation);
    }

    /**
     * Fetch Location from Hospice.
     *
     * @return  \App\Http\Payloads\AgencyLocationPayload
     *
     * @throws  \App\Exceptions\EmrApiException
     */
    protected function fetchHospiceQuery(): AgencyLocationPayload
    {
        $token = Identity::rawToken();
        $url = config('axxess.hospice_api.base_url') . '/patient-location';

        $response = Http::withToken($token)->get($url, [
            'AgencyId' => $this->getParam('agencyId'),
            'PatientId' => $this->getParam('patientId'),
            'PatientContactId' => $this->getParam('patientContactId'),
        ]);

        if ($response->failed()) {
            throw new QueryException('Failed to fetch Location from Hospice');
        }

        return AgencyLocationPayload::fromHospiceApi($response->object());
    }
}
