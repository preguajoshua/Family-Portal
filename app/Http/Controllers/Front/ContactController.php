<?php

namespace App\Http\Controllers\Front;

use App\Facades\Query;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{
    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function index()
    {
        $user = Auth::getUser();

        $application = $user->getApplication();
        $agencyId = $user->getAgencyId();
        $patientId = $user->getPatientId();
        $patientContactId = $user->getPatientContactId();

        $contacts = Query::pagination('PatientContactsQuery', compact('application', 'agencyId', 'patientId', 'patientContactId'));

        return response()->json($contacts->toArray());
    }
}
