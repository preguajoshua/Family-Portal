<?php

namespace App\Http\Controllers\Api;

use Throwable;
use App\Rules\EmrExists;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\UserCreationService;
use App\Exceptions\DuplicatePatientException;

class UserController extends Controller
{
    /**
     * Store a new resource.
     *
     * @param   Request  $request
     * @return  \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'agency_id' => 'required|uuid',
            'agency_name' => 'required|string',
            'application_id' => ['required', new EmrExists],
            'patient_id' => 'required|uuid',
            'patient_contact_id' => 'required|uuid',
        ]);

        try {
            $payload = DB::transaction(function () use ($request) {
                $user = (new UserCreationService)->firstOrCreateUser($request->email, $request->name, $request->agency_name);
                $account = (new UserCreationService)->firstOrCreateAccount($request->agency_id, $request->agency_name, $request->application_id);
                $accountUser = (new UserCreationService)->firstOrCreateAccountUser($user, $account);
                $patient = (new UserCreationService)->firstOrCreatePatient($accountUser->id, $request->patient_id, $request->patient_contact_id);

                return new UserApiPayload([
                    'login_id' => $user->login_id,
                    'agency_id' => $account->agency_id,
                    'patient_id' => $patient->emr_patient_id,
                    'patient_contact_id' => $patient->emr_patient_contact_id,
                ]);
            });

        } catch (DuplicatePatientException $e) {
            Log::channel('teams')->error('Duplicate patient and patient contact IDs not allowed');
            Log::channel('teams')->debug('Rolled back user creation', $request->all());

            return response()->json([
                'status' => 'fail',
                'data' => 'Duplicate patient and patient contact IDs not allowed',
            ], 422);

        } catch (Throwable $e) {
            Log::channel('teams')->error($e->getMessage());
            Log::channel('teams')->debug('Rolled back user creation', $request->all());

            return response()->json([
                'message' => 'Failed to create new user',
                'debug' => $e->getMessage(),
            ], 500);
        }

        return response()->json($payload, 201);
    }
}
