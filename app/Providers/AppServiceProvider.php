<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;

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

        // Share the dynamic database cart item count with all storefront layouts
        View::composer('layouts.store', function ($view) {
            $cartModel = \App\Models\Cart::getActiveCart();
            $cartCount = $cartModel ? $cartModel->items()->sum('quantity') : 0;
            $view->with('cartCount', $cartCount);
        });
    }
}