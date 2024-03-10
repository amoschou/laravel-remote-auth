<?php

namespace AMoschou\RemoteAuth\App\Providers;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Routing\Router;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * The path to the package's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(Router $router): void
    {
        // Config

        $this->publishes([
            $this->path('config/public.php') => config_path('remote_auth.php'),
        ], 'remote-auth-config');

        // Migrations

        $this->loadMigrationsFrom($this->path('database/migrations'));

        // Views

        $this->loadViewsFrom($this->path('resources/views'), 'remote-auth');

        $this->publishes([
            $this->path('resources/views') => resource_path('views/vendor/remote-auth'),
        ], 'remote-auth-views');

        // Routes

        $this->loadRoutesFrom($this->path('routes/web.php'));
    }

    private function path(string $path): string
    {
        return __DIR__ . '/../../' . $path;
    }
}
