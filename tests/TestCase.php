<?php

namespace Tests;

use App\Models\User;
use App\Models\Client;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Sushi test cache path.
     *
     * @var  string
     */
    protected $sushiTestCachePath = '';

    /**
     * Refresh the Sushi databases.
     *
     * @return  void
     */
    protected function refreshSushiDatabase()
    {
        $this->sushiTestCachePath = storage_path('framework/cache/testing');

        config(['sushi.cache-path' => $this->sushiTestCachePath]);

        $this->clearSushiTestCache();
    }

    /**
     * Clear Sushi test cache.
     *
     * @return  void
     */
    protected function clearSushiTestCache()
    {
        File::cleanDirectory($this->sushiTestCachePath);
    }

    /**
     * Log in.
     *
     * @param   App\Models\Client  $client
     * @param   App\Models\User    $user
     * @return  void
     */
    protected function login($client = null, $user = null)
    {
        $client = $client ?? Client::factory()->make();
        $user = $user ?? User::factory()->make();

        session()->put('client', $client->toArray());

        Auth::login($user);
    }

    /**
     * Log out.
     */
    protected function logout()
    {
        Auth::logout();
    }

    /**
     * Set the users customer ID.
     *
     * @param  integer  $customerId
     * @return  void
     */
    protected function setUsersCustomer($customerId)
    {
        $user = Auth::getUser();

        $user->customer_id = $customerId;
    }

    /**
     * Get the users customer ID.
     *
     * @return  integer
     */
    protected function getUsersCustomer()
    {
        $user = Auth::getUser();

        return $user->customer_id;
    }
}
