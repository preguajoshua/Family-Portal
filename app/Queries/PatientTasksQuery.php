<?php

namespace App\Queries;

use App\Facades\Identity;
use App\Http\Payloads\TaskPayload;
use App\Exceptions\EmrApiException;
use Illuminate\Support\Facades\Http;
use App\Http\Payloads\TaskCollection;

class PatientTasksQuery extends QueryObject
{
    /**
     * Override default query limit.
     *
     * @var  integer
     */
    protected $limit = 100;

    /**
     * A range of dates for the query.
     *
     * @var  array
     */
    protected $dateRange = [];

    /**
     * {@inheritdoc}
     */
    public function fetch(): TaskCollection
    {
        $this->dateRange = $this->dateRange();

        return $this->fetchQueryByApplication();
    }

    /**
     * Fetch Tasks from Home Health.
     *
     * @return  \App\Http\Payloads\TaskCollection
     *
     * @throws  \App\Exceptions\EmrApiException
     */
    protected function fetchAgencyCoreQuery(): TaskCollection
    {
        $token = Identity::rawToken();
        $url = resolve('AgencyCoreApiService')->url($slug = "patient-tasks/{$this->getParam('patientId')}");

        $response = Http::withToken($token)->get($url, [
            'startDate' => $this->dateRange['startDate'],
            'endDate' => $this->dateRange['endDate'],
        ]);

        if ($response->failed()) {
            throw new EmrApiException('Failed to fetch Tasks from Home Health');
        }

        $tasks = array_map(fn($task) => TaskPayload::fromHomeHealthApi($task), $response->object());

        return new TaskCollection(null, ...$tasks);
    }

    /**
     * Fetch Tasks from Home Care.
     *
     * @return  \App\Http\Payloads\TaskCollection
     *
     * @throws  \App\Exceptions\EmrApiException
     */
    protected function fetchHomeCareQuery(): TaskCollection
    {
        $token = Identity::rawToken();
        $url = config('axxess.homecare_api.base_url') . '/patientTasksByDate';

        $response = Http::withToken($token)->get($url, [
            'PatientId' => $this->getParam('patientId'),
            'PatientContactId' => $this->getParam('patientContactId'),
            'StartDate' => $this->dateRange['startDate'],
            'EndDate' => $this->dateRange['endDate'],
            'Page' => $this->hasParam('page') ? $this->getParam('page') : 1,
            'PageLength' => $this->hasParam('perPage') ?  $this->getParam('perPage') : $this->limit,
        ]);

        if ($response->failed()) {
            throw new EmrApiException('Failed to fetch Tasks from Home Care');
        }

        $tasks = array_map(fn($task) => TaskPayload::fromHomeCareApi($task), $response->object()->items);

        return new TaskCollection($response->object()->itemCount, ...$tasks);
    }

    /**
     * Fetch Tasks from Hospice.
     *
     * @return  \App\Http\Payloads\TaskCollection
     *
     * @throws  \App\Exceptions\EmrApiException
     */
    protected function fetchHospiceQuery(): TaskCollection
    {
        $token = Identity::rawToken();
        $url = config('axxess.hospice_api.base_url') . '/patient-tasks-by-date';

        $response = Http::withToken($token)->get($url, [
            'AgencyId' => $this->getParam('agencyId'),
            'PatientId' => $this->getParam('patientId'),
            'PatientContactId' => $this->getParam('patientContactId'),
            'StartDate' => $this->dateRange['startDate'],
            'EndDate' => $this->dateRange['endDate'],
            'Page' => $this->hasParam('page') ? $this->getParam('page') : 1,
            'PageLength' => $this->hasParam('PageLength') ?  $this->getParam('PageLength') : $this->limit,
        ]);

        if ($response->failed()) {
            throw new EmrApiException('Failed to fetch Tasks from Hospice');
        }

        $tasks = array_map(fn($task) => TaskPayload::fromHospiceApi($task), $response->object()->items);

        return new TaskCollection($response->object()->itemCount, ...$tasks);
    }

    /**
     * Get the date range for the query.
     *
     * @return  array
     */
    private function dateRange()
    {
        if ($this->hasParam('filter')) {
            return $this->dateRangeFromFilter($this->getParam('filter'));
        }

        return [
            'startDate' => $this->getParam('start'),
            'endDate' => $this->getParam('end'),
        ];
    }

    /**
     * Get the date range from the filter.
     *
     * @param   string  $filter
     * @return  array
     */
    private function dateRangeFromFilter($filter)
    {
        switch ($filter) {
            case 'today':
                $startDate = date("Y-m-d");
                break;

            case 'yesterday':
                $startDate = date("Y-m-d", strtotime('-1 days'));
                break;

            case '7days':
                $startDate = date("Y-m-d", strtotime('-7 days'));
                break;

            case '30days':
                $startDate = date("Y-m-d", strtotime('-30 days'));
                break;

            default:
                $startDate = date("Y-m-d", strtotime('-3650 days'));
        }

        $endDate = date('Y-m-d');

        return [
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
    }
}
