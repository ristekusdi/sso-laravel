<?php
namespace RistekUSDI\SSO;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use RistekUSDI\SSO\Auth\Guard\TokenGuard;
use RistekUSDI\SSO\Auth\TokenUserProvider;
use RistekUSDI\SSO\Middleware\Token\Authenticate;
use RistekUSDI\SSO\Middleware\Token\Role;

class TokenGuardServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Configuration
        $config = __DIR__ . '/../config/sso-token.php';

        $this->publishes([$config => config_path('sso-token.php')], 'config');
        $this->mergeConfigFrom($config, 'sso-token');

        // Token User Provider
        Auth::provider('imissu-token', function($app, array $config) {
            return new TokenUserProvider($config['model']);
        });
    }

    public function register()
    {
        // SSO Token Guard
        Auth::extend('imissu-token', function ($app, $name, array $config) {
            return new TokenGuard(Auth::createUserProvider($config['provider']), $app->request);
        });

        // Middleware Group
        $this->app['router']->middlewareGroup('imissu-token', [
            Authenticate::class,
        ]);

        // Middleware IMISSU Web Role
        $this->app['router']->aliasMiddleware('imissu-token-role', Role::class);
    }
}