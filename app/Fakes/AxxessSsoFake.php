<?php

namespace App\Fakes;

use App\Models\User;
use App\Services\EncryptionService;
use App\Models\Membership\LoginToken;

class AxxessSsoFake
{
    /**
     * List the available users.
     *
     * @return  Response
     */
    public function index()
    {
        return view('fakes.axxess-sso', [
            'users' => User::all(),
        ]);
    }

    /**
     * Log the selected user in.
     *
     * @param   \App\Models\User  $user
     * @return  Response
     */
    public function login(User $user)
    {
        $loginToken = LoginToken::where('LoginId', $user->login_id)->firstOrFail();

        $encryptedToken = EncryptionService::encrypt($loginToken->Id);

        return redirect("/SingleSignOn?enc={$encryptedToken}");
    }
}
