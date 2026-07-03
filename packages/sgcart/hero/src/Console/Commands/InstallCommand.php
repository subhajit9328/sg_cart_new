<?php

namespace SGCart\Hero\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sgcart:hero-install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run migrations and configure permissions for SGCart Hero Section package';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Installing SGCart Hero Section package resources...');

        Artisan::call('migrate', [
            '--path' => 'packages/sgcart/hero/database/migrations',
            '--force' => true,
        ]);

        $this->info(Artisan::output());
        $this->info('SGCart Hero Section package installed successfully!');
    }
}
