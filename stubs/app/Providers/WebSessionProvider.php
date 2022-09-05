<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\WebSession;

class WebSessionProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('websession', function () {
            return new WebSession;
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
