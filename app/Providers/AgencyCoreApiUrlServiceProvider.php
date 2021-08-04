<?php

namespace App\Providers;

use Auth;
use Illuminate\Support\ServiceProvider;
use App\Services\AgencyCoreApiUrlService;

class AgencyCoreApiUrlServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('AgencyCoreApiService', function ($app) {
            $cluster = (Auth::getUser())
                ? Auth::getUser()->getCluster()
                : 60;    // TODO

            return (new AgencyCoreApiUrlService($cluster));
        });
    }

    public function boot()
    {
        //
    }
}
