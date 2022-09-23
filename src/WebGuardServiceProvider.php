<?php

namespace RistekUSDI\SSO;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use RistekUSDI\SSO\Auth\UserProvider;
use RistekUSDI\SSO\Services\SSOService;

class WebGuardServiceProvider extends \Illuminate\Support\ServiceProvider
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
            // Controllers
            __DIR__.'/../stubs/app/Http/Controllers/SSO/Web/AuthController.php' => app_path('Http/Controllers/SSO/Web/AuthController.php'),

            // Models
            __DIR__.'/../stubs/app/Models/SSO/Web/User.php' => app_path('Models/SSO/Web/User.php'),

            // Views
            __DIR__.'/../stubs/resources/views/errors' => resource_path('views/sso-web/errors'),

            // Routes
            __DIR__.'/../stubs/routes/web.php' => base_path('routes/sso-web.php'),

            // Config
            __DIR__ . '/../stubs/config/sso.php' => config_path('sso.php')
        ], 'sso-laravel-web');

        // Advance Setup
        $this->publishes([
            // Web Guard
            __DIR__.'/../stubs/app/Services/Auth/Guard/WebGuard.php' => app_path('Services/Auth/Guard/WebGuard.php'),

            // Web session service provider
            __DIR__.'/../stubs/app/Facades/WebSession.php' => app_path('Facades/WebSession.php'),
            __DIR__.'/../stubs/app/Services/WebSession.php' => app_path('Services/WebSession.php'),
            __DIR__.'/../stubs/app/Providers/WebSessionProvider.php' => app_path('Providers/WebSessionProvider.php'),

            // Web session controller
            __DIR__.'/../stubs/app/Http/Controllers/SSO/Web/WebSessionController.php' => app_path('Http/Controllers/SSO/Web/WebSessionController.php'),
            __DIR__.'/../stubs/routes/web-session.php' => base_path('routes/web-session.php'),

            // SSO Web demo
            __DIR__.'/../stubs/app/Http/Controllers/SSO/Web/DemoController.php' => app_path('Http/Controllers/SSO/Web/DemoController.php'),
            __DIR__.'/../stubs/resources/views/demo.blade.php' => resource_path('views/sso-web/demo.blade.php'),
            __DIR__.'/../stubs/routes/sso-web-demo.php' => base_path('routes/sso-web-demo.php'),
        ], 'sso-laravel-web-advance');

        // Web User Provider
        Auth::provider('imissu-web', function($app, array $config) {
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

        // Middleware IMISSU Web Permission
        $this->app['router']->aliasMiddleware('imissu-web-permission', 
            \RistekUSDI\SSO\Middleware\Web\Permission::class
        );

        // Bind for client data
        $this->app->when(SSOService::class)->needs(ClientInterface::class)->give(function() {
            return new Client(Config::get('sso.guzzle_options', []));
        });
    }
}