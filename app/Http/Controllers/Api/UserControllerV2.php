<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Account;
use App\Models\Patient;
use App\Rules\EmrExists;
use App\Models\AccountUser;
use Illuminate\Http\Request;
use App\Services\UserServiceV2;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Membership\UserApplication;

class UserControllerV2 extends Controller
{
    protected $userService;

    public function __construct(UserServiceV2 $userService)
    {
        $this->userService = $userService;
    }

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

        // check if user exists
        $user = $this->userService->getUserByEmail($request->email);
        if (!$user && !is_object($user)) {
            return $this->createNewUser($request, $request->agency_name);
        };

        // check the account user table
        $accountUser = AccountUser::where("user_id", $user->id)->first();
        if (!$accountUser && !is_object($accountUser)) {
            return $this->createAccountWithExistingUser($request, $user);
        };

        // check patient table
        $patient = Patient::fetchPatient($request->patient_id, $request->patient_contact_id);
        if (!$patient && !is_object($patient)) {
            try {
                $patient = Patient::createPatientOnFamilyPortal($request, $accountUser->id);
            } catch (\Throwable $e) {
                return $this->jsonResponseWithError($e->getMessage());
            }

            return response()->json(
                [
                    'loginId' => $user->login_id,
                    "message" => "Patient added to the user successfully"
                ],
            );
        }

        return $this->jsonResponseWithData("User already registered", 409);
    }

    //
    public function createAccountWithExistingUser(object $request, object $user)
    {
        try {
            $user = DB::transaction(function () use ($request, $user) {
                $account = Account::createAccountOnFamilyPortal($request);
                $accountUser = AccountUser::createAccountUserOnFamilyPortal($user->id, $account->id, $request, $user->login_id);
                Patient::createPatientOnFamilyPortal($request, $accountUser->id);
                return $user;
            }, 1);
        } catch (\Throwable $e) {
            return $this->jsonResponseWithError($e->getMessage());
        }

        return response()->json(
            [
                'loginId' => $user->login_id,
                "message" => "Patient added to the user successfully"
            ],
        );
    }


    public function createNewUser(object $request, $agencyName)
    {
        $login = $this->userService->createLoginOnMembership($request, $agencyName);
        if (!$login->successful()) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Failed to create login on membership',
                'debug' => $login->body(),
            ], 500);
        }
        $loginId = $login->json()['subjectId'];

        try {
            $user = DB::transaction(function () use ($request, $loginId) {
                $user = $this->userService->createUserOnFamilyPortal($request, $loginId);
                $account = Account::createAccountOnFamilyPortal($request);
                $accountUser = AccountUser::createAccountUserOnFamilyPortal($user->id, $account->id, $request, $user->login_id);
                Patient::createPatientOnFamilyPortal($request, $accountUser->id);
                return $user;
            }, 1);
        } catch (\Throwable $e) {
            Log::channel('teams')->error($e->getMessage());
            Log::channel('teams')->info('Rolled back createNewUser');
            Log::channel('teams')->debug($request->all());

            return response()->json([
                'status' => 'fail',
                'message' => 'Failed to create new user',
                'debug' => $e->getMessage(),
            ], 500);
        }


        return response()->json(
            [
                'loginId' => $user->login_id,
                "message" => "User Created Successfully"
            ],
        );
    }

    private function createNewUserOnFamilyPortal(object $request,  object $user, $newLoginId)
    {
        $userCreationRequest = $request;
        $userCreationRequest->name = $user->name;
        $userCreationRequest->application = $request->application_id;
        return $this->userService->createUserOnFamilyPortal($userCreationRequest, $newLoginId);
    }

    private function movePatientToExistingAccount($request, $oldAccountUser, $newAccount, $userId)
    {
        $newAccountUser =  AccountUser::where('user_id', $userId)->where('account_id', $newAccount->id)->first();
        Patient::updatePatientAccountUserId($request->patient_id, $request->patient_contact_id, $oldAccountUser->id, $newAccountUser->id);

        return response()->json($newAccountUser, 200);
    }
}
