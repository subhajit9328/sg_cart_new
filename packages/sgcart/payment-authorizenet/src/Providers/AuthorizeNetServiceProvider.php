<?php

namespace SGCart\AuthorizeNet\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Models\PaymentMethod;
use SGCart\AuthorizeNet\Gateways\AuthorizeNetGateway;

class AuthorizeNetServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load Views
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'authorizenet');

        // Register to core Payment Manager
        if ($this->app->bound('payment.manager')) {
            $paymentManager = $this->app->make('payment.manager');
            $paymentManager->registerGateway(new AuthorizeNetGateway());

            // Seed to database automatically
            try {
                if (Schema::hasTable('payment_methods')) {
                    PaymentMethod::firstOrCreate(
                        ['id' => 'authorizenet'],
                        [
                            'name' => 'Authorize.Net',
                            'description' => 'Secure credit card payments via Authorize.Net.',
                            'is_installed' => true,
                            'is_enabled' => false,
                            'config' => []
                        ]
                    );
                }
            } catch (\Exception $e) {
                // Silently fail during early CLI/migration boots
            }
        }
    }
}
