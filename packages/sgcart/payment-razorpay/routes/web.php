<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Enums\PaymentStatus;

Route::get('/razorpay/callback/{order_ulid}', function (Request $request, $order_ulid) {
    $order = Order::where('ulid', $order_ulid)->firstOrFail();

    $paymentLinkId = $request->query('razorpay_payment_link_id');
    $paymentId = $request->query('razorpay_payment_id');
    $status = $request->query('razorpay_payment_link_status');

    if ($status === 'paid' || $paymentId) {
        // Record successful payment transaction
        $order->payments()->create([
            'status' => PaymentStatus::PAID,
            'payment_method' => 'Razorpay',
            'amount' => $order->total,
            'transaction_id' => $paymentId ?? $paymentLinkId,
            'payload' => $request->all(),
        ]);

        return redirect()->route('store.success', ['order_id' => $order->order_number]);
    }

    // Record failed payment transaction
    $order->payments()->create([
        'status' => PaymentStatus::FAILED,
        'payment_method' => 'Razorpay',
        'amount' => $order->total,
        'transaction_id' => $paymentLinkId,
        'payload' => $request->all(),
    ]);

    return redirect()->route('store.checkout')->with('error', 'Razorpay payment was not completed or failed.');
})->name('razorpay.callback');
