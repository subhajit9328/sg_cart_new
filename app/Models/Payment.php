<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use App\Observers\PaymentObserver;

#[ObservedBy([PaymentObserver::class])]
class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'payment_method',
        'amount',
        'status',
        'transaction_id',
        'card_name',
        'card_number_masked',
        'payload',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'payload' => 'array',
        ];
    }

    /**
     * Get the order associated with this payment.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }



    /**
     * Format activity log for payment events.
     */
    public static function formatActivityLog(\App\Models\ActivityLog $activity): string
    {
        if ($activity->event === 'order.payment_created') {
            $amount = number_format($activity->properties['amount'] ?? 0, 2);
            $method = $activity->properties['payment_method'] ?? 'unknown';
            $status = $activity->properties['status'] ?? 'pending';
            return "Payment of <span class='font-semibold text-slate-850 dark:text-slate-100'>₹{$amount}</span> via <span class='font-semibold text-slate-850 dark:text-slate-100'>{$method}</span> was created (Status: <span class='font-semibold text-slate-805 dark:text-slate-200'>{$status}</span>)";
        }

        if ($activity->event === 'order.payment_updated') {
            $old = $activity->attribute_changes['old']['status'] ?? 'unknown';
            $new = $activity->attribute_changes['new']['status'] ?? 'unknown';
            return "Payment status changed from <span class='font-semibold text-slate-805 dark:text-slate-200'>{$old}</span> to <span class='font-semibold text-slate-805 dark:text-slate-200'>{$new}</span>";
        }

        return $activity->description;
    }

    /**
     * Get timeline-specific data for payment events.
     */
    public static function getTimelineData(\App\Models\ActivityLog $activity): array
    {
        if ($activity->event === 'order.payment_created') {
            $status = $activity->properties['status'] ?? 'Pending';
            $method = $activity->properties['payment_method'] ?? 'unknown';
            $amount = number_format($activity->properties['amount'] ?? 0, 2);

            if ($status === 'Paid') {
                return [
                    'title' => 'Order Completed & Payment Authorized',
                    'description' => "Order placed and payment charged via {$method}.",
                    'icon' => 'fa-circle-check',
                    'icon_color' => 'bg-blue-50 text-blue-600 border border-blue-100 dark:bg-blue-950/20 dark:text-blue-400 dark:border-blue-900/30',
                ];
            }

            if ($status === 'Pending') {
                $description = "Payment of ₹{$amount} is pending customer action or manual clearance.";
                if (!empty($activity->properties['transaction_id'])) {
                    $description .= "<br><span class='text-[10px] text-slate-400 font-mono'>Txn ID: {$activity->properties['transaction_id']}</span>";
                }
                return [
                    'title' => "Payment Pending: {$method}",
                    'description' => $description,
                    'icon' => 'fa-clock',
                    'icon_color' => 'bg-amber-50 text-amber-600 border border-amber-100 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-900/30',
                ];
            }

            return [
                'title' => "Payment Failed: {$method}",
                'description' => "Payment attempt of ₹{$amount} failed.",
                'icon' => 'fa-circle-xmark',
                'icon_color' => 'bg-rose-50 text-rose-600 border border-rose-100 dark:bg-rose-955/20 dark:text-rose-455 dark:border-rose-900/30',
            ];
        }

        if ($activity->event === 'order.payment_updated') {
            $old = $activity->attribute_changes['old']['status'] ?? 'unknown';
            $new = $activity->attribute_changes['new']['status'] ?? 'unknown';

            return [
                'title' => 'Payment Status Updated',
                'description' => "Payment status changed from {$old} to {$new}.",
                'icon' => 'fa-credit-card',
                'icon_color' => 'bg-blue-50 text-blue-600 border border-blue-100 dark:bg-blue-950/20 dark:text-blue-400 dark:border-blue-900/30',
            ];
        }

        return [
            'title' => 'Payment Activity',
            'description' => $activity->description,
            'icon' => 'fa-credit-card',
            'icon_color' => 'bg-slate-50 text-slate-600 border border-slate-200/50 dark:bg-slate-900/50 dark:text-slate-300 dark:border-slate-800/60',
        ];
    }
}


