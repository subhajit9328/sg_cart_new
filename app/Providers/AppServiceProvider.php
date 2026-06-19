<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Super-admin bypasses ALL permission checks automatically.
        // This means you never need to manually assign every permission
        // to the super-admin role — they always have full access.
        Gate::before(function ($user, $ability) {
            return ($user->hasRole('super-admin') || $user->hasRole('Super Admin')) ? true : null;
        });
    }
}