<?php

namespace SGCart\Marketplace\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Order;
use App\Models\OrderItem;

class SellerCommission extends Model
{
    protected $fillable = [
        'order_id',
        'order_item_id',
        'seller_id',
        'product_price',
        'quantity',
        'subtotal',
        'commission_rate',
        'commission_amount',
        'seller_earning',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'product_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'commission_rate' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'seller_earning' => 'decimal:2',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * Relationship to the authenticatable Seller model.
     */
    public function seller()
    {
        return $this->belongsTo(Seller::class, 'seller_id');
    }
}
