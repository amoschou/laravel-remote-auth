<?php
 
namespace AMoschou\RemoteAuth\App\Providers;
 
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
 
class ServiceProvider extends BaseServiceProvider
{
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
    public function boot(): void
    {
        // Config

        $this->publishes([
            $this->path('config/public.php') => config_path('remote_auth.php'),
        ], 'remote-auth-config');

        // Migrations

        $this->loadMigrationsFrom($this->path('database/migrations'));

        // Views

        $this->publishes([
            $this->path('resources/views') => resource_path('views/vendor/laravel-remote-auth'),
        ], 'remote-auth-views');

    }

    private function path($path): string
    {
        return __DIR__ . '/../../' . $path;
    }
}