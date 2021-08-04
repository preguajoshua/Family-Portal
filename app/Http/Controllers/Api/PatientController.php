<?php

namespace App\Http\Controllers\Api;

use Throwable;
use App\Models\Account;
use App\Models\Patient;
use App\Models\AccountUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\UserCreationService;
use App\Exceptions\PatientUserAssociationExistsException;

class PatientController extends Controller
{
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'patient_id' => 'required|uuid',
            'patient_contact_id' => 'required|uuid',
        ]);

        $patient = Patient::query()
            ->where('emr_patient_id', $request->patient_id)
            ->where('emr_patient_contact_id', $request->patient_contact_id)
            ->first();

        if (! $patient) {
            return response()->json([
                'status' => 'fail',
                'data' => 'The patient_id and patient_contact_id do not match any existing records.',
            ], 422);
        }

        $currentAccountUser = AccountUser::findOrFail($patient->account_user_id);
        $account = Account::findOrFail($currentAccountUser->account_id);

        try {
            $payload = DB::transaction(function () use ($request, $account, $currentAccountUser, $patient) {
                $user = (new UserCreationService)->firstOrCreateUser($request->email, $request->name, $account->agency_name);
                $accountUser = (new UserCreationService)->firstOrCreateAccountUser($user, $account);

                if ($accountUser->id === $currentAccountUser->id) {
                    throw new PatientUserAssociationExistsException;
                }

                $patient->update([
                    'account_user_id' => $accountUser->id
                ]);

                return new UserApiPayload([
                    'login_id' => $user->login_id,
                    'agency_id' => $account->agency_id,
                    'patient_id' => $patient->emr_patient_id,
                    'patient_contact_id' => $patient->emr_patient_contact_id,
                ]);
            });

        } catch (PatientUserAssociationExistsException $e) {
            Log::channel('teams')->error('The patient-user association already exists');
            Log::channel('teams')->debug('Rolled back user-patient update', $request->all());

            return response()->json([
                'status' => 'fail',
                'data' => 'The patient-user association already exists.',
            ], 422);

        } catch (Throwable $e) {
            Log::channel('teams')->error($e->getMessage());
            Log::channel('teams')->debug('Rolled back user-patient update', $request->all());

            return response()->json([
                'message' => 'Failed to update the requested user-patient relationship.',
                'debug' => $e->getMessage(),
            ], 500);
        }

        return response()->json($payload, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|uuid',
            'patient_contact_id' => 'required|uuid',
        ]);

        $patient = Patient::query()
            ->where('emr_patient_id', $request->patient_id)
            ->where('emr_patient_contact_id', $request->patient_contact_id)
            ->first();

        if (! $patient) {
            return response()->json([
                'status' => 'fail',
                'data' => 'The patient_id and patient_contact_id do not match any existing records.',
            ], 422);
        }

        $patient->delete();

        return response()->json($data = [], 204);
    }
}
