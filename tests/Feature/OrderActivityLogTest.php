<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrderActivityLogTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that creating an order creates an activity log.
     */
    public function test_creating_order_logs_activity(): void
    {
        $customer = Customer::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($customer, 'customer');

        $order = Order::create([
            'ulid' => (string) Str::ulid(),
            'order_number' => 'ORD-1234',
            'customer_id' => $customer->id,
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
            'status' => 'Processing',
        ]);

        $this->assertDatabaseHas('activity_log', [
            'event' => 'order.created',
            'subject_type' => Order::class,
            'subject_id' => $order->id,
            'causer_type' => Customer::class,
            'causer_id' => $customer->id,
        ]);

        $log = ActivityLog::where('event', 'order.created')->first();
        $this->assertEquals('Order was created', $log->formatted_action);
        $this->assertEquals('Customer: Jane Doe', $log->causer_label);
    }

    /**
     * Test that updating order status logs activity.
     */
    public function test_updating_order_status_logs_activity(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin User',
        ]);

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
            'status' => 'Processing',
        ]);

        // Act as admin
        $this->actingAs($admin);

        $order->update(['status' => 'Shipped']);

        $this->assertDatabaseHas('activity_log', [
            'event' => 'order.status_updated',
            'subject_type' => Order::class,
            'subject_id' => $order->id,
            'causer_type' => User::class,
            'causer_id' => $admin->id,
        ]);

        $log = ActivityLog::where('event', 'order.status_updated')->first();
        $this->assertStringContainsString('Status changed from', $log->formatted_action);
        $this->assertStringContainsString('Processing', $log->formatted_action);
        $this->assertStringContainsString('Shipped', $log->formatted_action);
        $this->assertEquals('Admin: Admin User', $log->causer_label);
    }

    /**
     * Test that creating a payment logs activity.
     */
    public function test_creating_payment_logs_activity(): void
    {
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
            'status' => 'Processing',
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'Razorpay',
            'amount' => 115.00,
            'status' => 'Paid',
            'transaction_id' => 'pay_123456',
        ]);

        $this->assertDatabaseHas('activity_log', [
            'event' => 'order.payment_created',
            'subject_type' => Order::class,
            'subject_id' => $order->id,
        ]);

        $log = ActivityLog::where('event', 'order.payment_created')->first();
        $this->assertStringContainsString('Payment of', $log->formatted_action);
        $this->assertStringContainsString('₹115.00', $log->formatted_action);
        $this->assertStringContainsString('Razorpay', $log->formatted_action);
        $this->assertStringContainsString('Paid', $log->formatted_action);
    }

    /**
     * Test that updating a payment status logs activity.
     */
    public function test_updating_payment_status_logs_activity(): void
    {
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
            'status' => 'Processing',
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'Manual Update',
            'amount' => 115.00,
            'status' => 'Pending',
        ]);

        $payment->update(['status' => 'Paid']);

        $this->assertDatabaseHas('activity_log', [
            'event' => 'order.payment_updated',
            'subject_type' => Order::class,
            'subject_id' => $order->id,
        ]);

        $log = ActivityLog::where('event', 'order.payment_updated')->first();
        $this->assertStringContainsString('Payment status changed from', $log->formatted_action);
        $this->assertStringContainsString('Pending', $log->formatted_action);
        $this->assertStringContainsString('Paid', $log->formatted_action);
    }
}
