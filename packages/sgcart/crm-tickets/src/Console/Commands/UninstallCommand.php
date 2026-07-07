<?php

namespace SGCart\CrmTickets\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class UninstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sgcart:crm-tickets-uninstall';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Roll back CRM tickets migrations, remove migration records, and perform package clean up';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting SGCart CRM Tickets uninstallation...');

        try {
            $migrationNames = [
                '2026_07_02_100001_create_ticket_statuses_table',
                '2026_07_02_100002_create_tickets_table',
                '2026_07_02_100003_create_ticket_comments_table',
                '2026_07_02_100004_create_ticket_attachments_table',
            ];

            // 1. Rollback migrations if they have ran
            $ranMigrations = DB::table('migrations')
                ->whereIn('migration', $migrationNames)
                ->pluck('migration')
                ->toArray();

            if (!empty($ranMigrations)) {
                $this->comment('Found package migrations to rollback: ' . implode(', ', $ranMigrations));

                // Temporarily set the batch of these migrations to a unique batch number
                DB::table('migrations')
                    ->whereIn('migration', $ranMigrations)
                    ->update(['batch' => 999999]);

                // Determine path to run rollback
                $migrationsPath = realpath(__DIR__ . '/../../../database/migrations');
                if ($migrationsPath) {
                    $relativePath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $migrationsPath);
                    Artisan::call('migrate:rollback', [
                        '--path' => $relativePath,
                        '--batch' => 999999,
                        '--force' => true,
                    ]);
                    $this->info(Artisan::output());
                }
            } else {
                $this->info('No package migrations were found in the ran migrations database.');
            }

            // 2. Clean up migration table records explicitly
            DB::table('migrations')->whereIn('migration', $migrationNames)->delete();
            $this->info('Removed migration registry entries.');

            // 3. Remove published migration files if present
            $publishedMigrationsPath = database_path('migrations');
            if (File::exists($publishedMigrationsPath)) {
                foreach ($migrationNames as $migrationName) {
                    $pattern = $publishedMigrationsPath . DIRECTORY_SEPARATOR . '*' . $migrationName . '.php';
                    $matchedFiles = File::glob($pattern);
                    foreach ($matchedFiles as $file) {
                        File::delete($file);
                        $this->info('Removed published migration file: ' . basename($file));
                    }
                }
            }

            // 4. Remove Spatie permissions
            if (class_exists(\Spatie\Permission\Models\Permission::class)) {
                $permissions = \Spatie\Permission\Models\Permission::whereIn('name', ['view tickets', 'manage tickets'])->get();
                foreach ($permissions as $permission) {
                    $permission->delete();
                    $this->info("Removed permission: {$permission->name}");
                }
            }

            // 5. Remove published configuration file
            $configPath = config_path('crm-tickets.php');
            if (File::exists($configPath)) {
                File::delete($configPath);
                $this->info('Removed configuration file: crm-tickets.php');
            }

            // 6. Note: We do NOT delete the local HasTickets trait stub
            // so that the Order model does not break when the package is uninstalled.

            $this->info('SGCart CRM Tickets uninstallation completed successfully.');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('An error occurred during uninstallation: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
