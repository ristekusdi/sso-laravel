<?php

namespace RistekUSDI\SSO;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use RistekUSDI\SSO\Auth\Guard\WebGuard;
use RistekUSDI\SSO\Auth\WebUserProvider;
use RistekUSDI\SSO\Middleware\Authenticated;
use RistekUSDI\SSO\Middleware\Can;
use RistekUSDI\SSO\Models\User;
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
        // Configuration
        $config = __DIR__ . '/../config/sso.php';

        $this->publishes([$config => config_path('sso.php')], 'config');
        $this->mergeConfigFrom($config, 'sso');

        // User Provider
        Auth::provider('keycloak-users', function($app, array $config) {
            return new WebUserProvider($config['model']);
        });

        // Gate
        Gate::define('keycloak-web', function ($user, $roles, $resource = '') {
            return $user->hasRole($roles, $resource) ?: null;
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
        Auth::extend('keycloak-web', function ($app, $name, array $config) {
            $provider = Auth::createUserProvider($config['provider']);
            return new WebGuard($provider, $app->request);
        });

        // Facades
        $this->app->bind('sso-web', function($app) {
            return $app->make(SSOService::class);
        });

        // Routes
        $this->registerRoutes();

        // Middleware Group
        $this->app['router']->middlewareGroup('keycloak-web', [
            StartSession::class,
            Authenticated::class,
        ]);

        // Add Middleware "keycloak-web-can"
        $this->app['router']->aliasMiddleware('keycloak-web-can', Can::class);

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
        $defaults = [
            'login' => 'login',
            'logout' => 'logout',
            'register' => 'register',
            'callback' => 'callback',
        ];

        $routes = Config::get('sso.routes', []);
        $routes = array_merge($defaults, $routes);

        // Register Routes
        $router = $this->app->make('router');

        if (! empty($routes['login'])) {
            $router->middleware('web')->get($routes['login'], 'RistekUSDI\SSO\Controllers\AuthController@login')->name('sso.login');
        }

        if (! empty($routes['logout'])) {
            $router->middleware('web')->get($routes['logout'], 'RistekUSDI\SSO\Controllers\AuthController@logout')->name('sso.logout');
        }

        if (! empty($routes['register'])) {
            $router->middleware('web')->get($routes['register'], 'RistekUSDI\SSO\Controllers\AuthController@register')->name('sso.register');
        }

        if (! empty($routes['callback'])) {
            $router->middleware('web')->get($routes['callback'], 'RistekUSDI\SSO\Controllers\AuthController@callback')->name('sso.callback');
        }
    }
}
