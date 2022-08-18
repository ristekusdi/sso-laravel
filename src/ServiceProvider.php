<?php

namespace RistekUSDI\SSO;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use RistekUSDI\SSO\Auth\UserProvider;
use RistekUSDI\SSO\Auth\Guard\TokenGuard;
use RistekUSDI\SSO\Services\SSOService;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
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
            __DIR__.'/../resources/views' => resource_path('views/vendor/sso-laravel'),
        ]);

        // Routes
        $this->publishes([
            __DIR__.'/../stubs/routes/web.php' => base_path('routes/sso-web.php'),
            __DIR__.'/../stubs/routes/api.php' => base_path('routes/sso-token.php')
        ]);

        // Controllers and Requests
        $this->publishes([
            __DIR__.'/../stubs/App/Http/Controllers/SSO/AuthController.php' => app_path('Http/Controllers/SSO/AuthController.php'),
            __DIR__.'/../stubs/App/Http/Controllers/API/AuthController.php' => app_path('Http/Controllers/API/AuthController.php'),
            __DIR__.'/../stubs/App/Http/Requests/API/CredentialRequest.php' => app_path('Http/Requests/API/CredentialRequest.php'),
        ]);

        // Configuration
        $config = __DIR__ . '/../config/sso.php';

        $this->publishes([$config => config_path('sso.php')], 'config');
        $this->mergeConfigFrom($config, 'sso');

        // Web User Provider
        Auth::provider('imissu-web', function($app, array $config) {
            return new UserProvider($config['model']);
        });

        // Token User Provider
        Auth::provider('imissu-token', function($app, array $config) {
            return new UserProvider($config['model']);
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
            $web_guard_class = Config::get('sso.web.guard');
            return new $web_guard_class($provider, $app->request);
        });

        // SSO Token Guard
        Auth::extend('imissu-token', function ($app, $name, array $config) {
            return new TokenGuard(Auth::createUserProvider($config['provider']), $app->request);
        });

        // Middleware IMISSU Token
        $this->app['router']->middlewareGroup('imissu-token', [
            \RistekUSDI\SSO\Middleware\Token\Authenticate::class,
        ]);

        // Middleware IMISSU Web Role
        $this->app['router']->aliasMiddleware('imissu-token-role', 
            \RistekUSDI\SSO\Middleware\Web\Role::class
        );

        // Facades
        $this->app->bind('imissu-web', function($app) {
            return $app->make(SSOService::class);
        });

        // Middleware IMISSU Web
        $this->app['router']->middlewareGroup('imissu-web', [
            StartSession::class,
            \RistekUSDI\SSO\Middleware\Web\Authenticate::class,
        ]);

        // Middleware IMISSU Web Role
        $this->app['router']->aliasMiddleware('imissu-web-role', 
            \RistekUSDI\SSO\Middleware\Web\Role::class
        );

        // Bind for client data
        $this->app->when(SSOService::class)->needs(ClientInterface::class)->give(function() {
            return new Client(Config::get('sso.guzzle_options', []));
        });
    }
}
