<?php

namespace SGCart\LogisticTracking\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Order;

class OrderTracking extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'order_trackings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'order_id',
        'shipping_courier_id',
        'tracking_number',
        'shipping_carrier',
        'tracking_url',
        'estimated_delivery_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'estimated_delivery_at' => 'datetime',
    ];

    /**
     * Get the order that owns this tracking.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the shipping courier associated with this tracking.
     */
    public function shippingCourier()
    {
        return $this->belongsTo(ShippingCourier::class, 'shipping_courier_id');
    }
}
