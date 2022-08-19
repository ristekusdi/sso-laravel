<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AppSession;

class AppSessionProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('appsession', function () {
            return new AppSession;
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
