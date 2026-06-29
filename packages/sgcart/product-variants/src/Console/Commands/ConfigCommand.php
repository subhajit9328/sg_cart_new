<?php

namespace SGCart\ProductVariants\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class ConfigCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sgcart:variants-config';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Interactively configure product variants';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('==================================================');
        $this->info('       SGCart Product Variants Configurator        ');
        $this->info('==================================================');

        // 1. Prompt user to choose option
        $choice = $this->choice(
            'Which variant dimensions would you like to enable?',
            [
                'Both Color and Size (Default)',
                'Color only (No size-related tables, views, or code)',
                'Size only (No color-related tables, views, or code)'
            ],
            0
        );

        $colorEnabled = true;
        $sizeEnabled = true;

        if ($choice === 'Color only (No size-related tables, views, or code)') {
            $sizeEnabled = false;
        } elseif ($choice === 'Size only (No color-related tables, views, or code)') {
            $colorEnabled = false;
        }

        $this->info('Configuring variant dimensions...');

        // 2. Publish config file to main app directory if not already published
        $configPath = config_path('product-variants.php');
        
        $this->comment('Publishing package configuration file...');
        Artisan::call('vendor:publish', [
            '--tag' => 'sgcart-variants-config',
            '--force' => true,
        ]);

        // 3. Write selected configurations to config/product-variants.php
        if (File::exists($configPath)) {
            $colorVal = $colorEnabled ? 'true' : 'false';
            $sizeVal = $sizeEnabled ? 'true' : 'false';

            $configContent = <<<PHP
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Active Variant Dimensions
    |--------------------------------------------------------------------------
    | Define which product variant options are enabled in your e-commerce system.
    | Disabling a feature will skip its database migration, hide the CRUD UI,
    | omit it from product grids, and bypass its storefront validation.
    |
    */
    'features' => [
        'color' => {$colorVal},
        'size' => {$sizeVal},
    ],
];
PHP;

            File::put($configPath, $configContent);
            $this->info('Configuration file generated successfully.');
        } else {
            $this->error('Failed to publish configuration file.');
            return Command::FAILURE;
        }

        // Clear config cache to ensure the new values are read instantly
        Artisan::call('config:clear');

        // Set configuration in-memory so the current process uses the new values
        config(['product-variants.features.color' => $colorEnabled]);
        config(['product-variants.features.size' => $sizeEnabled]);

        // 4. Run the schema sync command to update the database schema
        $this->comment('Running database synchronization...');
        Artisan::call('sgcart:variants-sync');
        $this->info(Artisan::output());

        $this->info('==================================================');
        $this->info('SGCart Product Variants configured successfully!');
        $this->info('Enabled: ' . ($colorEnabled ? 'Color ' : '') . ($sizeEnabled ? 'Size' : ''));
        $this->info('==================================================');

        return Command::SUCCESS;
    }
}
