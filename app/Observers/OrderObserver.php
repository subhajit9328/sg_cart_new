<?php

namespace App\Observers;

use App\Actions\LogActivity;
use App\Enums\OrderStatus;
use App\Mail\OrderCancelledMail;
use App\Mail\OrderCreatedMail;
use App\Mail\OrderStatusUpdatedMail;
use App\Models\Order;
use BackedEnum;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Mail;

class OrderObserver implements ShouldHandleEventsAfterCommit
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        if ($order->email) {
            Mail::to($order->email)->send(new OrderCreatedMail($order));
        }

        if (class_exists(LogActivity::class)) {
            app(LogActivity::class)->capture(
                description: 'Order created with reference: '.$order->order_number,
                event: 'order.created',
                subject: $order,
                logName: 'order'
            );
        }

        if ($order->customer_id && class_exists(\App\Helpers\NotificationHelper::class)) {
            \App\Helpers\NotificationHelper::sendToCustomer(
                $order->customer_id,
                'Order Placed',
                "Your order #{$order->order_number} has been successfully placed.",
                route('store.account.order.view', $order->ulid),
                'info',
                'fa-shopping-bag'
            );
        }
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        if ($order->wasChanged('status')) {
            if ($order->email) {
                if ($order->status === OrderStatus::CANCELLED) {
                    Mail::to($order->email)->send(new OrderCancelledMail($order));
                } else {
                    Mail::to($order->email)->send(new OrderStatusUpdatedMail($order));
                }
            }

            if (class_exists(LogActivity::class)) {
                $oldStatus = $order->getOriginal('status');
                $newStatus = $order->status;

                if ($oldStatus instanceof BackedEnum) {
                    $oldStatus = $oldStatus->value;
                }
                if ($newStatus instanceof BackedEnum) {
                    $newStatus = $newStatus->value;
                }

                app(LogActivity::class)->capture(
                    description: "Order status changed from {$oldStatus} to {$newStatus}",
                    event: 'order.status_updated',
                    subject: $order,
                    attributeChanges: [
                        'old' => ['status' => $oldStatus],
                        'new' => ['status' => $newStatus],
                    ],
                    logName: 'order'
                );
            }

            // User side notifications for all order status changes
            if ($order->customer_id && class_exists(\App\Helpers\NotificationHelper::class)) {
                [$title, $message, $type, $icon] = match ($order->status) {
                    OrderStatus::NEW_ORDER => [
                        'Order Placed',
                        "Your order #{$order->order_number} has been successfully placed.",
                        'info',
                        'fa-shopping-bag'
                    ],
                    OrderStatus::PROCESSING, OrderStatus::PROCESSED => [
                        'Order Processing',
                        "Your order #{$order->order_number} is now being processed.",
                        'info',
                        'fa-box-open'
                    ],
                    OrderStatus::SHIPPED => [
                        'Order Shipped',
                        "Your order #{$order->order_number} has been shipped and is in transit.",
                        'info',
                        'fa-truck'
                    ],
                    OrderStatus::OUT_FOR_DELIVERY => [
                        'Out for Delivery',
                        "Your order #{$order->order_number} is out for delivery and will reach you shortly.",
                        'warning',
                        'fa-truck-ramp-box'
                    ],
                    OrderStatus::DELIVERED => [
                        'Order Delivered',
                        "Your order #{$order->order_number} has been successfully delivered.",
                        'success',
                        'fa-circle-check'
                    ],
                    OrderStatus::CANCELLED => [
                        'Order Cancelled',
                        "Your order #{$order->order_number} has been cancelled.",
                        'danger',
                        'fa-ban'
                    ],
                    default => [null, null, null, null],
                };

                if ($title !== null) {
                    \App\Helpers\NotificationHelper::sendToCustomer(
                        $order->customer_id,
                        $title,
                        $message,
                        route('store.account.order.view', $order->ulid),
                        $type,
                        $icon
                    );
                }
            }
        }
    }
}
