<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasUlids;

    protected $fillable = [
        'order_number',
        'customer_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'alternate_phone',
        'address_type',
        'landmark',
        'shipping_and_billing_same',
        'billing_first_name',
        'billing_last_name',
        'billing_address',
        'billing_city',
        'billing_state',
        'billing_zip',
        'billing_country',
        'billing_phone',
        'address',
        'city',
        'state',
        'zip',
        'country',
        'subtotal',
        'tax',
        'tax_method',
        'shipping_charge',
        'shipping_method',
        'discount',
        'total',
        'status',
        'payment_status',
        'payment_method',
        'card_name',
        'card_number_masked',
        'payment_transaction_id',
        'payment_gateway',
        'tracking_number',
        'shipping_carrier',
        'tracking_url',
        'estimated_delivery_at',
    ];

    /**
     * Only auto-generate ULID for the `ulid` column.
     * The `id` column remains a standard auto-increment integer PK.
     */
    public function uniqueIds(): array
    {
        return ['ulid'];
    }

    /**
     * The column used for route model binding (exposes ULID, not integer id).
     */
    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    /**
     * Get the customer that placed the order.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the items for the order.
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'status' => \App\Enums\OrderStatus::class,
            'payment_status' => \App\Enums\PaymentStatus::class,
            'estimated_delivery_at' => 'datetime',
        ];
    }
}
