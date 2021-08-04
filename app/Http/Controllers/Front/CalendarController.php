<?php

namespace App\Http\Controllers\Front;

use Throwable;
use App\Facades\Identity;
use App\Facades\Query;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class CalendarController extends Controller
{
    //
    public function index(Request $request)
    {
        $user = Auth::getUser();

        $application = $user->getApplication();
        $agencyId = $user->getAgencyId();
        $patientId = $user->getPatientId();
        $patientContactId = $user->getPatientContactId();
        $filter = $request->input('filter');
        $page = $request->input('page', 1);
        $perPage = $request->input('perPage', 10);

        $tasks = Query::fetch('PatientTasksQuery', compact('application', 'agencyId', 'patientId', 'patientContactId', 'filter', 'page', 'perPage'));

        // TODO - sortByDesc('EventStartDate')
        return response()->json([
            'data' => $tasks->toArray(),
            'count' => $tasks->nonPaginatedCount(),
        ]);
    }

    //
    public function events(Request $request)
    {
        $user = Auth::getUser();

        $application = $user->getApplication();
        $agencyId = $user->getAgencyId();
        $patientId = $user->getPatientId();
        $patientContactId = $user->getPatientContactId();
        $start = $request->input('start');
        $end = $request->input('end');

        $tasks = Query::fetch('PatientTasksQuery', compact('application', 'agencyId', 'patientId', 'patientContactId', 'start', 'end'));

        return response()->json($tasks->toArray());
    }

    //
    public function downloadDocument(Request $request, $taskId)
    {
        $token = Identity::rawToken();
        $url = config('axxess.homecare_api.base_url') . '/document/print';

        $user = Auth::getUser();
        $patientId = $user->getPatientId();
        $patientContactId = $user->getPatientContactId();

        try {
            $response = Http::withToken($token)->get($url, [
                'taskId' => $taskId,
                'patientId' => $patientId,
                'patientContactId' => $patientContactId
            ]);

        } catch (Throwable $e) {
            abort(404, 'Service is unavailable');
        }

        if (! $response->successful()) {
            abort(404, 'Documentation not found, Please try again later');
        }

        $filename = $this->getFileName($taskId);
        $method = $request->get('download')  ? 'attachment' : 'inline';

        return response($response)
            ->header('Content-type', 'application/pdf')
            ->header('Content-Length', strlen($response))
            ->header('Content-Disposition', "{$method}; filename={$filename}");
    }

    //
    private function getFileName($taskId)
    {
        $client =  Session::get('client');

        return $client['FirstName'] . $client['LastName'] . "{$taskId}.pdf";
    }
}
