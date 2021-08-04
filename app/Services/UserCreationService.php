<?php

namespace App\Services;

use Exception;
use App\Models\User;
use App\Models\Account;
use App\Models\Patient;
use App\Facades\Identity;
use App\Models\AccountUser;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\Membership\UserApplication;
use App\Exceptions\DuplicatePatientException;
use App\Models\Membership\AgencyApplications;

class UserCreationService
{
    //
    public function firstOrCreateUser($email, $name, $agencyName)
    {
        if ($user = User::where('email', $email)->first()) {
            return $user;
        }

        try {
            $loginId = $this->createLoginOnMembership($email, $agencyName);
        } catch (Exception $e) {
            throw new Exception($e);
        }

        return User::create([
            'email' => $email,
            'login_id' => $loginId,
            'name' => $name,
            'password' => '',
            'application' => 0,
        ]);
    }

    //
    private function createLoginOnMembership($email, $agencyName)
    {
        $token = Identity::rawToken();
        $url = config('axxess.identity.url') . '/api/v1/invites/end-users/create';

        $result = Http::withToken($token)->post($url, [
            'email' => $email,
            'requestingOrganization' => $agencyName,
            'requestingIndividualFullName' => 'Family Portal',
            'requestingIndividualEmail' => 'familyportal@axxess.com',
            'requestingProduct' => 'Family Portal',
        ]);

        if (! $result->successful()) {
            throw new Exception('Failed to create new user on Axxess Identity.');
        }

        return $result->json()['subjectId'];
    }

    //
    public function firstOrCreateAccount($agencyId, $agencyName, $applicationId)
    {
        $agencyId = ($applicationId == UserApplication::APP_HOSPICE)
            ? $this->lookupHospiceAgencyId($agencyId)
            : $agencyId;

        return Account::firstOrCreate(
            [
                'agency_id' => $agencyId,
                'application_id' => $applicationId,
            ],
            [
                'agency_name' => $agencyName,
            ],
        );
    }

    //
    private function lookupHospiceAgencyId($applicationAccountId)
    {
        $agencyApplication = AgencyApplications::query()
            ->select('AgencyId')
            ->where('ApplicationAccountId', $applicationAccountId)
            ->first();

        if (! $agencyApplication) {
            throw new Exception('Can not find Hospice Agency Id');
        }

        return $agencyApplication->AgencyId;
    }

    //
    public function firstOrCreateAccountUser(User $user, Account $account)
    {
        $accountUser =  AccountUser::firstOrCreate([
            'user_id' => $user->id,
            'account_id' => $account->id,
        ]);

        $this->createUserApplication($user, $account);

        return $accountUser;
    }

    //
    private function createUserApplication(User $user, Account $account)
    {
        return UserApplication::firstOrCreate(
            [
                'UserId' => $user->id,
                'LoginId' => $user->login_id,
                'AgencyId' => $account->agency_id,
            ],
            [
                'Created' => now(),
                'IsDeprecated' => false,
                'Application' => UserApplication::APP_FAMILY_PORTAL,
                'TitleType' => null,
                'Status' => 1,
            ],
        );
    }

    //
    public function firstOrCreatePatient($accountUserId, $patientId, $patientContactId)
    {
        if (Patient::where('emr_patient_id', $patientId)->where('emr_patient_contact_id', $patientContactId)->exists()) {
            throw new DuplicatePatientException;
        }

        return Patient::create([
            'account_user_id' => $accountUserId,
            'emr_patient_id' => $patientId,
            'emr_patient_contact_id' => $patientContactId,
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
