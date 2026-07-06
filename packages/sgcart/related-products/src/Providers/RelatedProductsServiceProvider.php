<?php

namespace SGCart\RelatedProducts\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

class RelatedProductsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load Package Migrations
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                \SGCart\RelatedProducts\Console\Commands\UninstallCommand::class,
            ]);
        }

        // Automate Installation (Migrations) inside boot phase
        $this->autoInstall();
    }

    /**
     * Programmatically runs package migrations if DB is connected and table is missing.
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

            // Check database connection and verify if related_products table is missing
            if (Schema::connection(null)->getConnection()->getPdo() && !Schema::hasTable('related_products')) {
                // Programmatically trigger package database migrations
                Artisan::call('migrate', [
                    '--path' => 'packages/sgcart/related-products/database/migrations',
                    '--force' => true
                ]);
            }
        } catch (\Exception $e) {
            // Silently fail if database is not set up/connected during early composer boot phase
        }
    }
}
