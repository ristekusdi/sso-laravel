<?php

namespace RistekUSDI\SSO\Laravel;

use Illuminate\Support\Facades\Auth;
use RistekUSDI\SSO\Laravel\Auth\UserProvider;
use RistekUSDI\SSO\Laravel\Auth\Guard\TokenGuard;

class TokenGuardServiceProvider extends \Illuminate\Support\ServiceProvider
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

        $this->publishes([
            // Controllers and Requests
            __DIR__.'/../stubs/app/Http/Controllers/SSO/Token/AuthController.php' => app_path('Http/Controllers/SSO/Token/AuthController.php'),
            __DIR__.'/../stubs/app/Http/Requests/SSO/Token/CredentialRequest.php' => app_path('Http/Requests/SSO/Token/CredentialRequest.php'),
            
            // Routes
            __DIR__.'/../stubs/routes/api.php' => base_path('routes/sso-token.php'),

            // Models
            __DIR__.'/../stubs/app/Models/SSO/Token/User.php' => app_path('Models/SSO/Token/User.php'),

            // Config
            __DIR__ . '/../stubs/config/sso.php' => config_path('sso.php')
        ], 'sso-laravel-token');

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
        // SSO Token Guard
        Auth::extend('imissu-token', function ($app, $name, array $config) {
            return new TokenGuard(Auth::createUserProvider($config['provider']), $app->request);
        });

        // Middleware IMISSU Token
        $this->app['router']->middlewareGroup('imissu-token', [
            \RistekUSDI\SSO\Laravel\Middleware\Token\Authenticate::class,
        ]);

        // Middleware IMISSU Token Role
        $this->app['router']->aliasMiddleware('imissu-token-role', 
            \RistekUSDI\SSO\Laravel\Middleware\Token\Role::class
        );
        $this->app['router']->aliasMiddleware('imissu-token.role', 
            \RistekUSDI\SSO\Laravel\Middleware\Token\Role::class
        );

        // Middleware IMISSU Token Client Role
        $this->app['router']->aliasMiddleware('imissu-token.client-role', 
            \RistekUSDI\SSO\Laravel\Middleware\Token\ClientRole::class
        );
    }
}
