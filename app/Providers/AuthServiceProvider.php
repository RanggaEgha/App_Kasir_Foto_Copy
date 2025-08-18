<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
{
    // admin auto lolos semua ability
    Gate::before(fn(User $u,$a) => $u->role === 'admin' ? true : null);
    Gate::define('admin', fn(User $u) => $u->role === 'admin');
    Gate::define('kasir', fn(User $u) => in_array($u->role, ['kasir','admin'], true));
}
}
