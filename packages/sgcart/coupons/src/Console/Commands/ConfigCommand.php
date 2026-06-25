<?php

namespace SGCart\Coupons\Console\Commands;

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
    protected $signature = 'sgcart:coupons-config';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Interactively configure coupons';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('==================================================');
        $this->info('           SGCart Coupons Configurator            ');
        $this->info('==================================================');

        // 1. Prompt user to choose option
        $minCartChoice = $this->confirm(
            'Would you like to enable Minimum Cart Subtotal requirements?',
            true
        );

        $expiresChoice = $this->confirm(
            'Would you like to enable Expiration Dates on coupons?',
            true
        );

        $this->info('Configuring coupon features...');

        // 2. Publish config file to main app directory if not already published
        $configPath = config_path('coupons.php');
        
        $this->comment('Publishing package configuration file...');
        Artisan::call('vendor:publish', [
            '--tag' => 'sgcart-coupons-config',
            '--force' => true,
        ]);

        // 3. Write selected configurations to config/coupons.php
        if (File::exists($configPath)) {
            $minCartVal = $minCartChoice ? 'true' : 'false';
            $expiresVal = $expiresChoice ? 'true' : 'false';

            $configContent = <<<PHP
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Active Coupon Features
    |--------------------------------------------------------------------------
    | Define which coupon validation features are enabled.
    | Disabling a feature will skip its database columns, hide its admin UI,
    | and bypass its application rules.
    |
    */
    'features' => [
        'min_cart_total' => {$minCartVal},
        'expires_at' => {$expiresVal},
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
        config(['coupons.features.min_cart_total' => $minCartChoice]);
        config(['coupons.features.expires_at' => $expiresChoice]);

        // 4. Run the schema sync command to update the database schema
        $this->comment('Running database synchronization...');
        Artisan::call('sgcart:coupons-sync');
        $this->info(Artisan::output());

        $this->info('==================================================');
        $this->info('SGCart Coupons configured successfully!');
        $this->info('Min Cart Total check: ' . ($minCartChoice ? 'ENABLED' : 'DISABLED'));
        $this->info('Expiry Dates check: ' . ($expiresChoice ? 'ENABLED' : 'DISABLED'));
        $this->info('==================================================');

        return Command::SUCCESS;
    }
}
