<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserIsActive
{
    public function handle(Request $r, Closure $next)
    {
        if ($r->user() && !$r->user()->is_active) {
            auth()->logout();
            return redirect()->route('login')->withErrors(['email'=>'Akun dinonaktifkan.']);
        }
        return $next($r);
    }
}
