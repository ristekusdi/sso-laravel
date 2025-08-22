<?php

namespace RistekUSDI\SSO\Laravel;

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
        $this->publishes($this->getDefaultStubs(), 'sso-laravel');

        // Demo
        $this->publishes([
            ...$this->getDefaultStubs(), 
            
            __DIR__.'/../stubs/resources/views/sso-web/demo.blade.php' => resource_path('views/sso-web/demo.blade.php'),
            __DIR__.'/../stubs/routes/sso-web-demo.php' => base_path('routes/sso-web-demo.php'),
        ], 'sso-laravel.demo');

        // Config
        $this->publishes($this->getConfigStubs(), 'sso-laravel.config');

        // Routes
        $this->publishes($this->getRouteStubs(), 'sso-laravel.route');

        // WebSession
        $this->publishes($this->getWebSessionStubs(), 'sso-laravel.web-session');

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
        Auth::extend('imissu-web', function ($app, $name, array $config) {
            $provider = Auth::createUserProvider($config['provider']);
            return new WebGuard($provider, $app->request);
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

    private function getDefaultStubs()
    {
        return [
            // Controllers
            ...$this->getControllerStubs(),

            // Models
            ...$this->getModelStubs(),

            // Config
            ...$this->getConfigStubs(),

            // Routes
            ...$this->getRouteStubs(),
            
            // WebSession
            ...$this->getWebSessionStubs(),
        ];
    }

    private function getControllerStubs()
    {
        return [
            __DIR__.'/../stubs/app/Http/Controllers/SSO/Web/AuthController.php' => app_path('Http/Controllers/SSO/Web/AuthController.php'),
        ];
    }

    private function getModelStubs()
    {
        return [ 
            __DIR__.'/../stubs/app/Models/SSO/Web/User.php' => app_path('Models/SSO/Web/User.php'),
        ];
    }

    private function getConfigStubs()
    {
        return [
            __DIR__ . '/../stubs/config/sso.php' => config_path('sso.php'),
        ];
    }

    private function getRouteStubs()
    {
        return [
            __DIR__.'/../stubs/routes/sso-web.php' => base_path('routes/sso-web.php'),
        ];
    }

    private function getWebSessionStubs()
    {
        return [
            // Controllers
            __DIR__.'/../stubs/app/Http/Controllers/SSO/Web/SessionController.php' => app_path('Http/Controllers/SSO/Web/SessionController.php'),

            // Middleware
            __DIR__.'/../stubs/app/Http/Middleware/InitWebSession.php' => app_path('Http/Middleware/InitWebSession.php'),

            // Session facade, provider, and service
            __DIR__.'/../stubs/app/Facades/WebSession.php' => app_path('Facades/WebSession.php'),
            __DIR__.'/../stubs/app/Providers/WebSessionProvider.php' => app_path('Providers/WebSessionProvider.php'),
            __DIR__.'/../stubs/app/Services/WebSession.php' => app_path('Services/WebSession.php'),

            // Routes
            __DIR__.'/../stubs/routes/web-session.php' => base_path('routes/web-session.php'),
        ];
    }
}
