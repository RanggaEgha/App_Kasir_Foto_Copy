<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleOr404
{
    public function handle(Request $req, Closure $next, ...$roles)
    {
        $user = $req->user();
        if (!$user) return redirect()->route('login');

        if (!in_array($user->role, $roles, true)) {
            abort(404); // sembunyikan seolah tidak ada
        }
        return $next($req);
    }
}
