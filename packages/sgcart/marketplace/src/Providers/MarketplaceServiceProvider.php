<?php

namespace SGCart\Marketplace\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use App\Models\OrderItem;
use App\Models\Order;
use App\Models\Product;
use SGCart\Marketplace\Models\Seller;
use SGCart\Marketplace\Models\SellerCommission;
use SGCart\Marketplace\Http\Middleware\EnsureSellerIsApproved;

class MarketplaceServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(__DIR__.'/../../config/marketplace.php', 'marketplace');

        // Dynamically inject the 'seller' auth guard configuration
        $this->configureAuthGuard();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $router = $this->app['router'];
        $router->aliasMiddleware('seller.approved', EnsureSellerIsApproved::class);
        $router->aliasMiddleware('seller.onboarded', \SGCart\Marketplace\Http\Middleware\EnsureSellerIsOnboarded::class);

        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'marketplace');

        $this->autoInstall();
        $this->registerSellerScopesAndObservers();
        $this->registerViewComposers();
    }

    /**
     * Register view composers to share variables across views.
     */
    protected function registerViewComposers(): void
    {
        $this->app['view']->composer('marketplace::admin-menu', function ($view) {
            $pendingSellersCount = 0;
            $pendingProductsCount = 0;

            try {
                if (Schema::hasTable('sellers')) {
                    $pendingSellersCount = Seller::where('status', 'pending')->count();
                }
                if (Schema::hasTable('products')) {
                    $pendingProductsCount = Product::where('status', \App\Enums\ProductStatus::PENDING_APPROVAL)->whereNotNull('seller_id')->count();
                }
            } catch (\Exception $e) {
                // Silently default to 0 during bootstrap/migrations
            }

            $view->with(compact('pendingSellersCount', 'pendingProductsCount'));
        });
    }

    /**
     * Configure the seller auth guard on the fly.
     */
    protected function configureAuthGuard(): void
    {
        $auth = $this->app['config']->get('auth', []);

        // Guard injection
        $auth['guards']['seller'] = [
            'driver' => 'session',
            'provider' => 'sellers',
        ];

        // Provider injection
        $auth['providers']['sellers'] = [
            'driver' => 'eloquent',
            'model' => Seller::class,
        ];

        // Password broker injection
        $auth['passwords']['sellers'] = [
            'provider' => 'sellers',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ];

        $this->app['config']->set('auth', $auth);
    }

    /**
     * Run package migrations automatically if not present.
     */
    protected function autoInstall(): void
    {
        if ($this->app->runningInConsole()) {
            return;
        }
        try {
            if (Schema::connection(null)->getConnection()->getPdo() && !Schema::hasTable('sellers')) {
                Artisan::call('migrate', [
                    '--path' => 'packages/sgcart/marketplace/database/migrations',
                    '--force' => true
                ]);
            }
        } catch (\Exception $e) {
            // Silence DB exceptions during bootstrap
        }
    }

    /**
     * Setup multi-tenant seller scopes and event observers.
     */
    protected function registerSellerScopesAndObservers(): void
    {
        // 1. Scope Products to Seller
        Product::addGlobalScope('seller_portal', function ($builder) {
            $isSellerPortal = !app()->runningInConsole() && (request()->is('seller/*') || request()->is('seller') || request()->routeIs('seller.*'));
            if ($isSellerPortal && auth('seller')->check()) {
                $builder->where('seller_id', auth('seller')->id());
            }
        });

        // 2. Scope Orders to Seller
        Order::addGlobalScope('seller_portal', function ($builder) {
            $isSellerPortal = !app()->runningInConsole() && (request()->is('seller/*') || request()->is('seller') || request()->routeIs('seller.*'));
            if ($isSellerPortal && auth('seller')->check()) {
                $builder->whereHas('items.product', function ($q) {
                    $q->where('seller_id', auth('seller')->id());
                });
            }
        });

        // 3. Scope Order Items to Seller
        OrderItem::addGlobalScope('seller_portal', function ($builder) {
            $isSellerPortal = !app()->runningInConsole() && (request()->is('seller/*') || request()->is('seller') || request()->routeIs('seller.*'));
            if ($isSellerPortal && auth('seller')->check()) {
                $builder->whereHas('product', function ($q) {
                    $q->where('seller_id', auth('seller')->id());
                });
            }
        });

        // 4. Force Seller ID and status reset on Product saving
        Product::saving(function ($product) {
            $isSellerPortal = !app()->runningInConsole() && (request()->is('seller/*') || request()->is('seller') || request()->routeIs('seller.*'));
            if ($isSellerPortal && auth('seller')->check()) {
                $product->seller_id = auth('seller')->id();
                
                // Force status to pending_approval on creation or if price/title updates occur
                if (!$product->exists || $product->isDirty('status') || $product->isDirty('price') || $product->isDirty('name')) {
                    $product->status = \App\Enums\ProductStatus::PENDING_APPROVAL;
                    $product->rejection_reason = null;
                }
            }
        });

        // 5. Calculate and log commission on OrderItem creation
        OrderItem::created(function ($orderItem) {
            try {
                $product = $orderItem->product;
                if ($product && $product->seller_id) {
                    $seller = Seller::find($product->seller_id);
                    if ($seller && $seller->status === 'approved') {
                        $price = (float) $orderItem->price;
                        $qty = (int) $orderItem->quantity;
                        $subtotal = $price * $qty;
                        $rate = (float) $seller->getActiveCommissionRate();
                        $commission = $subtotal * ($rate / 100);
                        $earning = $subtotal - $commission;

                        SellerCommission::create([
                            'order_id' => $orderItem->order_id,
                            'order_item_id' => $orderItem->id,
                            'seller_id' => $seller->id,
                            'product_price' => $price,
                            'quantity' => $qty,
                            'subtotal' => $subtotal,
                            'commission_rate' => $rate,
                            'commission_amount' => $commission,
                            'seller_earning' => $earning,
                            'status' => 'pending',
                        ]);
                    }
                }
            } catch (\Exception $e) {
                // Silence checkout errors
            }
        });

        // 6. Cancel commissions if order is cancelled
        Order::updated(function ($order) {
            try {
                $statusStr = is_object($order->status) ? ($order->status->value ?? $order->status->name) : $order->status;
                $originalStatus = $order->getOriginal('status');
                $originalStatusStr = is_object($originalStatus) ? ($originalStatus->value ?? $originalStatus->name) : $originalStatus;

                if ($statusStr === 'Cancelled' && $originalStatusStr !== 'Cancelled') {
                    SellerCommission::where('order_id', $order->id)
                        ->update(['status' => 'cancelled']);
                }
            } catch (\Exception $e) {
                // Silence update errors
            }
        });
    }
}
