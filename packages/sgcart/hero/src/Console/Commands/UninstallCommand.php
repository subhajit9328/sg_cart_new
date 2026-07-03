<?php

namespace SGCart\Hero\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;

class UninstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sgcart:hero-uninstall';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Roll back Hero Section migrations, delete uploaded images and clean up database settings';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting SGCart Hero Section package uninstallation...');

        try {
            $migrationNames = [
                '2026_07_03_120001_create_hero_images_table',
            ];

            // 1. Delete uploaded files from storage
            if (Schema::hasTable('hero_images')) {
                $images = DB::table('hero_images')->pluck('image_path');
                foreach ($images as $path) {
                    if (Storage::disk('public')->exists($path)) {
                        Storage::disk('public')->delete($path);
                        $this->info("Deleted image from storage: {$path}");
                    }
                }
            }

            // 2. Rollback package migrations if they ran
            $ranMigrations = DB::table('migrations')
                ->whereIn('migration', $migrationNames)
                ->pluck('migration')
                ->toArray();

            if (!empty($ranMigrations)) {
                $this->comment('Found package migrations to rollback: ' . implode(', ', $ranMigrations));

                DB::table('migrations')
                    ->whereIn('migration', $ranMigrations)
                    ->update(['batch' => 999999]);

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
            }

            // 3. Clean up migration records explicitly
            DB::table('migrations')->whereIn('migration', $migrationNames)->delete();
            $this->info('Removed migration registry entries.');

            // 4. Remove published migrations if present
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

            // 5. Remove Spatie permissions
            if (class_exists(\Spatie\Permission\Models\Permission::class)) {
                $permission = \Spatie\Permission\Models\Permission::where('name', 'manage hero section')->first();
                if ($permission) {
                    $permission->delete();
                    $this->info("Removed permission: manage hero section");
                }
            }

            $this->info('SGCart Hero Section uninstallation completed successfully.');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('An error occurred during uninstallation: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
