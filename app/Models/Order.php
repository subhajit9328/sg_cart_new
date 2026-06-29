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
    ];

    /**
     * Get the latest payment status (dynamically computed from payments ledger).
     */
    public function getPaymentStatusAttribute()
    {
        return $this->payments()->latest()->first()?->status ?? \App\Enums\PaymentStatus::PENDING;
    }

    /**
     * Get the latest payment method (dynamically computed from payments ledger).
     */
    public function getPaymentMethodAttribute()
    {
        return $this->payments()->latest()->first()?->payment_method ?? 'None';
    }

    /**
     * Get the latest transaction id (dynamically computed from payments ledger).
     */
    public function getTransactionIdAttribute()
    {
        return $this->payments()->latest()->first()?->transaction_id;
    }

    /**
     * Get the latest card name (dynamically computed from payments ledger).
     */
    public function getCardNameAttribute()
    {
        return $this->payments()->latest()->first()?->card_name;
    }

    /**
     * Get the latest masked card number (dynamically computed from payments ledger).
     */
    public function getCardNumberMaskedAttribute()
    {
        return $this->payments()->latest()->first()?->card_number_masked;
    }

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
     * Get the payments associated with this order.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the tracking number.
     */
    public function getTrackingNumberAttribute()
    {
        if (class_exists(\SGCart\LogisticTracking\Models\OrderTracking::class)) {
            return $this->tracking?->tracking_number;
        }
        return null;
    }

    /**
     * Get the shipping carrier.
     */
    public function getShippingCarrierAttribute()
    {
        if (class_exists(\SGCart\LogisticTracking\Models\OrderTracking::class)) {
            return $this->tracking?->shipping_carrier;
        }
        return null;
    }

    /**
     * Get the tracking URL.
     */
    public function getTrackingUrlAttribute()
    {
        if (class_exists(\SGCart\LogisticTracking\Models\OrderTracking::class)) {
            return $this->tracking?->tracking_url;
        }
        return null;
    }

    /**
     * Get the estimated delivery date.
     */
    public function getEstimatedDeliveryAtAttribute()
    {
        if (class_exists(\SGCart\LogisticTracking\Models\OrderTracking::class)) {
            return $this->tracking?->estimated_delivery_at;
        }
        return null;
    }

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'status' => \App\Enums\OrderStatus::class,
            'estimated_delivery_at' => 'datetime',
        ];
    }
}
