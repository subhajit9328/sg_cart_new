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
        }
    }
}
