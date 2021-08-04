<?php

namespace App\Queries;

use App\Facades\Identity;
use App\Services\UserServiceV2;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Http\Payloads\PatientPayload;
use App\Http\Payloads\PatientCollection;
use App\Services\HomeCareApiPermissions;

class PatientsQuery extends QueryObject
{
    /**
     * {@inheritdoc}
     */
    public function fetch(): PatientCollection
    {
        // Fetch clients from all EMRs
        $homeHealthClients = $this->fetchAgencyCoreQuery();
        $homeCareClients = $this->fetchHomeCareQuery();
        $hospiceClients = $this->fetchHospiceQuery();

        $clients = array_merge(
            $homeHealthClients->items(),
            $homeCareClients->items(),
            $hospiceClients->items()
        );

        return new PatientCollection(...$clients);
    }

    /**
     * Fetch Clients from Home Health.
     *
     * @return  \App\Http\Payloads\PatientCollection
     */
    protected function fetchAgencyCoreQuery(): PatientCollection
    {
        if (! $baseUrl = config('axxess.agencycore_api.base_url')) {
            return new PatientCollection;
        }

        $token = Identity::rawToken();
        $url = resolve('AgencyCoreApiService')->url($slug = "clients/{$this->getParam('loginId')}");

        $response = Http::withToken($token)->get($url);

        if ($response->failed()) {
            // Do not throw EmrApiException, as fetch calls are chained
            Log::channel('teams')->error('Failed to fetch Clients from Home Health');

            return new PatientCollection;
        }


        $clients = array_map(fn($client) => PatientPayload::fromHomeHealthApi($client), $response->object());

        return new PatientCollection(...$clients);
    }

    /**
     * Fetch Clients from Home Care.
     *
     * @return  \App\Http\Payloads\PatientCollection
     */
    protected function fetchHomeCareQuery(): PatientCollection
    {
        if (! $baseUrl = config('axxess.homecare_api.base_url')) {
            return new PatientCollection;
        }

        $token = Identity::rawToken();
        $url = "{$baseUrl}/patients-by-contact-ids";

        $patientContactIds = (new UserServiceV2)->getPatientContactIds($this->getParam('userId'), $appId = 2);

        if (! $patientContactIds) {
            return new PatientCollection;
        }

        $response = Http::withToken($token)->get($url, [
            'PatientContactIds' => $patientContactIds,
        ]);

        if ($response->failed()) {
            // Do not throw EmrApiException, as fetch calls are chained
            Log::channel('teams')->error('Failed to fetch Clients from Home Care');

            return new PatientCollection;
        }

        $clientsCollection = collect($response->object());

        // TODO fetch isPayor/isAgencyBankAccountSetup/isPatientAccountSetup
        if ($this->getParam('action') === 'loadAndCache') {
            $clientsCollection->each(function ($client) {
                (new HomeCareApiPermissions($client))->loadAndCache();
            });

        } else {
            $clientsCollection->each(function ($client) {
                (new HomeCareApiPermissions($client))->load();
            });
        }
        // TODO

        $clients = array_map(fn($client) => PatientPayload::fromHomeCareApi($client), $clientsCollection->toArray());

        return new PatientCollection(...$clients);
    }

    /**
     * Fetch Clients from Hospice.
     *
     * @return  \App\Http\Payloads\PatientCollection
     */
    protected function fetchHospiceQuery(): PatientCollection
    {
        if (! $baseUrl = config('axxess.hospice_api.base_url')) {
            return new PatientCollection;
        }

        $token = Identity::rawToken();
        $url = "{$baseUrl}/patients-by-contact-ids";

        $patientContactIds = (new UserServiceV2)->getPatientContactIds($this->getParam('userId'), $appId = 256);

        if (! $patientContactIds) {
            return new PatientCollection;
        }

        $response = Http::withToken($token)->get($url, [
            'PatientContactIds' => $patientContactIds,
        ]);

        if ($response->failed()) {
            // Do not throw EmrApiException, as fetch calls are chained
            Log::channel('teams')->error('Failed to fetch Clients from Hospice');

            return new PatientCollection;
        }

        $clients = array_map(fn($client) => PatientPayload::fromHospiceApi($client), $response->object());

        return new PatientCollection(...$clients);
    }
}
