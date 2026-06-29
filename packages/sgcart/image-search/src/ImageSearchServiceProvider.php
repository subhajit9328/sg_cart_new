<?php

namespace SGCart\ImageSearch;

use Illuminate\Support\ServiceProvider;

class ImageSearchServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/advance-search.php', 'image-search'
        );

        $this->app->singleton('image-analyzer', function ($app) {
            return new ImageAnalyzer;
        });

        $this->app->singleton('multi-object-detector', function ($app) {
            return new MultiObjectDetector;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\InstallCommand::class,
                Console\UninstallCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/advance-search.php' => config_path('image-search.php'),
            ], 'image-search-config');

            $this->publishes([
                __DIR__.'/../database/migrations/create_search_terms_table.php' => $this->getMigrationFileName(),
            ], 'image-search-migrations');
        }
    }

    /**
     * Get the target migration file name.
     */
    protected function getMigrationFileName(): string
    {
        $timestamp = date('Y_m_d_His');
        $migrations = $this->app->make('files')->glob(database_path('migrations/*_create_search_terms_table.php'));

        return reset($migrations) ?: database_path("migrations/{$timestamp}_create_search_terms_table.php");
    }
}
