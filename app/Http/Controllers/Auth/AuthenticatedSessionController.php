<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Facades\Query;
use App\Models\Patient;
use App\Models\AccountUser;
use Illuminate\Http\Request;
use App\Models\Membership\Login;
use App\Services\EncryptionService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Membership\LoginToken;
use App\Providers\RouteServiceProvider;

class AuthenticatedSessionController extends Controller
{
    /**
     * Log the user in to the application.
     *
     * @param   Request  $request
     * @return  [type]
     */
    public function store(Request $request)
    {
        $token = $this->getToken($request->get('enc'));

        $loginToken = LoginToken::find($token);

        if (!$loginToken || !$loginToken->validApplicationId()) {
            Log::channel('teams')->error('Failed to find login token record.', ['token' => $token, 'enc' => $request->get('enc')]);
            abort(500);
        }

        $login = Login::find($loginToken->getLoginId());

        if (!$login) {
            Log::channel('teams')->error('Failed to find login record.', ['loginId' => $loginToken->getLoginId()]);
            abort(500);
        }

        $user = User::where('login_id', $login->getId())->first();

        if (!$user) {
            Log::channel('teams')->error('Failed to find user record.', ['loginId' => $login->getId()]);
            abort(500);
        }

        $accountUser = AccountUser::where('user_id', $user->id)->first();

        if (!$accountUser) {
            Log::channel('teams')->error('Failed to find account user record.', ['userId' => $user->id]);
            abort(500);
        }

        $patients = Patient::query()
            ->where('account_user_id', $accountUser->id)
            ->pluck('emr_patient_contact_id');

        if ($patients->isEmpty()) {
            // TODO - handle this
            Log::channel('teams')->error('Failed to find patient record(s).', ['accountUserId' => $accountUser->id]);

            //
            $request->session()->put('clients', []);
            return redirect(RouteServiceProvider::HOME);
        }

        $clients = Query::fetch('PatientsQuery', [
            'userId' => $user->id,
            'loginId' => $loginToken->getLoginId(),
            'action' => 'loadAndCache',
        ]);

        if ($clients->count() < 1) {
            Log::channel('teams')->error('Failed to find client record(s).', [
                'token' => $token,
                'displayName' => $login->DisplayName,
                'patients' => $patients->toArray(),
            ]);

            abort(500);
        }

        Auth::login($user, $remember = true);

        $request->session()->put('clients', $clients->toArray());

        if ($clients->count() > 1) {
            return redirect(RouteServiceProvider::HOME);
        }

        $request->session()->put('client', $clients->offsetGet(0)->toArray());

        return redirect()->route('home');
    }

    //
    private function getToken($enc)
    {
        $encryptedToken = str_replace(' ', '+', $enc);

        $decryptedToken = EncryptionService::decrypt($encryptedToken);

        $token = str_replace('tokenId=', '', $decryptedToken);

        return $token;
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        if ($response = $this->loggedOut($request)) {
            return $response;
        }

        return $request->wantsJson()
            ? new JsonResponse([], 204)
            : redirect('/');
    }

    /**
     * The user has logged out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    protected function loggedOut(Request $request)
    {
        $request->session()->forget([
            'client', 'clients'
        ]);

        return response()->json(['status' => 'success']);
    }
}
