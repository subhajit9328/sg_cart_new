<?php

namespace SGCart\ImageSearch\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UninstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'image-search:uninstall';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Uninstall the ImageSearch package resources (such as dropping database tables and deleting trait files)';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->components->info('Uninstalling ImageSearch package resources...');

        // 1. Drop the search_terms table
        if (Schema::hasTable('search_terms')) {
            Schema::drop('search_terms');
            $this->components->info('Database table "search_terms" dropped successfully.');
        }

        // 2. Clean up migration record in the migrations table
        try {
            DB::table('migrations')->where('migration', 'like', '%_create_search_terms_table')->delete();
            $this->components->info('Removed database migration records.');
        } catch (\Exception $e) {
            // Ignore if migrations table doesn't exist
        }

        // 3. Delete the migration file from database/migrations
        $migrationFiles = File::glob(database_path('migrations/*_create_search_terms_table.php'));
        foreach ($migrationFiles as $file) {
            File::delete($file);
            $this->components->info('Deleted migration file: ' . basename($file));
        }

        // 4. Delete the configuration file from config/
        $configPath = config_path('image-search.php');
        if (File::exists($configPath)) {
            File::delete($configPath);
            $this->components->info('Deleted configuration file: image-search.php');
        }

        // 5. Note: We do NOT delete the local HasSearchTerms trait stub
        // so that the Product model does not break when the package is uninstalled.
        // The stub safely returns a dummy empty relationship if the package is missing.

        $this->components->info('ImageSearch resources cleaned up successfully!');
    }
}
