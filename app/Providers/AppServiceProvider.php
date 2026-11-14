<?php

namespace App\Providers;

use App\Database\PostgresConnection;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Connection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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

        // AI coach: each message is a paid Groq call, so cap it per account.
        RateLimiter::for('ai', function (Request $request) {
            $key = (string) ($request->user()?->id ?? $request->ip());

            return [
                Limit::perMinute(10)->by('ai-minute:'.$key),
                Limit::perDay(150)->by('ai-day:'.$key),
            ];
        });

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
