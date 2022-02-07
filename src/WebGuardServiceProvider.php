<?php

namespace RistekUSDI\SSO;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use RistekUSDI\SSO\Auth\Guard\WebGuard;
use RistekUSDI\SSO\Auth\WebUserProvider;
use RistekUSDI\SSO\Middleware\Authenticate;
use RistekUSDI\SSO\Models\Web\User;
use RistekUSDI\SSO\Services\SSOService;

class WebGuardServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Load helpers file
        if (file_exists(__DIR__ . '/helpers.php')) {
            require __DIR__ . '/helpers.php';
        }

        // Views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'sso-laravel');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/sso-laravel')
        ]);

        // Routes
        $this->publishes([
            __DIR__.'/../stubs/routes/web.php' => base_path('routes/sso.php')
        ]);

        // Controllers
        $this->publishes([
            __DIR__.'/../stubs/App/Http/Controllers/SSO/AuthController.php' => app_path('Http/Controllers/SSO/AuthController.php')
        ]);

        // Configuration
        $config = __DIR__ . '/../config/sso.php';

        $this->publishes([$config => config_path('sso.php')], 'config');
        $this->mergeConfigFrom($config, 'sso');

        // Routes
        // $this->registerRoutes();

        // User Provider
        Auth::provider('imissu-web', function($app, array $config) {
            return new WebUserProvider($config['model']);
        });
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // SSO Web Guard
        Auth::extend('imissu-web', function ($app, $name, array $config) {
            $provider = Auth::createUserProvider($config['provider']);
            $web_guard_class = Config::get('sso.guards.web');
            return new $web_guard_class($provider, $app->request);
        });

        // Facades
        $this->app->bind('imissu-web', function($app) {
            return $app->make(SSOService::class);
        });

        // Middleware Group
        $this->app['router']->middlewareGroup('imissu-web', [
            StartSession::class,
            Authenticate::class,
        ]);

        // Bind for client data
        $this->app->when(SSOService::class)->needs(ClientInterface::class)->give(function() {
            return new Client(Config::get('sso.guzzle_options', []));
        });
    }

    /**
     * Register the authentication routes for keycloak.
     *
     * @return void
     */
    private function registerRoutes()
    {
        Route::group(['middleware' => 'web'], function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        });
    }
}
