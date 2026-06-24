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

        foreach ($this->items as $item) {
            $product = $productsCol->firstWhere('id', $item->product_id);
            if (!$product) {
                continue;
            }

            // Cart key: productId_size_color
            $key = $item->product_id . '_' . ($item->size ?? '') . '_' . ($item->color ?? '');
            
            $formatted[$key] = [
                'id' => $product['id'],
                'slug' => $product['slug'],
                'name' => $product['name'],
                'price' => $product['price'],
                'img' => $product['img'],
                'quantity' => $item->quantity,
                'size' => $item->size,
                'color' => $item->color,
                'cat' => $product['cat'],
                'stock' => $product['stock'] ?? 0,
                'cart_item_id' => $item->id,
            ];
        }

        return $formatted;
    }
}
