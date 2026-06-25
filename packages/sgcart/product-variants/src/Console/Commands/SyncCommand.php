<?php

namespace SGCart\ProductVariants\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class SyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sgcart:variants-sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync product variant database schemas to match configuration settings';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Syncing product variants features...');

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
                        $this->comment('Rolling back package migrations to re-sync schema...');

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
                    }
                }

                $this->comment('Re-running migrations with new configuration settings...');
                $relativePath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $migrationsPath);
                Artisan::call('migrate', [
                    '--path' => $relativePath,
                    '--force' => true,
                ]);

                $this->info(Artisan::output());
            }

            $this->info('Database schemas synced with configurations successfully.');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('An error occurred during sync: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
