<?php

namespace SGCart\Reviews\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

class ReviewsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Merge configuration if any
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load Package Components
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'reviews');

        // Automate Installation (Migrations & Permissions) inside boot phase
        $this->autoInstall();
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
                    'migrate:rollback',
                    'migrate:reset',
                    'migrate:refresh',
                ]) || (is_string($command) && str_contains($command, 'uninstall'))) {
                    return;
                }
            }

            // Check database connection and verify if review_statuses table is missing
            if (Schema::connection(null)->getConnection()->getPdo() && !Schema::hasTable('review_statuses')) {
                // Programmatically trigger package database migrations
                Artisan::call('migrate', [
                    '--path' => 'packages/sgcart/reviews/database/migrations',
                    '--force' => true
                ]);
            }

            // Programmatically seed Spatie permissions
            if (class_exists(\Spatie\Permission\Models\Permission::class)) {
                $permission = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'manage reviews', 'guard_name' => 'web']);
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
