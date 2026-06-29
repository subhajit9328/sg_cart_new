<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register core Payment Manager singleton
        $this->app->singleton(\App\Payments\Services\PaymentManager::class, function ($app) {
            return new \App\Payments\Services\PaymentManager();
        });
        
        // Also bind to 'payment.manager' for ease of access
        $this->app->alias(\App\Payments\Services\PaymentManager::class, 'payment.manager');
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

        // Register default Cash on Delivery Gateway
        if ($this->app->bound('payment.manager')) {
            $paymentManager = $this->app->make('payment.manager');
            $paymentManager->registerGateway(new \App\Payments\Gateways\CashOnDeliveryGateway());
            
            // Seed COD default record if connection exists and table is present
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('payment_methods')) {
                    \App\Models\PaymentMethod::firstOrCreate(
                        ['id' => 'cod'],
                        [
                            'name' => 'Cash on Delivery',
                            'description' => 'Pay with cash upon delivery of your order.',
                            'is_installed' => true,
                            'is_enabled' => true,
                            'config' => ['instructions' => 'Please have the exact amount of cash ready upon delivery.']
                        ]
                    );
                }
            } catch (\Exception $e) {
                // Silently ignore early migration connection exceptions
            }
        }

        // Programmatically seed manage payments permission
        try {
            if (class_exists(\Spatie\Permission\Models\Permission::class) && \Illuminate\Support\Facades\Schema::hasTable('permissions')) {
                $permission = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'manage payments', 'guard_name' => 'web']);
                $role = \Spatie\Permission\Models\Role::whereIn('name', ['Super Admin', 'super-admin'])->first();
                if ($role && !$role->hasPermissionTo($permission)) {
                    $role->givePermissionTo($permission);
                }
            }
        } catch (\Exception $e) {
            // Silently ignore early connection exceptions
        }
    }
}