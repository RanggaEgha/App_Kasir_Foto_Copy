<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $adminEmail = config('auth.admin_email', env('ADMIN_EMAIL'));

        if (!$user || !$adminEmail || strcasecmp($user->email, $adminEmail) !== 0) {
            abort(403, 'Only admin is allowed.');
        }
        return $next($request);
    }
}
