<?php

namespace SGCart\RelatedProducts\Console\Commands;

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
    protected $signature = 'sgcart:related-products-uninstall';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Roll back related products migrations, remove migration records, and perform package clean up';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting SGCart Related Products uninstallation...');

        try {
            $migrationNames = [
                '2026_07_06_000001_create_related_products_table',
            ];

            // 1. Rollback migrations if they have run
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

            $this->info('SGCart Related Products uninstallation completed successfully.');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('An error occurred during uninstallation: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
