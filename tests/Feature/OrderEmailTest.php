<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Enums\OrderStatus;
use App\Mail\OrderCreatedMail;
use App\Mail\OrderCancelledMail;
use App\Mail\OrderStatusUpdatedMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrderEmailTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that creating an order dispatches the OrderCreatedMail.
     */
    public function test_creating_order_sends_created_email(): void
    {
        Mail::fake();

        $order = Order::create([
            'ulid' => (string) Str::ulid(),
            'order_number' => 'ORD-1234',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
            'phone' => '1234567890',
            'address' => '123 Main St',
            'city' => 'Metro',
            'state' => 'State',
            'zip' => '12345',
            'country' => 'India',
            'subtotal' => 100.00,
            'tax' => 10.00,
            'shipping_charge' => 5.00,
            'total' => 115.00,
            'status' => OrderStatus::NEW_ORDER,
        ]);

        Mail::assertSent(OrderCreatedMail::class, function ($mail) use ($order) {
            return $mail->hasTo('jane@example.com') &&
                   $mail->order->id === $order->id;
        });
    }

    /**
     * Test that updating an order status to Cancelled dispatches OrderCancelledMail.
     */
    public function test_cancelling_order_sends_cancelled_email(): void
    {
        Mail::fake();

        $order = Order::create([
            'ulid' => (string) Str::ulid(),
            'order_number' => 'ORD-5678',
            'first_name' => 'John',
            'last_name' => 'Smith',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'address' => '456 Oak St',
            'city' => 'Metro',
            'state' => 'State',
            'zip' => '12345',
            'country' => 'India',
            'subtotal' => 50.00,
            'tax' => 5.00,
            'shipping_charge' => 0.00,
            'total' => 55.00,
            'status' => OrderStatus::NEW_ORDER,
        ]);

        // Reset faked mail to clear the creation email
        Mail::fake();

        $order->update(['status' => OrderStatus::CANCELLED]);

        Mail::assertSent(OrderCancelledMail::class, function ($mail) use ($order) {
            return $mail->hasTo('john@example.com') &&
                   $mail->order->id === $order->id;
        });

        Mail::assertNotSent(OrderStatusUpdatedMail::class);
    }

    /**
     * Test that updating an order to any other status dispatches OrderStatusUpdatedMail.
     */
    public function test_updating_status_sends_dynamic_status_email(): void
    {
        Mail::fake();

        $order = Order::create([
            'ulid' => (string) Str::ulid(),
            'order_number' => 'ORD-9999',
            'first_name' => 'Alice',
            'last_name' => 'Wonder',
            'email' => 'alice@example.com',
            'phone' => '1234567890',
            'address' => '789 Pine St',
            'city' => 'Metro',
            'state' => 'State',
            'zip' => '12345',
            'country' => 'India',
            'subtotal' => 200.00,
            'tax' => 20.00,
            'shipping_charge' => 10.00,
            'total' => 230.00,
            'status' => OrderStatus::NEW_ORDER,
        ]);

        // Reset faked mail to clear the creation email
        Mail::fake();

        $order->update(['status' => OrderStatus::SHIPPED]);

        Mail::assertSent(OrderStatusUpdatedMail::class, function ($mail) use ($order) {
            $expectedSubject = 'Order Status Updated: Shipped - #ORD-9999 - ' . config('app.name');
            return $mail->hasTo('alice@example.com') &&
                   $mail->order->id === $order->id &&
                   $mail->envelope()->subject === $expectedSubject;
        });

        Mail::assertNotSent(OrderCancelledMail::class);
    }

    /**
     * Test that payment method falls back to request parameter if no payment record exists.
     */
    public function test_payment_method_fallback_to_request(): void
    {
        $order = Order::create([
            'ulid' => (string) \Illuminate\Support\Str::ulid(),
            'order_number' => 'ORD-1111',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'address' => '123 Main St',
            'city' => 'Metro',
            'state' => 'State',
            'zip' => '12345',
            'country' => 'India',
            'subtotal' => 100.00,
            'tax' => 10.00,
            'shipping_charge' => 5.00,
            'total' => 115.00,
            'status' => OrderStatus::NEW_ORDER,
        ]);

        // Scenario 1: No payment, no request input => None
        $this->assertEquals('None', $order->payment_method);

        // Scenario 2: No payment, with request input => cod
        request()->merge(['payment_method' => 'cod']);
        $this->assertEquals('Cash on Delivery', $order->payment_method);

        // Scenario 3: Payment exists => Payment's payment_method overrides request input
        $order->payments()->create([
            'payment_method' => 'Razorpay',
            'amount' => 115.00,
            'status' => \App\Enums\PaymentStatus::PAID,
        ]);

        // Clear relationship cache to force reloading
        $order->unsetRelation('payments');

        $this->assertEquals('Razorpay', $order->payment_method);
    }
}
