<?php

namespace SGCart\Blog\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

class BlogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/blog.php', 'blog'
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/blog.php' => config_path('blog.php'),
            ], 'sgcart-blog-config');

            // Auto-publish config file if it does not exist
            $targetConfig = config_path('blog.php');
            if (!file_exists($targetConfig)) {
                @copy(__DIR__.'/../../config/blog.php', $targetConfig);
            }

            $this->commands([
                \SGCart\Blog\Console\Commands\UninstallCommand::class,
            ]);
        }

        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'blog');

        $this->autoInstall();

        // Listen for Composer pre-uninstall event
        $this->app['events']->listen('composer_package.sgcart/blog:pre_uninstall', function () {
            Artisan::call('sgcart:blog-uninstall');
        });
    }

    protected function autoInstall(): void
    {
        try {
            if ($this->app->runningInConsole()) {
                $command = $_SERVER['argv'][1] ?? null;
                if (in_array($command, [
                    'sgcart:blog-uninstall',
                    'migrate:rollback',
                    'migrate:reset',
                    'migrate:refresh',
                ]) || (is_string($command) && str_contains($command, 'uninstall'))) {
                    return;
                }
            }

            // Check connection and run package migrations dynamically if table is missing
            if (Schema::connection(null)->getConnection()->getPdo() && !Schema::hasTable('blog_posts')) {
                Artisan::call('migrate', [
                    '--path' => 'packages/sgcart/blog/database/migrations',
                    '--force' => true
                ]);
            }

            // Seed Spatie manage permission for admin panel
            if (class_exists(\Spatie\Permission\Models\Permission::class)) {
                $permission = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'manage blog', 'guard_name' => 'web']);
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
