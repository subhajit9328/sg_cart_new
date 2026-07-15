<?php

namespace SGCart\Inventory\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use App\Models\OrderItem;
use App\Models\Order;
use App\Models\Product;
use SGCart\Inventory\Models\InventoryLog;

class InventoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'inventory');

        // Customize validation attribute names for inventory quantity
        if ($this->app->bound('translator')) {
            $this->app->extend('translator', function ($translator) {
                $translator->addLines([
                    'validation.attributes.qty' => 'quantity',
                    'validation.attributes.adjustments.*.qty' => 'quantity',
                ], $translator->getLocale());
                return $translator;
            });
        }

        $this->autoInstall();
        $this->registerStockObservers();
    }

    protected function autoInstall(): void
    {
        try {
            if (Schema::connection(null)->getConnection()->getPdo() && !Schema::hasTable('inventory_logs')) {
                Artisan::call('migrate', [
                    '--path' => 'packages/sgcart/inventory/database/migrations',
                    '--force' => true
                ]);
            }
            
            // Seed permission if Spatie exists
            if (class_exists(\Spatie\Permission\Models\Permission::class) && Schema::hasTable('permissions')) {
                $permission = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'manage inventory', 'guard_name' => 'web']);
                $role = \Spatie\Permission\Models\Role::whereIn('name', ['Super Admin', 'super-admin'])->first();
                if ($role && !$role->hasPermissionTo($permission)) {
                    $role->givePermissionTo($permission);
                }
            }
        } catch (\Exception $e) {
            // Silence early DB connection exceptions
        }
    }

    protected function registerStockObservers(): void
    {
        // Observe OrderItem creation to log sale and handle variants stock
        OrderItem::created(function ($orderItem) {
            try {
                $order = $orderItem->order;
                $quantity = $orderItem->quantity;
                $productId = $orderItem->product_id;
                
                $variant = $this->findVariant($productId, $orderItem->color, $orderItem->size);
                
                if ($variant) {
                    $before = $variant->stock;
                    $variant->decrement('stock', $quantity);
                    $after = $variant->stock;

                    // Sync base product stock
                    $this->syncProductStock($productId);

                    InventoryLog::create([
                        'product_id' => $productId,
                        'product_variant_id' => $variant->id,
                        'quantity' => -$quantity,
                        'action' => 'order_sale',
                        'reason' => "Storefront Checkout Order #" . ($order->order_number ?? $order->id),
                        'before_stock' => $before,
                        'after_stock' => $after,
                    ]);
                } else {
                    // Non-variant item. StoreController will decrement the base product's stock.
                    // The before stock should be the product stock right now (since controller has not run decrement yet)
                    $product = Product::find($productId);
                    if ($product) {
                        $before = $product->stock;
                        $after = $before - $quantity;

                        InventoryLog::create([
                            'product_id' => $productId,
                            'product_variant_id' => null,
                            'quantity' => -$quantity,
                            'action' => 'order_sale',
                            'reason' => "Storefront Checkout Order #" . ($order->order_number ?? $order->id),
                            'before_stock' => $before,
                            'after_stock' => $after,
                        ]);
                    }
                }
            } catch (\Exception $e) {
                // Prevent checkouts from breaking if inventory logs fail
            }
        });

        // Observe Order deleting (failed checkout / order deleted) to restock variants
        Order::deleting(function ($order) {
            try {
                // Retrieve items loaded in memory or database
                $items = $order->relationLoaded('items') ? $order->items : $order->items()->get();

                foreach ($items as $orderItem) {
                    $variant = $this->findVariant($orderItem->product_id, $orderItem->color, $orderItem->size);
                    if ($variant) {
                        $before = $variant->stock;
                        $variant->increment('stock', $orderItem->quantity);
                        $after = $variant->stock;

                        $this->syncProductStock($orderItem->product_id);

                        InventoryLog::create([
                            'product_id' => $orderItem->product_id,
                            'product_variant_id' => $variant->id,
                            'quantity' => $orderItem->quantity,
                            'action' => 'order_refund',
                            'reason' => "Restocked from Order deletion #" . ($order->order_number ?? $order->id),
                            'before_stock' => $before,
                            'after_stock' => $after,
                        ]);
                    } else {
                        // StoreController manually increments product stock.
                        // We just log it here.
                        $product = Product::find($orderItem->product_id);
                        if ($product) {
                            $before = $product->stock;
                            $after = $before + $orderItem->quantity;

                            InventoryLog::create([
                                'product_id' => $orderItem->product_id,
                                'product_variant_id' => null,
                                'quantity' => $orderItem->quantity,
                                'action' => 'order_refund',
                                'reason' => "Restocked from Order deletion #" . ($order->order_number ?? $order->id),
                                'before_stock' => $before,
                                'after_stock' => $after,
                            ]);
                        }
                    }
                }
            } catch (\Exception $e) {
                // Silence deletion hooks exceptions
            }
        });

        // Observe Order Cancellation to restock variants and base products
        Order::updated(function ($order) {
            try {
                $statusStr = is_object($order->status) ? ($order->status->value ?? $order->status->name) : $order->status;
                $originalStatus = $order->getOriginal('status');
                $originalStatusStr = is_object($originalStatus) ? ($originalStatus->value ?? $originalStatus->name) : $originalStatus;

                // Restock ONLY if status changed to Cancelled and was not Cancelled before
                if ($statusStr === 'Cancelled' && $originalStatusStr !== 'Cancelled') {
                    $items = $order->items;
                    foreach ($items as $orderItem) {
                        $variant = $this->findVariant($orderItem->product_id, $orderItem->color, $orderItem->size);
                        if ($variant) {
                            $before = $variant->stock;
                            $variant->increment('stock', $orderItem->quantity);
                            $after = $variant->stock;

                            $this->syncProductStock($orderItem->product_id);

                            InventoryLog::create([
                                'product_id' => $orderItem->product_id,
                                'product_variant_id' => $variant->id,
                                'quantity' => $orderItem->quantity,
                                'action' => 'order_refund',
                                'reason' => "Restocked from Order Cancellation #" . ($order->order_number ?? $order->id),
                                'before_stock' => $before,
                                'after_stock' => $after,
                            ]);
                        } else {
                            $product = Product::find($orderItem->product_id);
                            if ($product) {
                                $before = $product->stock;
                                $product->increment('stock', $orderItem->quantity);
                                $after = $product->stock;

                                InventoryLog::create([
                                    'product_id' => $orderItem->product_id,
                                    'product_variant_id' => null,
                                    'quantity' => $orderItem->quantity,
                                    'action' => 'order_refund',
                                    'reason' => "Restocked from Order Cancellation #" . ($order->order_number ?? $order->id),
                                    'before_stock' => $before,
                                    'after_stock' => $after,
                                ]);
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                // Silence cancellation hooks exceptions
            }
        });
    }

    protected function findVariant($productId, $colorNameOrHex, $sizeCodeOrName)
    {
        if (!class_exists(\SGCart\ProductVariants\Models\ProductVariant::class)) {
            return null;
        }

        return \SGCart\ProductVariants\Models\ProductVariant::where('product_id', $productId)
            ->when($colorNameOrHex, function ($q) use ($colorNameOrHex) {
                $q->whereHas('color', function ($c) use ($colorNameOrHex) {
                    $c->where('hex_code', $colorNameOrHex)
                      ->orWhere('name', $colorNameOrHex);
                });
            })
            ->when($sizeCodeOrName, function ($q) use ($sizeCodeOrName) {
                $q->whereHas('size', function ($s) use ($sizeCodeOrName) {
                    $s->where('code', $sizeCodeOrName)
                      ->orWhere('name', $sizeCodeOrName);
                });
            })
            ->first();
    }

    protected function syncProductStock($productId): void
    {
        if (!class_exists(\SGCart\ProductVariants\Models\ProductVariant::class)) {
            return;
        }

        $product = Product::find($productId);
        if ($product) {
            $totalStock = \SGCart\ProductVariants\Models\ProductVariant::where('product_id', $productId)
                ->where('is_active', true)
                ->sum('stock');
            
            $product->stock = $totalStock;
            $product->save();
        }
    }
}
