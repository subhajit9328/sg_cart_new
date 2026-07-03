<?php

namespace SGCart\Hero\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use SGCart\Hero\Models\HeroImage;

class HeroServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/hero.php', 'hero'
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Load Routes, Migrations, and Views
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'hero');

        if ($this->app->runningInConsole()) {
            $this->commands([
                \SGCart\Hero\Console\Commands\InstallCommand::class,
                \SGCart\Hero\Console\Commands\UninstallCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../../config/hero.php' => config_path('hero.php'),
            ], 'hero-config');
        }

        // View Composer for welcome storefront page
        view()->composer('welcome', function ($view) {
            try {
                if (Schema::hasTable('hero_images')) {
                    $heroImages = HeroImage::orderBy('sort_order')->orderBy('id')->get();
                    $view->with(compact('heroImages'));
                }
            } catch (\Exception $e) {
                // Silently ignore errors during early migrations/bootstrap
            }
        });

        // Run auto install (migrations and permissions seeding)
        $this->autoInstall();
    }

    /**
     * Programmatically run migrations and seed permissions.
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

            // Check database connection and verify if hero_images table is missing
            if (Schema::connection(null)->getConnection()->getPdo() && !Schema::hasTable('hero_images')) {
                // Programmatically trigger package database migrations
                Artisan::call('migrate', [
                    '--path' => 'packages/sgcart/hero/database/migrations',
                    '--force' => true
                ]);
            }

            // Programmatically seed Spatie permissions
            if (class_exists(\Spatie\Permission\Models\Permission::class)) {
                $permission = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'manage hero section', 'guard_name' => 'web']);

                // Assign both permissions to Super Admin and Admin roles
                $adminRoles = \Spatie\Permission\Models\Role::whereIn('name', ['Super Admin', 'super-admin', 'Admin', 'admin'])->get();
                foreach ($adminRoles as $role) {
                    if (!$role->hasPermissionTo($permission)) {
                        $role->givePermissionTo($permission);
                    }
                }
            }
        } catch (\Exception $e) {
            // Silently fail if database is not set up/connected during early composer boot phase
        }
    }
}
