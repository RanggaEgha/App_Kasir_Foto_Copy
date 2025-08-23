<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuditContext
{
    public function handle(Request $request, Closure $next)
    {
        // simpan ke singleton supaya bisa dipanggil di mana saja
        app()->instance('audit.context', [
            'batch_id'   => (string) Str::uuid(),
            'ip'         => $request->ip(),
            'url'        => $request->fullUrl(),
            'method'     => $request->method(),
            'user_agent' => (string) $request->userAgent(),
        ]);

        return $next($request);
    }
}
