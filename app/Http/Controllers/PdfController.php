<?php

namespace App\Http\Controllers;

use Auth;
use Query;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\PatientNotesService;

class PdfController extends Controller
{
    /**
     * Generate a calendar PDF.
     *
     * @param  Request  $request
     * @return Response
     */
    public function calendar(Request $request)
    {
        $user = Auth::getUser();
        $client = $user->client();

        $application = $user->getApplication();
        $agencyId = $user->getAgencyId();
        $patientId = $user->getPatientId();
        $patientContactId = $user->getPatientContactId();
        $locationId = $client->AgencyLocationId;
        $provider = Query::fetch('PatientAgencyLocationQuery', compact('agencyId', 'locationId', 'patientId', 'patientContactId'));

        $start = $request->get('start', date('Y-m-01'));
        $end = $request->get('end', date('Y-m-t'));
        $notes = (new PatientNotesService)->getNotesByRange($patientId, $start, $end);
        $events = Query::fetch('PatientTasksQuery', compact('application', 'agencyId', 'patientId', 'patientContactId', 'start', 'end'));

        $monthDate = date('Y-m-d', strtotime($start . ' +7 days'));

        $view = view('pdf.calendar', [
            'client' => $client,
            'provider' => $provider,
            'notes' => $notes,
            'events' => $events,
            'monthDate' => $monthDate,
        ]);

        $body = sprintf("'%s'", $this->sanitizeOutput($view->render()));

        $content = Http::withBody($body, 'application/json')->post(config('axxess.pdf_service_api'));

        if (! $content->successful()) {
            abort(500);
        }

        $method = $request->get('download') ? 'attachment' : "inline";
        $filename = "calendar_" . strtolower(date('M_y', strtotime($monthDate))) . ".pdf";
        $length = strlen($content);

        return response($content)
            ->header("Content-type", "application/pdf;charset:utf-8")
            ->header("Content-Length", $length)
            ->header("Content-Disposition", "{$method}; filename={$filename}");
    }

    /**
     * Generate a medications PDF.
     *
     * @param  Request  $request
     * @return Response
     */
    public function medications(Request $request)
    {
        $user = Auth::getUser();
        $client = $user->client();

        $application = $user->getApplication();
        $agencyId = $user->getAgencyId();
        $patientId = $user->getPatientId();
        $patientContactId = $user->getPatientContactId();
        $page = $request->get('page', 1);
        $medications = Query::fetch('PatientMedicationsQuery', compact('application', 'agencyId', 'patientId', 'patientContactId', 'page'));

        $locationId = $client->AgencyLocationId;
        $provider = Query::fetch('PatientAgencyLocationQuery', compact('agencyId', 'locationId', 'patientId', 'patientContactId'));

        $print = in_array($request->get('print'), ['all', 'active'])
            ? $request->get('print')
            : 'all';

        $view = view('pdf.medications', [
            'client' => $client,
            'medications' => $medications,     // TODO - sortByDesc('Active')
            'provider' => $provider,
            'options' => [
                'active' => $print == 'active' || $print == 'all',
                'inactive' => $print == 'all',
            ],
        ]);

        $body = sprintf("'%s'", $view->render());
        $content = Http::withBody($body, 'application/json')->post(config('axxess.pdf_service_api'));

        if (! $content->successful()) {
            abort(500);
        }

        $method = $request->get('download') ? 'attachment' : "inline";
        $filename = "medications_{$print}.pdf";
        $length = strlen($content);

        return response($content)
            ->header("Content-type", "application/pdf;charset:utf-8")
            ->header("Content-Length", $length)
            ->header("Content-Disposition", "{$method}; filename={$filename}");
    }

    /**
     * Sanitize output.
     *
     * @param   string  $buffer
     * @return  string
     */
    private function sanitizeOutput($buffer)
    {
        $search = array(
            '/\>[^\S ]+/s', // strip whitespaces after tags, except space
            '/[^\S ]+\</s', // strip whitespaces before tags, except space
            '/(\s)+/s', // shorten multiple whitespace sequences
        );

        $replace = array(
            '>',
            '<',
            '\\1',
        );

        $buffer = preg_replace($search, $replace, $buffer);

        return addslashes($buffer);
    }
}
