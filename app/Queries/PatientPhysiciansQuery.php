<?php

namespace App\Queries;

use App\Facades\Identity;
use App\Exceptions\EmrApiException;
use Illuminate\Support\Facades\Http;
use App\Http\Payloads\PhysicianPayload;
use App\Http\Payloads\PhysicianCollection;

class PatientPhysiciansQuery extends QueryObject
{
    /**
     * {@inheritdoc}
     */
    public function fetch(): PhysicianCollection
    {
        return $this->fetchQueryByApplication();
    }

    /**
     * Fetch Physicians from Home Health.
     *
     * @return  \App\Http\Payloads\PhysicianCollection
     *
     * @throws  \App\Exceptions\EmrApiException
     */
    protected function fetchAgencyCoreQuery(): PhysicianCollection
    {
        $token = Identity::rawToken();
        $url = resolve('AgencyCoreApiService')->url($slug = "patient-physicians/{$this->getParam('patientId')}");

        $response = Http::withToken($token)->get($url);

        if ($response->failed()) {
            throw new EmrApiException('Failed to fetch Physicians from Home Health');
        }

        $physicians = array_map(fn($physician) => PhysicianPayload::fromHomeHealthApi($physician), $response->object());

        return new PhysicianCollection(...$physicians);
    }

    /**
     * Fetch Physicians from Home Care.
     *
     * @return  \App\Http\Payloads\PhysicianCollection
     *
     * @throws  \App\Exceptions\EmrApiException
     */
    protected function fetchHomeCareQuery(): PhysicianCollection
    {
        $token = Identity::rawToken();
        $url = config('axxess.homecare_api.base_url') . '/patientPhysicians';

        $response = Http::withToken($token)->get($url, [
            'PatientId' =>  $this->getParam('patientId'),
            'PatientContactId' =>  $this->getParam('patientContactId'),
        ]);

        if ($response->failed()) {
            throw new EmrApiException('Failed to fetch Physicians from Home Care');
        }

        $physicians = array_map(fn($physician) => PhysicianPayload::fromHomeCareApi($physician), $response->object()->patientPhysicians);

        return new PhysicianCollection(...$physicians);
    }

    /**
     * Fetch Physicians from Hospice.
     *
     * @return  \App\Http\Payloads\PhysicianCollection
     *
     * @throws  \App\Exceptions\EmrApiException
     */
    protected function fetchHospiceQuery(): PhysicianCollection
    {
        $token = Identity::rawToken();
        $url = config('axxess.hospice_api.base_url') . '/patient-physicians';

        $response = Http::withToken($token)->get($url, [
            'AgencyId' => $this->getParam('agencyId'),
            'PatientId' => $this->getParam('patientId'),
            'PatientContactId' => $this->getParam('patientContactId'),
        ]);

        if ($response->failed()) {
            throw new EmrApiException('Failed to fetch Physicians from Hospice');
        }

        $physicians = array_map(fn($physician) => PhysicianPayload::fromHospiceApi($physician), $response->object());

        return new PhysicianCollection(...$physicians);
    }
}
