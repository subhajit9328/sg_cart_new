<?php

namespace SGCart\LogisticTracking\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Order;
use SGCart\LogisticTracking\Models\OrderTracking;

class LogisticTrackingServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Load package migrations
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        // Dynamically define the tracking relationship on the Order model
        Order::resolveRelationUsing('tracking', function ($orderModel) {
            return $orderModel->hasOne(OrderTracking::class, 'order_id');
        });

        // Register command
        $this->commands([
            \SGCart\LogisticTracking\Console\Commands\UninstallCommand::class,
        ]);

        if ($this->app->runningInConsole()) {
            // Allow publishing the migration
            $this->publishes([
                __DIR__.'/../../database/migrations/2026_06_29_000000_create_order_trackings_table.php' => database_path('migrations/2026_06_29_000000_create_order_trackings_table.php'),
            ], 'logistic-tracking-migrations');
        }

        // Listen for Composer pre-uninstall event
        $this->app['events']->listen('composer_package.sgcart/logistic-tracking:pre_uninstall', function () {
            \Illuminate\Support\Facades\Artisan::call('logistic-tracking:uninstall');
        });
    }
}
