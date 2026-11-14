<?php

namespace App\Providers;

use App\Database\PostgresConnection;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind booleans as 'true'/'false' on PostgreSQL (see App\Database\PostgresConnection).
        Connection::resolverFor('pgsql', fn ($pdo, $database, $prefix, $config) =>
            new PostgresConnection($pdo, $database, $prefix, $config));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
