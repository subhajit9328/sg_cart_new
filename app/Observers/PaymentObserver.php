<?php

namespace App\Observers;

use App\Actions\LogActivity;
use App\Models\Payment;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class PaymentObserver implements ShouldHandleEventsAfterCommit
{
    /**
     * Handle the Payment "created" event.
     */
    public function created(Payment $payment): void
    {
        if ($payment->order && class_exists(LogActivity::class)) {
            $status = $payment->status instanceof \BackedEnum ? $payment->status->value : $payment->status;
            app(LogActivity::class)->capture(
                description: 'Payment of ₹'.number_format($payment->amount, 2)." via {$payment->payment_method} created with status: {$status}",
                event: 'order.payment_created',
                subject: $payment->order,
                properties: [
                    'amount' => $payment->amount,
                    'payment_method' => $payment->payment_method,
                    'status' => $status,
                ],
                logName: 'order'
            );
        }
    }

    /**
     * Handle the Payment "updated" event.
     */
    public function updated(Payment $payment): void
    {
        if ($payment->wasChanged('status') && $payment->order && class_exists(LogActivity::class)) {
            $oldStatus = $payment->getOriginal('status');
            $newStatus = $payment->status;

            if ($oldStatus instanceof \BackedEnum) {
                $oldStatus = $oldStatus->value;
            }
            if ($newStatus instanceof \BackedEnum) {
                $newStatus = $newStatus->value;
            }

            app(LogActivity::class)->capture(
                description: "Payment status changed from {$oldStatus} to {$newStatus}",
                event: 'order.payment_updated',
                subject: $payment->order,
                attributeChanges: [
                    'old' => ['status' => $oldStatus],
                    'new' => ['status' => $newStatus],
                ],
                logName: 'order'
            );
        }
    }
}
