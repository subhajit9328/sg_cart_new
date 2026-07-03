<?php

namespace SGCart\Reporting\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UninstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sgcart:reporting-uninstall';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove the Reporting package permissions and clean up';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting SGCart Reporting uninstallation...');

        try {
            if (class_exists(\Spatie\Permission\Models\Permission::class)) {
                $permission = \Spatie\Permission\Models\Permission::where('name', 'view reports')
                    ->where('guard_name', 'web')
                    ->first();

                if ($permission) {
                    // Revoke from all roles that have it
                    $roles = \Spatie\Permission\Models\Role::all();
                    foreach ($roles as $role) {
                        if ($role->hasPermissionTo($permission)) {
                            $role->revokePermissionTo($permission);
                            $this->comment("Revoked 'view reports' from role: {$role->name}");
                        }
                    }

                    // Also revoke from any users who have it directly
                    DB::table('model_has_permissions')
                        ->where('permission_id', $permission->id)
                        ->delete();

                    $permission->delete();
                    $this->info("Deleted permission: 'view reports'");
                } else {
                    $this->info("Permission 'view reports' was not found — skipping.");
                }
            }

            $this->info('SGCart Reporting uninstallation completed successfully.');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('An error occurred during uninstallation: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
