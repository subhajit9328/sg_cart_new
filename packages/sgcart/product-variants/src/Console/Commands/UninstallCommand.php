<?php

namespace SGCart\ProductVariants\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class UninstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sgcart:variants-uninstall';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Roll back product variants migrations and perform package clean up';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting SGCart Product Variants uninstallation...');

        try {
            $migrationsPath = realpath(__DIR__ . '/../../../database/migrations');
            if ($migrationsPath) {
                $migrationFiles = glob($migrationsPath . '/*.php');
                $migrationNames = array_map(function ($file) {
                    return basename($file, '.php');
                }, $migrationFiles);

                if (!empty($migrationNames)) {
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

                        // Run migrate:rollback
                        $relativePath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $migrationsPath);
                        Artisan::call('migrate:rollback', [
                            '--path' => $relativePath,
                            '--batch' => 999999,
                            '--force' => true,
                        ]);

                        $this->info(Artisan::output());
                    } else {
                        $this->info('No package migrations were found in the ran migrations database.');
                    }
                }
            }

            $this->info('SGCart Product Variants uninstallation completed successfully.');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('An error occurred during uninstallation: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
