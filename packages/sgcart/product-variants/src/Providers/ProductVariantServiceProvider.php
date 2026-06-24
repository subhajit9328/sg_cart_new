<?php

namespace SGCart\ProductVariants\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

class ProductVariantServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind variants logic if needed
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \SGCart\ProductVariants\Console\Commands\UninstallCommand::class,
            ]);
        }

        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'product-variants');

        $this->autoInstall();

        // Listen for Composer pre-uninstall event
        $this->app['events']->listen('composer_package.sgcart/product-variants:pre_uninstall', function () {
            Artisan::call('sgcart:variants-uninstall');
        });
    }

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

            // Check connection and run package migrations dynamically if table is missing
            if (Schema::connection(null)->getConnection()->getPdo() && !Schema::hasTable('product_variants')) {
                Artisan::call('migrate', [
                    '--path' => 'packages/sgcart/product-variants/database/migrations',
                    '--force' => true
                ]);
            }

            // Seed colors and sizes if the tables are empty
            // if (Schema::hasTable('colors') && Schema::hasTable('sizes')) {
            //     Artisan::call('db:seed', [
            //         '--class' => \SGCart\ProductVariants\Database\Seeders\VariantDatabaseSeeder::class,
            //         '--force' => true,
            //     ]);
            // }

            // Seed Spatie manage permission for admin panel
            if (class_exists(\Spatie\Permission\Models\Permission::class)) {
                $permission = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'manage variants', 'guard_name' => 'web']);
                $role = \Spatie\Permission\Models\Role::whereIn('name', ['Super Admin', 'super-admin'])->first();
                if ($role && !$role->hasPermissionTo($permission)) {
                    $role->givePermissionTo($permission);
                }
            }
        } catch (\Exception $e) {
            // Silence exceptions during early boot phase
        }
    }
}
