<?php

namespace App\Services;

use stdClass;
use App\Models\User;
use App\Models\Account;
use App\Models\Patient;
use App\Facades\Identity;
use App\Models\AccountUser;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use App\Models\Membership\UserApplication;

class UserServiceV2
{
    public function getUserByEmail($email)
    {
        return User::where("email", $email)->first();
    }

    public function getUserByUserId($userId)
    {
        return User::where("id", $userId)->first();
    }


    public function createLoginOnMembership(object $request, $agencyName)
    {
        $token = Identity::rawToken();
        $identityBaseUrl =  Config::get('axxess.identity.url');
        $url = "{$identityBaseUrl}/api/v1/invites/end-users/create";

        return Http::withToken($token)->post($url, [
            'email' => $request->email,
            'requestingOrganization' => $agencyName,
            'requestingIndividualFullName' => 'Family Portal',
            'requestingIndividualEmail' => 'familyportal@axxess.com',
            'requestingProduct' => 'Family Portal',
        ]);
    }


    public function createUserOnFamilyPortal(object $request, $loginId)
    {
        return User::firstOrCreate(
            [
                "email" => $request->email
            ],
            [
                "login_id" =>  $loginId,
                "name" => $request->name,
                "password" => '',
                "application" => $request->application_id,
            ]
        );
    }

    //
    public function firstOrCreateUser($email, $name, $accountId)
    {
        $user = User::where('email', $email)->first();

        if ($user) {
            return $user;
        }

        return $this->createUser($email, $name, $accountId);
    }

    //
    private function createUser($email, $name, $accountId)
    {
        $request = new stdClass();
        $request->email = $email;
        $account = Account::find($accountId);
        $result = $this->createLoginOnMembership($request, $account->agency_name);

        if (! $result->successful()) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Failed to create login on membership during update',
                'debug' => $result->body(),
            ], 500);
        }

        $loginId = $result->json()['subjectId'];

        $user = User::create([
            'email' => $email,
            'login_id' => $loginId,
            'name' => $name,
            'password' => '',
            'application' => $account->application_id,
        ]);

        $request = new stdClass();
        $request->agency_id = $account->agency_id;
        $request->application_id = $account->application_id;
        UserApplication::createUserApplicationOnMembership($request, $loginId, $user->id);

        return $user;
    }

    //
    public function firstOrCreateAccountUser($userId, $accountId)
    {
        return AccountUser::firstOrCreate([
            'user_id' => $userId,
            'account_id' => $accountId,
        ]);
    }

    /**
     * Get patient contact IDs.
     *
     * @param   string  $userId
     * @param   string  $applicationId
     * @return  array
     */
    public function getPatientContactIds($userId, $applicationId)
    {
        $accountIds = Account::where('application_id', $applicationId)->pluck('id');

        $accountUserIds = AccountUser::query()
            ->where('user_id', $userId)
            ->whereIn('account_id', $accountIds)
            ->pluck('id');

        if ($accountUserIds->isEmpty()) {
            Log::info("No Account User records found for {$userId} under the {$applicationId} application");
            return [];
        }

        $applicationIds = Patient::query()
            ->whereIn('account_user_id', $accountUserIds->toArray())
            ->pluck('emr_patient_contact_id');

        if ($applicationIds->isEmpty()) {
            return [];
        }

        return $applicationIds->toArray();
    }
}
