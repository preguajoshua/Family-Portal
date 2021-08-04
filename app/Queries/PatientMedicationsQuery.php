<?php

namespace App\Queries;

use App\Facades\Identity;
use App\Exceptions\EmrApiException;
use Illuminate\Support\Facades\Http;
use App\Http\Payloads\MedicationPayload;
use App\Http\Payloads\MedicationCollection;

class PatientMedicationsQuery extends QueryObject
{
    /**
     * Override default query limit.
     *
     * @var  integer
     */
    protected $limit = 200;

    /**
     * {@inheritdoc}
     */
    public function fetch(): MedicationCollection
    {
        return $this->fetchQueryByApplication();
    }

    /**
     * Fetch Medications from Home Health.
     *
     * @return  \App\Http\Payloads\MedicationCollection
     *
     * @throws  \App\Exceptions\EmrApiException
     */
    protected function fetchAgencyCoreQuery(): MedicationCollection
    {
        $token = Identity::rawToken();
        $url = resolve('AgencyCoreApiService')->url($slug = "patient-medications/{$this->getParam('patientId')}");

        $response = Http::withToken($token)->get($url);

        if ($response->failed()) {
            throw new EmrApiException('Failed to fetch Medications from Home Health');
        }

        $medications = array_map(fn($medication) => MedicationPayload::fromHomeHealthApi($medication), $response->object());

        return new MedicationCollection(...$medications);
    }

    /**
     * Fetch Medications from Home Care.
     *
     * @return  \App\Http\Payloads\MedicationCollection
     *
     * @throws  \App\Exceptions\EmrApiException
     */
    protected function fetchHomeCareQuery(): MedicationCollection
    {
        $token = Identity::rawToken();
        $url = config('axxess.homecare_api.base_url') . '/patientMedications';

        $response = Http::withToken($token)->get($url, [
            'PatientId' => $this->getParam('patientId'),
            'PatientContactId' => $this->getParam('patientContactId'),
            'Page' => $this->getParam('page'),
            'PageLength' => $this->limit,
        ]);

        if ($response->failed()) {
            throw new EmrApiException('Failed to fetch Medications from Home Care');
        }

        $medications = array_map(fn($medication) => MedicationPayload::fromHomeCareApi($medication), $response->object()->items);

        return new MedicationCollection(...$medications);
    }

    /**
     * Fetch Medications from Hospice.
     *
     * @return  \App\Http\Payloads\MedicationCollection
     *
     * @throws  \App\Exceptions\EmrApiException
     */
    protected function fetchHospiceQuery(): MedicationCollection
    {
        $token = Identity::rawToken();
        $url = config('axxess.hospice_api.base_url') . '/patient-medications';

        $response = Http::withToken($token)->get($url, [
            'AgencyId' => $this->getParam('agencyId'),
            'PatientId' => $this->getParam('patientId'),
            'PatientContactId' => $this->getParam('patientContactId'),
            'Page' => $this->getParam('page'),
            'PageLength' => $this->limit,
        ]);

        if ($response->failed()) {
            throw new EmrApiException('Failed to fetch Medications from Hospice');
        }

        $medications = array_map(fn($medication) => MedicationPayload::fromHospiceApi($medication), $response->object()->items);

        return new MedicationCollection(...$medications);
    }
}
