<?php

namespace SGCart\Razorpay\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Models\PaymentMethod;
use SGCart\Razorpay\Gateways\RazorpayGateway;

class RazorpayServiceProvider extends ServiceProvider
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
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'razorpay');

        // Load Routes
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');

        // Register to core Payment Manager
        if ($this->app->bound('payment.manager')) {
            $paymentManager = $this->app->make('payment.manager');
            $paymentManager->registerGateway(new RazorpayGateway());

            // Seed to database automatically
            try {
                if (Schema::hasTable('payment_methods')) {
                    PaymentMethod::firstOrCreate(
                        ['id' => 'razorpay'],
                        [
                            'name' => 'Razorpay',
                            'description' => 'Pay securely using Razorpay (Cards, UPI, Netbanking).',
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
