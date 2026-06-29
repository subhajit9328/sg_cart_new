<?php

namespace SGCart\LogisticTracking\Console\Commands;

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
    protected $signature = 'logistic-tracking:uninstall';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Uninstall the LogisticTracking package resources (dropping database tables and deleting published migration files)';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->components->info('Uninstalling LogisticTracking package resources...');

        // 1. Drop the tables
        if (Schema::hasTable('order_trackings')) {
            Schema::drop('order_trackings');
            $this->components->info('Database table "order_trackings" dropped successfully.');
        }

        if (Schema::hasTable('shipping_couriers')) {
            Schema::drop('shipping_couriers');
            $this->components->info('Database table "shipping_couriers" dropped successfully.');
        }

        // 2. Clean up migration record in the migrations table
        try {
            DB::table('migrations')->where('migration', 'like', '%_create_order_trackings_table')->delete();
            $this->components->info('Removed database migration records.');
        } catch (\Exception $e) {
            // Ignore if migrations table doesn't exist
        }

        // 3. Delete the migration file from database/migrations
        $migrationFiles = File::glob(database_path('migrations/*_create_order_trackings_table.php'));
        foreach ($migrationFiles as $file) {
            File::delete($file);
            $this->components->info('Deleted migration file: ' . basename($file));
        }

        $this->components->info('LogisticTracking package uninstalled successfully.');
    }
}
