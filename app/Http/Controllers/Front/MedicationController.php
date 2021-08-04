<?php

namespace App\Http\Controllers\Front;

use App\Facades\Query;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class MedicationController extends Controller
{
    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $user = Auth::getUser();

        $application = $user->getApplication();
        $agencyId = $user->getAgencyId();
        $patientId = $user->getPatientId();
        $patientContactId = $user->getPatientContactId();
        $page = $request->get('page', 1);

        $medications = Query::fetch('PatientMedicationsQuery', compact('application', 'agencyId', 'patientId', 'patientContactId', 'page'));

        // TODO - sortBy('medicationDosage')
        return response()->json($medications->toArray());
    }
}
