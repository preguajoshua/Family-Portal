<?php

namespace App\Http\Controllers\Front;

use App\Facades\Query;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function index()
    {
        $user = Auth::getUser();

        $clients = Query::pagination('PatientsQuery', [
            'userId' => $user->id,
            'loginId' => $user->getLoginId(),
            'action' => 'load',
        ]);

        return response()->json($clients->toArray());
    }
}
