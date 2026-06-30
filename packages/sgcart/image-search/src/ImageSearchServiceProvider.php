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
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'image-search');

        // Dynamically override config from database if settings table exists
        $isActive = true;
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('image_search_settings')) {
                $setting = Models\ImageSearchSetting::first();
                if ($setting) {
                    $isActive = $setting->is_active;
                    if ($setting->provider) {
                        config(['image-search.image_analyzer_provider' => $setting->provider]);
                        if ($setting->api_key) {
                            config(["ai.providers.{$setting->provider}.key" => $setting->api_key]);
                        }
                    }
                    if ($setting->model) {
                        config(['image-search.image_analyzer_model' => $setting->model]);
                    }
                }
            }
        } catch (\Exception $e) {
            // Silence exceptions during migrations or early bootstrap phases
        }
        config(['image-search.is_active' => $isActive]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\InstallCommand::class,
                Console\UninstallCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/advance-search.php' => config_path('image-search.php'),
            ], 'image-search-config');

            $this->publishes([
                __DIR__.'/../database/migrations/create_search_terms_table.php' => $this->getMigrationFileName('create_search_terms_table.php', 0),
                __DIR__.'/../database/migrations/create_image_search_settings_table.php' => $this->getMigrationFileName('create_image_search_settings_table.php', 1),
            ], 'image-search-migrations');
        }
    }

    /**
     * Get the target migration file name.
     */
    protected function getMigrationFileName(string $file, int $offset = 0): string
    {
        $timestamp = date('Y_m_d_His', time() + $offset);
        $baseName = str_replace('.php', '', $file);
        $migrations = $this->app->make('files')->glob(database_path("migrations/*_{$baseName}.php"));

        return reset($migrations) ?: database_path("migrations/{$timestamp}_{$file}");
    }
}
