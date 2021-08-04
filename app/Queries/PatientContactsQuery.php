<?php

namespace App\Queries;

use App\Facades\Identity;
use App\Exceptions\EmrApiException;
use Illuminate\Support\Facades\Http;
use App\Http\Payloads\ContactPayload;
use App\Http\Payloads\ContactCollection;

class PatientContactsQuery extends QueryObject
{
    /**
     * {@inheritdoc}
     */
    public function fetch(): ContactCollection
    {
        return $this->fetchQueryByApplication();
    }

    /**
     * Fetch Contacts from Home Health.
     *
     * @return  \App\Http\Payloads\ContactCollection
     *
     * @throws  \App\Exceptions\EmrApiException
     */
    protected function fetchAgencyCoreQuery(): ContactCollection
    {
        $token = Identity::rawToken();
        $url = resolve('AgencyCoreApiService')->url($slug = "patient-contacts/{$this->getParam('patientId')}");

        $response = Http::withToken($token)->get($url);

        if ($response->failed()) {
            throw new EmrApiException('Failed to fetch Contacts from Home Health');
        }

        $contacts = array_map(fn($contact) => ContactPayload::fromHomeHealthApi($contact), $response->object());

        return new ContactCollection(...$contacts);
    }

    /**
     * Fetch Contacts from Home Care.
     *
     * @return  \App\Http\Payloads\ContactCollection
     *
     * @throws  \App\Exceptions\EmrApiException
     */
    protected function fetchHomeCareQuery(): ContactCollection
    {
        $token = Identity::rawToken();
        $url = config('axxess.homecare_api.base_url') . '/patientContacts';

        $response = Http::withToken($token)->get($url, [
            'PatientId' =>  $this->getParam('patientId'),
            'PatientContactId' =>  $this->getParam('patientContactId'),
        ]);

        if ($response->failed()) {
            throw new EmrApiException('Failed to fetch Contacts from Home Care');
        }

        $contacts = array_map(fn($contact) => ContactPayload::fromHomeCareApi($contact), $response->object()->patientContacts);

        return new ContactCollection(...$contacts);
    }

    /**
     * Fetch Contacts from Hospice.
     *
     * @return  \App\Http\Payloads\ContactCollection
     *
     * @throws  \App\Exceptions\EmrApiException
     */
    protected function fetchHospiceQuery(): ContactCollection
    {
        $token = Identity::rawToken();
        $url = config('axxess.hospice_api.base_url') . '/patient-contacts';

        $response = Http::withToken($token)->get($url, [
            'AgencyId' => $this->getParam('agencyId'),
            'PatientId' => $this->getParam('patientId'),
            'PatientContactId' => $this->getParam('patientContactId'),
        ]);

        if ($response->failed()) {
            throw new EmrApiException('Failed to fetch Contacts from Hospice');
        }

        $contacts = array_map(fn($contact) => ContactPayload::fromHospiceApi($contact), $response->object());

        return new ContactCollection(...$contacts);
    }
}
