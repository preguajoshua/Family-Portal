<?php

namespace App\Providers;

use App\Services\ApiManager;
use App\Services\QueryManager;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
	public function register()
	{
		$this->app->singleton('api.manager', function ($app) {
			return new ApiManager($app);
		});

		$this->app->singleton('query', function ($app) {
			return new QueryManager($app);
		});
	}

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
	public function boot()
	{
        // https://laravel-news.com/laravel-5-4-key-too-long-error
        Schema::defaultStringLength(191);

        if (App::environment('testing')) {
            /**
             * Register any custom database migration paths that use the "testing" database.
             */
            $this->loadMigrationsFrom([
                database_path('migrations/membership'),
            ]);
        }
	}
}
