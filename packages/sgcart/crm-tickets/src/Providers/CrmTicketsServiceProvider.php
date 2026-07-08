<?php

namespace SGCart\CrmTickets\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

class CrmTicketsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/crm-tickets.php', 'crm-tickets');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load Package Components
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'crm-tickets');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/crm-tickets.php' => config_path('crm-tickets.php'),
            ], 'crm-tickets-config');

            $this->commands([
                \SGCart\CrmTickets\Console\Commands\InstallCommand::class,
                \SGCart\CrmTickets\Console\Commands\UninstallCommand::class,
            ]);
        }

        // Automatically publish the HasTickets trait stub if it doesn't exist
        $this->publishTraitStub();

        // Automate Installation (Migrations & Permissions) inside boot phase
        $this->autoInstall();

        // Register Ticket & TicketComment Observers
        if (class_exists(\SGCart\CrmTickets\Models\TicketComment::class)) {
            \SGCart\CrmTickets\Models\TicketComment::observe(\SGCart\CrmTickets\Observers\TicketCommentObserver::class);
        }
        if (class_exists(\SGCart\CrmTickets\Models\Ticket::class)) {
            \SGCart\CrmTickets\Models\Ticket::observe(\SGCart\CrmTickets\Observers\TicketObserver::class);
        }
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

            // Check database connection and verify if ticket_statuses table is missing
            if (Schema::connection(null)->getConnection()->getPdo() && !Schema::hasTable('ticket_statuses')) {
                // Programmatically trigger package database migrations
                Artisan::call('migrate', [
                    '--path' => 'packages/sgcart/crm-tickets/database/migrations',
                    '--force' => true
                ]);
            }

            // Programmatically seed Spatie permissions
            if (class_exists(\Spatie\Permission\Models\Permission::class)) {
                // Create permissions
                $viewPermission = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'view tickets', 'guard_name' => 'web']);
                $managePermission = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'manage tickets', 'guard_name' => 'web']);

                // Assign both permissions to Super Admin and Admin roles
                $adminRoles = \Spatie\Permission\Models\Role::whereIn('name', ['Super Admin', 'super-admin', 'Admin', 'admin'])->get();
                foreach ($adminRoles as $role) {
                    if (!$role->hasPermissionTo($viewPermission)) {
                        $role->givePermissionTo($viewPermission);
                    }
                    if (!$role->hasPermissionTo($managePermission)) {
                        $role->givePermissionTo($managePermission);
                    }
                }

                // Assign view-only permission to all other roles
                $otherRoles = \Spatie\Permission\Models\Role::whereNotIn('name', ['Super Admin', 'super-admin', 'Admin', 'admin'])->get();
                foreach ($otherRoles as $role) {
                    if (!$role->hasPermissionTo($viewPermission)) {
                        $role->givePermissionTo($viewPermission);
                    }
                }
            }
        } catch (\Exception $e) {
            // Silently fail if database is not set up/connected during early composer boot phase
        }
    }

    /**
     * Programmatically publishes the HasTickets trait stub if not present in the app.
     */
    protected function publishTraitStub(): void
    {
        try {
            $traitsDir = app_path('Traits');
            if (!\Illuminate\Support\Facades\File::exists($traitsDir)) {
                \Illuminate\Support\Facades\File::makeDirectory($traitsDir, 0755, true);
            }

            $traitPath = $traitsDir . '/HasTickets.php';

            if (!\Illuminate\Support\Facades\File::exists($traitPath)) {
                $stub = <<<'PHP'
<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasTickets
{
    /**
     * Get all tickets linked to this model (polymorphic).
     */
    public function tickets(): MorphMany
    {
        if (class_exists(\SGCart\CrmTickets\Models\Ticket::class)) {
            return $this->morphMany(\SGCart\CrmTickets\Models\Ticket::class, 'ticketable');
        }

        // Fallback: Return a dummy MorphMany relationship pointing to self
        // but constrained to return an empty collection so that it is safe
        // even if the package is removed from the codebase.
        return $this->morphMany(self::class, 'ticketable')->whereRaw('1 = 0');
    }
}
PHP;
                \Illuminate\Support\Facades\File::put($traitPath, $stub);
            }
        } catch (\Exception $e) {
            // Silently ignore failures if file system is read-only
        }
    }
}
