<?php

namespace App\Http\Controllers\Api;

use Throwable;
use App\Models\Patient;
use App\Models\AccountUser;
use Illuminate\Http\Request;
use App\Services\UserServiceV2;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class PatientControllerV2 extends Controller
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
            return response()->json(['error' => 'Patient not found'], 404);
        }

        $accountUser = AccountUser::find($patient->account_user_id);

        try {
            DB::transaction(function () use ($request, $accountUser, $patient) {
                $user = (new UserServiceV2)->firstOrCreateUser($request->email, $request->name, $accountUser->account_id);
                $accountUserInstance = (new UserServiceV2)->firstOrCreateAccountUser($user->id, $accountUser->account_id);

                $patient->update([
                    'account_user_id' => $accountUserInstance->id
                ]);
            });

        } catch (Throwable $e) {
            Log::channel('teams')->error($e->getMessage());
            Log::channel('teams')->info('Rolled back update');
            Log::channel('teams')->debug($request->all());

            return response()->json([
                'status' => 'fail',
                'message' => 'Failed to update user',
                'debug' => $e->getMessage(),
            ], 500);
        }

        return response()->json($data = [], 204);
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

        Patient::query()
            ->where('emr_patient_id', $request->patient_id)
            ->where('emr_patient_contact_id', $request->patient_contact_id)
            ->delete();

        return response()->json($data = [], 204);
    }
}
