<?php

namespace SGCart\Reporting\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class ReportingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/reporting.php', 'reporting');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'reporting');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/reporting.php' => config_path('reporting.php'),
            ], 'reporting-config');
        }

        $this->commands([
            \SGCart\Reporting\Console\Commands\UninstallCommand::class,
        ]);

        // Automate permission seeding on boot
        $this->autoInstall();
    }

    /**
     * Seeds the 'view reports' Spatie permission and assigns it to admin roles.
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

            // Only proceed if the permissions table exists
            if (!Schema::connection(null)->getConnection()->getPdo() || !Schema::hasTable('permissions')) {
                return;
            }

            if (class_exists(\Spatie\Permission\Models\Permission::class)) {
                $permission = \Spatie\Permission\Models\Permission::firstOrCreate(
                    ['name' => 'view reports', 'guard_name' => 'web']
                );

                $adminRoles = \Spatie\Permission\Models\Role::whereIn(
                    'name', ['Super Admin', 'super-admin', 'Admin', 'admin']
                )->get();

                foreach ($adminRoles as $role) {
                    if (!$role->hasPermissionTo($permission)) {
                        $role->givePermissionTo($permission);
                    }
                }
            }
        } catch (\Exception $e) {
            // Silently fail if database is not set up during early boot
        }
    }
}
