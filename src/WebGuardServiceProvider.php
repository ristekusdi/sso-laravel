<?php

namespace RistekUSDI\SSO\Laravel;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use RistekUSDI\SSO\Laravel\Services\SSOService;
use RistekUSDI\SSO\Laravel\Auth\Guard\WebGuard;
use RistekUSDI\SSO\PHP\Auth\WebUserProvider;

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

        // Default
        $this->publishes([
            // Controllers
            __DIR__.'/../stubs/app/Http/Controllers/SSO/Web/AuthController.php' => app_path('Http/Controllers/SSO/Web/AuthController.php'),

            // Models
            __DIR__.'/../stubs/app/Models/SSO/Web/User.php' => app_path('Models/SSO/Web/User.php'),

            // Config
            __DIR__ . '/../stubs/config/sso.php' => config_path('sso.php'),
        ], 'sso-laravel-web');

        // Basic demo
        $this->publishes([
            // Controllers
            __DIR__.'/../stubs/app/Http/Controllers/SSO/Web/AuthController.php' => app_path('Http/Controllers/SSO/Web/AuthController.php'),
            __DIR__.'/../stubs/app/Http/Controllers/SSO/Web/DemoController.php' => app_path('Http/Controllers/SSO/Web/DemoController.php'),

            // Models
            __DIR__.'/../stubs/app/Models/SSO/Web/User.php' => app_path('Models/SSO/Web/User.php'),

            // Routes
            __DIR__.'/../stubs/routes/sso-web-demo.php' => base_path('routes/sso-web-demo.php'),

            // Config
            __DIR__ . '/../stubs/config/sso.php' => config_path('sso.php'),

            // Views
            __DIR__.'/../stubs/resources/views/demo.blade.php' => resource_path('views/sso-web/demo.blade.php'),
            __DIR__.'/../stubs/resources/views/basic.blade.php' => resource_path('views/sso-web/basic.blade.php'),
        ], 'sso-laravel-web-demo-basic');

        // Advance demo
        $this->publishes([
            // View
            __DIR__.'/../stubs/resources/views/advance.blade.php' => resource_path('views/sso-web/advance.blade.php'),
        ], 'sso-laravel-web-demo-advance');

        $this->publishes([
            // Config
            __DIR__ . '/../stubs/config/sso.php' => config_path('sso.php'),
        ], 'sso-laravel-web-config');

        $this->publishes([
            // Controllers
            __DIR__.'/../stubs/app/Http/Controllers/SSO/Web/SessionController.php' => app_path('Http/Controllers/SSO/Web/SessionController.php'),

            // Session facade, provider, and service
            __DIR__.'/../stubs/app/Facades/WebSession.php' => app_path('Facades/WebSession.php'),
            __DIR__.'/../stubs/app/Providers/WebSessionProvider.php' => app_path('Providers/WebSessionProvider.php'),
            __DIR__.'/../stubs/app/Services/WebSession.php' => app_path('Services/WebSession.php'),

            // Routes
            __DIR__.'/../stubs/routes/web-session.php' => base_path('routes/web-session.php'),
        ], 'sso-laravel-web-session');

        // SSO web route
        $this->publishes([
            // Routes
            __DIR__.'/../stubs/routes/sso-web.php' => base_path('routes/sso-web.php'),
        ], 'sso-laravel-web-route');

        // Web User Provider
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
        Auth::extend('imissu-web', function (Application $app, string $name, array $config) {
            $provider = Auth::createUserProvider($config['provider']);
            return new WebGuard($provider);
        });

        // Facades
        $this->app->bind('imissu-web', function($app) {
            return $app->make(SSOService::class);
        });

        // Middleware IMISSU Web
        $this->app['router']->middlewareGroup('imissu-web', [
            \RistekUSDI\SSO\Laravel\Middleware\Web\Authenticate::class,
        ]);

        // Middleware IMISSU Web Role
        $this->app['router']->aliasMiddleware('imissu-web.role', 
            \RistekUSDI\SSO\Laravel\Middleware\Web\Role::class
        );

        // Middleware IMISSU Web Permission
        $this->app['router']->aliasMiddleware('imissu-web.permission', 
            \RistekUSDI\SSO\Laravel\Middleware\Web\Permission::class
        );
    }
}
