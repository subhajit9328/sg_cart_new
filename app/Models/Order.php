<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use App\Observers\OrderObserver;

#[ObservedBy([OrderObserver::class])]
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
        return $this->payments()->latest()->first()?->status ?? PaymentStatus::PENDING;
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
     * Get the shipping courier ID.
     */
    public function getShippingCourierIdAttribute()
    {
        if (class_exists(\SGCart\LogisticTracking\Models\OrderTracking::class)) {
            return $this->tracking?->shipping_courier_id;
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
            'status' => OrderStatus::class,
            'estimated_delivery_at' => 'datetime',
        ];
    }

    /**
     * Get the activity logs for the order.
     */
    public function activities()
    {
        return $this->morphMany(ActivityLog::class, 'subject')->latest();
    }


    /**
     * Format activity log for order events.
     */
    public static function formatActivityLog(ActivityLog $activity): string
    {
        if ($activity->event === 'order.created') {
            return 'Order was created';
        }

        if ($activity->event === 'order.status_updated') {
            $old = $activity->attribute_changes['old']['status'] ?? 'unknown';
            $new = $activity->attribute_changes['new']['status'] ?? 'unknown';
            return "Status changed from <span class='font-semibold text-slate-850 dark:text-slate-100'>{$old}</span> to <span class='font-semibold text-slate-850 dark:text-slate-100'>{$new}</span>";
        }

        return $activity->description;
    }

    /**
     * Get timeline-specific data for order events.
     */
    public static function getTimelineData(ActivityLog $activity): array
    {
        if ($activity->event === 'order.created') {
            return [
                'title' => 'Order record created',
                'description' => 'Assigned Order Reference: ' . ($activity->subject->order_number ?? ''),
                'icon' => 'fa-pen-nib',
                'icon_color' => 'bg-slate-50 text-slate-500 border border-slate-200/50 dark:bg-slate-900/50 dark:text-slate-400 dark:border-slate-800/60',
            ];
        }

        if ($activity->event === 'order.status_updated') {
            $newStatus = $activity->attribute_changes['new']['status'] ?? 'Processing';

            $statusData = [
                'Processing' => [
                    'title' => 'Processed & Packed',
                    'description' => 'Your items have been carefully packaged and are ready for handover to our courier partner.',
                    'icon' => 'fa-box-open',
                    'icon_color' => 'bg-slate-900 text-white border border-transparent dark:bg-slate-850 dark:text-slate-200',
                ],
                'Shipped' => [
                    'title' => 'Order Shipped (Transit Started)',
                    'description' => 'Order has been dispatched.',
                    'icon' => 'fa-truck',
                    'icon_color' => 'bg-blue-50 text-blue-600 border border-blue-100 dark:bg-blue-950/20 dark:text-blue-400 dark:border-blue-900/30',
                ],
                'Delivered' => [
                    'title' => 'Order Delivered',
                    'description' => 'Package successfully delivered to the recipient.',
                    'icon' => 'fa-circle-check',
                    'icon_color' => 'bg-emerald-50 text-emerald-600 border border-emerald-100 dark:bg-emerald-950/20 dark:text-emerald-400 dark:border-emerald-900/30',
                ],
                'Cancelled' => [
                    'title' => 'Order Cancelled',
                    'description' => 'This order has been marked as Cancelled.',
                    'icon' => 'fa-ban',
                    'icon_color' => 'bg-rose-50 text-rose-600 border border-rose-100 dark:bg-rose-955/20 dark:text-rose-455 dark:border-rose-900/30',
                ],
            ];

            return $statusData[$newStatus] ?? [
                'title' => "Order status updated to {$newStatus}",
                'description' => $activity->description,
                'icon' => 'fa-circle-info',
                'icon_color' => 'bg-slate-50 text-slate-600 border border-slate-200/50 dark:bg-slate-900/50 dark:text-slate-300 dark:border-slate-800/60',
            ];
        }

        return [
            'title' => 'Order Activity',
            'description' => $activity->description,
            'icon' => 'fa-circle-info',
            'icon_color' => 'bg-slate-50 text-slate-600 border border-slate-200/50 dark:bg-slate-900/50 dark:text-slate-300 dark:border-slate-800/60',
        ];
    }
}
