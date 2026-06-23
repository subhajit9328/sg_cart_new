<?php

namespace SGCart\Coupons\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use SGCart\Coupons\Services\CouponDiscountCalculator;

class CouponServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind the dynamic discount calculator singleton to the container
        $this->app->singleton('coupon.calculator', function ($app) {
            return new CouponDiscountCalculator();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \SGCart\Coupons\Console\Commands\UninstallCommand::class,
            ]);
        }

        // Load Package Components
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'coupons');

        // Automate Installation (Migrations & Permissions) inside boot phase
        $this->autoInstall();

        // Listen for Composer pre-uninstall event
        $this->app['events']->listen('composer_package.sgcart/coupons:pre_uninstall', function () {
            Artisan::call('sgcart:coupons-uninstall');
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
                    'migrate:rollback',
                    'migrate:reset',
                    'migrate:refresh',
                ])) {
                    return;
                }
            }

            // Check database connection and verify if coupons table is missing
            if (Schema::connection(null)->getConnection()->getPdo() && !Schema::hasTable('coupons')) {
                // Programmatically trigger package database migrations
                Artisan::call('migrate', [
                    '--path' => 'packages/sgcart/coupons/database/migrations',
                    '--force' => true
                ]);
            }

            // Programmatically seed Spatie permissions
            if (class_exists(\Spatie\Permission\Models\Permission::class)) {
                $permission = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'manage coupons', 'guard_name' => 'web']);
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
