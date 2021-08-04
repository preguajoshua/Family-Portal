<?php

namespace App\Http\Controllers\Front;

use App\Facades\Query;
use App\Models\Account;
use App\Models\Patient;
use App\Models\AccountUser;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function index()
    {
        $user = Auth::getUser();
        $client = [];
        $provider = [];
        $token = csrf_token();

        if ($user->client()->Id) {
            $client = $user->client();

            $emrId = Session::get('emrId', 0);

            if ($emrId > 0) {
                $agencyId = $client->AgencyId;
                $locationId = $client->AgencyLocationId;
                $patientId = $client->Id;
                $patientContactId = $client->ContactId;
                $provider = Query::fetch('PatientAgencyLocationQuery', compact('agencyId', 'locationId', 'patientId', 'patientContactId'));

                if ($provider) {
                    $provider->Application = $user->getApplication();
                }
            }
        }

        return response()->json(compact('user', 'client', 'provider', 'token'));
    }

    /**
     * [client description]
     * POST session/client
     *
     * @return  [type]
     */
    public function client()
    {
        $clientId = Request::get('id');
        $clients = Session::get('clients');

        if ($clients == null || !count($clients) || !is_array($clients)) {
            Log::error('No clients found');

            return response()->json([
                'status' => 'fail',
                'data' => 'No clients found',
            ], 500);
        }

        foreach ($clients as $client) {
            if ($clientId == $client['Id']) {
                $emrId = $this->getEmrId($client['Id'], $client['ContactId']);

                Session::put('emrId', $emrId);
                Session::put('client', $client);

                return response()->json([
                    'status' => 'success',
                    'data' => ['emrId' => $emrId, 'client' => $client],
                ], 200);
            }
        }

        Log::error("Can not find client matching {$clientId}");

        return response()->json([
            'status' => 'fail',
            'data' => "Can not find client matching {$clientId}",
        ], 500);
    }

    //
    private function getEmrId($patientId, $patientContactId)
    {
        $patient = Patient::query()
            ->where('emr_patient_id', $patientId)
            ->where('emr_patient_contact_id', $patientContactId)
            ->first();

        if (! $patient) {
            Log::info('Can not find patient from EMR IDs');
            return 0;
        }

        $accountUser = AccountUser::find($patient->account_user_id);
        $account = Account::find($accountUser->account_id);

        return $account->application_id;
    }
}
