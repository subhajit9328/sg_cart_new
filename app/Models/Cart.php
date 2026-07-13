<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\StoreController;

class Cart extends Model
{
    protected $fillable = [
        'customer_id',
        'session_id',
    ];

    /**
     * Get the customer that owns the cart.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the items in the cart.
     */
    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get or create the active cart for the current user/guest.
     */
    public static function getActiveCart()
    {
        if (auth('customer')->check()) {
            $customerId = auth('customer')->id();
            // Find or create cart for logged in customer
            return self::firstOrCreate(['customer_id' => $customerId]);
        }

        // For guest, check session
        $sessionId = session()->getId();
        return self::firstOrCreate(['session_id' => $sessionId]);
    }

    /**
     * Merge the guest cart with the customer's cart upon login.
     */
    public static function mergeGuestCart($customerId, $sessionId)
    {
        $guestCart = self::where('session_id', $sessionId)->first();
        if (!$guestCart) {
            return;
        }

        $customerCart = self::firstOrCreate(['customer_id' => $customerId]);

        foreach ($guestCart->items as $guestItem) {
            // Check if customer cart already has this item
            $existingItem = $customerCart->items()
                ->where('product_id', $guestItem->product_id)
                ->where('size', $guestItem->size)
                ->where('color', $guestItem->color)
                ->first();

            if ($existingItem) {
                $existingItem->quantity += $guestItem->quantity;
                $existingItem->save();
            } else {
                $guestItem->cart_id = $customerCart->id;
                $guestItem->save();
            }
        }

        // Delete guest cart as it's merged
        $guestCart->delete();
    }

    /**
     * Retrieve cart items formatted for storefront.
     */
    public function getFormattedItems()
    {
        $formatted = [];
        $products = StoreController::getProducts();
        $productsCol = collect($products);

        $hasVariants = class_exists(\SGCart\ProductVariants\Models\ProductVariant::class);
        $colorEnabled = $hasVariants ? config('product-variants.features.color', true) : false;
        $sizeEnabled = $hasVariants ? config('product-variants.features.size', true) : false;

        foreach ($this->items as $item) {
            $product = $productsCol->firstWhere('id', $item->product_id);
            if (!$product) {
                continue;
            }

            $price = $product['price'];
            $img = $product['img'];
            $sku = $product['sku'] ?? null;
            $stock = $product['stock'] ?? 0;

            if ($hasVariants) {
                $colorId = null;
                if ($colorEnabled && !empty($item->color)) {
                    $colorId = \SGCart\ProductVariants\Models\Color::where('hex_code', $item->color)->value('id');
                }

                $sizeId = null;
                if ($sizeEnabled && !empty($item->size)) {
                    $sizeId = \SGCart\ProductVariants\Models\Size::where('code', $item->size)->value('id');
                }

                $variantQuery = \SGCart\ProductVariants\Models\ProductVariant::where('product_id', $item->product_id)
                    ->where('is_active', true);

                if ($colorEnabled) {
                    if ($colorId) {
                        $variantQuery->where('color_id', $colorId);
                    } else {
                        $variantQuery->whereNull('color_id');
                    }
                }

                if ($sizeEnabled) {
                    if ($sizeId) {
                        $variantQuery->where('size_id', $sizeId);
                    } else {
                        $variantQuery->whereNull('size_id');
                    }
                }

                $variant = $variantQuery->first();

                if (!$variant) {
                    // Fallback to partial match if exact match fails
                    $variant = \SGCart\ProductVariants\Models\ProductVariant::where('product_id', $item->product_id)
                        ->where('is_active', true)
                        ->when($colorEnabled && $colorId, fn($q) => $q->where('color_id', $colorId))
                        ->when($sizeEnabled && $sizeId, fn($q) => $q->where('size_id', $sizeId))
                        ->first();
                }

                if ($variant) {
                    $variantPrice = $variant->sale_price ?: $variant->price;
                    if ($variantPrice !== null) {
                        $price = (float) $variantPrice;
                    }
                    if ($variant->image) {
                        $img = \Storage::url($variant->image);
                    }
                    if ($variant->sku) {
                        $sku = $variant->sku;
                    }
                    $stock = (int) $variant->stock;
                }
            }

            // Cart key: productId_size_color
            $key = $item->product_id . '_' . ($item->size ?? '') . '_' . ($item->color ?? '');
            
            $formatted[$key] = [
                'id' => $product['id'],
                'slug' => $product['slug'],
                'name' => $product['name'],
                'price' => $price,
                'img' => $img,
                'sku' => $sku,
                'quantity' => $item->quantity,
                'size' => $item->size,
                'color' => $item->color,
                'cat' => $product['cat'],
                'stock' => $stock,
                'cart_item_id' => $item->id,
            ];
        }

        return $formatted;
    }
}
