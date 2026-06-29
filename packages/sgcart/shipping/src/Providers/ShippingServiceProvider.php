<?php

namespace SGCart\Shipping\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

class ShippingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \SGCart\Shipping\Console\Commands\UninstallCommand::class,
            ]);
        }

        // Load Package Components
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'shipping');

        // Automate Installation (Migrations & Permissions) inside boot phase
        $this->autoInstall();

        // Listen for Composer pre-uninstall event
        $this->app['events']->listen('composer_package.sgcart/shipping:pre_uninstall', function () {
            Artisan::call('sgcart:shipping-uninstall');
        });
    }

    /**
     * Programmatically runs package migrations and seeds permissions if DB is connected.
     */
    protected function autoInstall(): void
    {
        try {
            if ($this->app->runningInConsole()) {
                $command = $_SERVER['argv'][1] ?? null;
                if (in_array($command, [
                    'sgcart:variants-uninstall',
                    'sgcart:coupons-uninstall',
                    'sgcart:shipping-uninstall',
                    'migrate:rollback',
                    'migrate:reset',
                    'migrate:refresh',
                ])) {
                    return;
                }
            }

            // Check database connection and verify if shipping_rates table is missing
            if (Schema::connection(null)->getConnection()->getPdo() && (!Schema::hasTable('shipping_rates') || !Schema::hasTable('shipping_settings'))) {
                // Programmatically trigger package database migrations
                Artisan::call('migrate', [
                    '--path' => 'packages/sgcart/shipping/database/migrations',
                    '--force' => true
                ]);
            }

            // Seed default settings if settings table exists
            if (Schema::connection(null)->getConnection()->getPdo() && Schema::hasTable('shipping_settings')) {
                \SGCart\Shipping\Models\ShippingSetting::firstOrCreate(
                    ['key' => 'shipping_selection_mode'],
                    ['value' => 'user_choice']
                );
            }

            // Programmatically seed Spatie permissions
            if (class_exists(\Spatie\Permission\Models\Permission::class)) {
                $permission = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'manage shipping', 'guard_name' => 'web']);
                $role = \Spatie\Permission\Models\Role::whereIn('name', ['Super Admin', 'super-admin'])->first();
                if ($role && !$role->hasPermissionTo($permission)) {
                    $role->givePermissionTo($permission);
                }
            }
        } catch (\Exception $e) {
            // Silently fail if database is not set up/connected during early composer boot phase
        }
    }
}
