<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureDatabaseIsUp
{
    public function __construct(private Application $app) {}

    public function handle(Request $request, Closure $next): Response
    {
        // Skip in console and during tests
        if ($this->app->runningInConsole() || $this->app->environment('testing')) {
            return $next($request);
        }

        // Skip health endpoints or static assets
        $path = $request->path();
        if (str_starts_with($path, '_debugbar') || str_starts_with($path, 'build/') || str_starts_with($path, 'vendor/')) {
            return $next($request);
        }

        // Cache the DB check result briefly to reduce overhead
        $healthy = Cache::remember('db_health_ok', 5, function () {
            try {
                DB::connection()->getPdo();
                return true;
            } catch (\Throwable $e) {
                return false;
            }
        });

        if (! $healthy) {
            // Clear cached flag to re-check on next request
            Cache::forget('db_health_ok');
            return response()->view('errors.db_unavailable', [
                'host' => config('database.connections.' . config('database.default') . '.host'),
                'port' => config('database.connections.' . config('database.default') . '.port'),
                'database' => config('database.connections.' . config('database.default') . '.database'),
            ], 503);
        }

        return $next($request);
    }
}

