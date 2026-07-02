<?php

namespace SGCart\DashboardAnalytics\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use App\Models\Order;

class DashboardAnalyticsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // No specific bindings needed, but can merge config if we have any
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \SGCart\DashboardAnalytics\Console\Commands\UninstallCommand::class,
            ]);
        }

        // Load Package Components
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'dashboard-analytics');

        // Register route middleware
        $this->app['router']->pushMiddlewareToGroup('web', \SGCart\DashboardAnalytics\Http\Middleware\LogSearchMiddleware::class);

        // Automate Order Coupon Capturing inside boot phase
        if (class_exists(Order::class)) {
            Order::creating(function (Order $order) {
                if (session()->has('coupon_code')) {
                    $order->coupon_code = session('coupon_code');
                }
            });
        }

        // Automate Installation (Migrations & Permissions & Demo Data Seeding)
        $this->autoInstall();

        // Listen for Composer pre-uninstall event
        $this->app['events']->listen('composer_package.sgcart/dashboard-analytics:pre_uninstall', function () {
            Artisan::call('sgcart:dashboard-analytics-uninstall');
        });
    }

    /**
     * Programmatically runs package migrations and seeds permissions if DB is connected.
     */
    protected function autoInstall(): void
    {
        try {
            if ($this->app->runningInConsole()) {
                $command = $_SERVER['argv'][1] ?? null;
                if (in_array($command, [
                    'sgcart:dashboard-analytics-uninstall',
                    'migrate:rollback',
                    'migrate:reset',
                    'migrate:refresh',
                ]) || (is_string($command) && str_contains($command, 'uninstall'))) {
                    return;
                }
            }

            // Check database connection
            if (Schema::connection(null)->getConnection()->getPdo()) {
                // Programmatically trigger package database migrations if search_logs table is missing
                if (!Schema::hasTable('search_logs')) {
                    Artisan::call('migrate', [
                        '--path' => 'packages/sgcart/dashboard-analytics/database/migrations',
                        '--force' => true
                    ]);
                }

                // Programmatically seed Spatie permissions
                if (class_exists(\Spatie\Permission\Models\Permission::class)) {
                    $permission = \Spatie\Permission\Models\Permission::firstOrCreate([
                        'name' => 'view analytics',
                        'guard_name' => 'web'
                    ]);
                    
                    // Automatically assign to Super Admin and Manager
                    foreach (['Super Admin', 'Manager'] as $roleName) {
                        $role = \Spatie\Permission\Models\Role::where('name', $roleName)->first();
                        if ($role && !$role->hasPermissionTo($permission)) {
                            $role->givePermissionTo($permission);
                        }
                    }
                }

                // Programmatically seed Demo Data if search_logs has 0 rows or Order count is low
                if (Schema::hasTable('search_logs') && \SGCart\DashboardAnalytics\Models\SearchLog::count() === 0) {
                    $this->seedDemoData();
                }
            }
        } catch (\Exception $e) {
            // Silently fail if database is not set up/connected during early composer boot phase
        }
    }

    /**
     * Seed realistic data to make the analytics dashboard display beautiful graphics and charts instantly.
     */
    protected function seedDemoData(): void
    {
        try {
            // 1. Seed Coupons if none exist
            if (class_exists(\SGCart\Coupons\Models\Coupon::class) && \SGCart\Coupons\Models\Coupon::count() === 0) {
                $couponsData = [
                    ['code' => 'WELCOME50', 'type' => 'flat', 'value' => 50.00, 'is_active' => true, 'min_cart_total' => 100.00],
                    ['code' => 'SAVE20', 'type' => 'percent', 'value' => 20.00, 'is_active' => true, 'min_cart_total' => 50.00],
                    ['code' => 'SUMMER10', 'type' => 'percent', 'value' => 10.00, 'is_active' => true, 'min_cart_total' => 0.00],
                    ['code' => 'BOGO', 'type' => 'flat', 'value' => 15.00, 'is_active' => true, 'min_cart_total' => 30.00],
                ];
                foreach ($couponsData as $coupon) {
                    \SGCart\Coupons\Models\Coupon::create($coupon);
                }
            }

            // 2. Seed Search Logs
            $searchTerms = [
                'shirt', 'jeans', 'shoes', 'hoodie', 'dress', 'kurta', 'sunglasses', 'bag', 'sweater', 'saree',
                'black shirt', 'nike shoes', 'adidas hoodie', 'zara dress', 'levis jeans', 'cotton kurta',
                'satin dress', 'leather bag', 'ethnic saree', 'denim pants'
            ];
            
            $now = \Illuminate\Support\Carbon::now();
            for ($i = 0; $i < 150; $i++) {
                $daysAgo = rand(0, 30);
                $searchTime = (clone $now)->subDays($daysAgo)->subHours(rand(0, 23))->subMinutes(rand(0, 59));
                
                \SGCart\DashboardAnalytics\Models\SearchLog::create([
                    'term' => $searchTerms[array_rand($searchTerms)],
                    'ip_address' => '192.168.1.' . rand(1, 254),
                    'created_at' => $searchTime,
                    'updated_at' => $searchTime,
                ]);
            }

            // 3. Seed Orders if count is low
            if (class_exists(\App\Models\Order::class) && \App\Models\Order::count() <= 2) {
                $products = \App\Models\Product::all();
                $customer = \App\Models\Customer::first();
                $customerId = $customer ? $customer->id : null;
                
                if ($products->isNotEmpty()) {
                    $statuses = [\App\Enums\OrderStatus::DELIVERED, \App\Enums\OrderStatus::SHIPPED, \App\Enums\OrderStatus::NEW_ORDER, \App\Enums\OrderStatus::CANCELLED];
                    $couponCodes = ['WELCOME50', 'SAVE20', 'SUMMER10', 'BOGO', null, null];
                    
                    // Seed 25 orders distributed over the last 20 days and different hours of the day
                    for ($j = 0; $j < 25; $j++) {
                        $daysAgo = rand(0, 20);
                        // Setup hour distribution
                        $hourWeights = [
                            0, 1, 2, 3, 4, 5,
                            6, 7, 8, 9, 10, 11,
                            12, 13, 14, 15, 16, 17, 12, 13, 14, 15, 16, 17,
                            18, 19, 20, 21, 22, 23, 18, 19, 20, 21, 22, 23, 18, 19, 20, 21, 22, 23
                        ];
                        $randomHour = $hourWeights[array_rand($hourWeights)];
                        $orderTime = (clone $now)->subDays($daysAgo)->setHour($randomHour)->setMinute(rand(0, 59));
                        
                        $status = $statuses[array_rand($statuses)];
                        $couponCode = $couponCodes[array_rand($couponCodes)];
                        
                        // Select 1 to 3 random products
                        $orderProducts = $products->random(rand(1, 3));
                        $subtotal = 0;
                        $items = [];
                        
                        foreach ($orderProducts as $prod) {
                            $qty = rand(1, 2);
                            $price = (float) $prod->price;
                            $subtotal += $price * $qty;
                            
                            $items[] = [
                                'product_id' => $prod->id,
                                'product_name' => $prod->name,
                                'product_sku' => $prod->sku,
                                'price' => $price,
                                'quantity' => $qty,
                            ];
                        }
                        
                        $discount = 0;
                        if ($couponCode) {
                            if ($couponCode === 'WELCOME50' && $subtotal >= 100) $discount = 50.00;
                            elseif ($couponCode === 'SAVE20' && $subtotal >= 50) $discount = $subtotal * 0.20;
                            elseif ($couponCode === 'SUMMER10') $discount = $subtotal * 0.10;
                            elseif ($couponCode === 'BOGO' && $subtotal >= 30) $discount = 15.00;
                        }
                        $discount = min($discount, $subtotal);
                        $tax = $subtotal * 0.05;
                        $shipping = 10.00;
                        $total = $subtotal - $discount + $tax + $shipping;
                        
                        $order = \App\Models\Order::create([
                            'order_number' => 'ORD-' . strtoupper(\Illuminate\Support\Str::random(8)),
                            'customer_id' => $customerId,
                            'first_name' => 'Demo',
                            'last_name' => 'Customer ' . $j,
                            'email' => 'customer' . $j . '@example.com',
                            'address' => '123 Test Street',
                            'city' => 'New York',
                            'state' => 'NY',
                            'zip' => '10001',
                            'country' => 'USA',
                            'subtotal' => $subtotal,
                            'tax' => $tax,
                            'discount' => $discount,
                            'coupon_code' => $couponCode,
                            'total' => $total,
                            'status' => $status,
                            'created_at' => $orderTime,
                            'updated_at' => $orderTime,
                        ]);
                        
                        foreach ($items as $item) {
                            $order->items()->create($item);
                        }
                        
                        // Seed payment for non-cancelled orders
                        if ($status !== \App\Enums\OrderStatus::CANCELLED) {
                            $order->payments()->create([
                                'transaction_id' => 'TXN-' . strtoupper(\Illuminate\Support\Str::random(12)),
                                'payment_method' => 'Credit Card',
                                'amount' => $total,
                                'status' => \App\Enums\PaymentStatus::PAID,
                                'created_at' => $orderTime,
                                'updated_at' => $orderTime,
                            ]);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Seeding analytics demo data failed: ' . $e->getMessage());
        }
    }
}
