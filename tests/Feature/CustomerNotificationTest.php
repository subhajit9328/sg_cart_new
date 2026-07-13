<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use App\Enums\OrderStatus;
use App\Helpers\NotificationHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use SGCart\CrmTickets\Models\Ticket;
use SGCart\CrmTickets\Models\TicketComment;
use Tests\TestCase;

class CustomerNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed ticket statuses if package is installed and statuses are empty
        if (class_exists(\SGCart\CrmTickets\Models\TicketStatus::class) && \SGCart\CrmTickets\Models\TicketStatus::count() === 0) {
            \SGCart\CrmTickets\Models\TicketStatus::create(['name' => 'Open']);
            \SGCart\CrmTickets\Models\TicketStatus::create(['name' => 'Review']);
            \SGCart\CrmTickets\Models\TicketStatus::create(['name' => 'Resolve']);
            \SGCart\CrmTickets\Models\TicketStatus::create(['name' => 'Reject']);
        }
    }

    /**
     * Test NotificationHelper can send notifications to customer.
     */
    public function test_notification_helper_can_send_to_customer(): void
    {
        $customer = Customer::create([
            'name' => 'Jane Customer',
            'email' => 'jane@example.com',
            'password' => bcrypt('password'),
        ]);

        NotificationHelper::sendToCustomer(
            $customer,
            'Test Title',
            'Test Message',
            '/some-url',
            'info',
            'fa-info'
        );

        $this->assertCount(1, $customer->notifications);
        $notification = $customer->notifications->first();
        $this->assertEquals('Test Title', $notification->data['title']);
        $this->assertEquals('Test Message', $notification->data['message']);
        $this->assertEquals('/some-url', $notification->data['url']);
    }

    /**
     * Test order status update triggers notification.
     */
    public function test_order_status_changes_trigger_notifications(): void
    {
        $customer = Customer::create([
            'name' => 'Jane Customer',
            'email' => 'jane@example.com',
            'password' => bcrypt('password'),
        ]);

        $order = Order::create([
            'ulid' => (string) Str::ulid(),
            'order_number' => 'ORD-TEST101',
            'customer_id' => $customer->id,
            'first_name' => 'Jane',
            'last_name' => 'Customer',
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

        // When created, customer should already have 1 notification: "Order Placed"
        $this->assertCount(1, $customer->notifications);
        $this->assertNotNull($customer->notifications->firstWhere('data.title', 'Order Placed'));

        // Processing status
        $order->update(['status' => OrderStatus::PROCESSED]);
        $customer->refresh();
        $this->assertCount(2, $customer->notifications);
        $this->assertNotNull($customer->notifications->firstWhere('data.title', 'Order Processing'));

        // Shipped status
        $order->update(['status' => OrderStatus::SHIPPED]);
        $customer->refresh();
        $this->assertCount(3, $customer->notifications);
        $this->assertNotNull($customer->notifications->firstWhere('data.title', 'Order Shipped'));

        // Out for delivery status
        $order->update(['status' => OrderStatus::OUT_FOR_DELIVERY]);
        $customer->refresh();
        $this->assertCount(4, $customer->notifications);
        $this->assertNotNull($customer->notifications->firstWhere('data.title', 'Out for Delivery'));

        // Delivered status
        $order->update(['status' => OrderStatus::DELIVERED]);
        $customer->refresh();
        $this->assertCount(5, $customer->notifications);
        $this->assertNotNull($customer->notifications->firstWhere('data.title', 'Order Delivered'));

        // Cancelled status
        $order->update(['status' => OrderStatus::CANCELLED]);
        $customer->refresh();
        $this->assertCount(6, $customer->notifications);
        $this->assertNotNull($customer->notifications->firstWhere('data.title', 'Order Cancelled'));
    }

    /**
     * Test ticket comment creation triggers notifications.
     */
    public function test_crm_ticket_comments_trigger_notifications(): void
    {
        if (!class_exists(Ticket::class) || !class_exists(TicketComment::class)) {
            $this->markTestSkipped('CRM Tickets package is not installed.');
        }

        $customer = Customer::create([
            'name' => 'Jane Customer',
            'email' => 'jane@example.com',
            'password' => bcrypt('password'),
        ]);

        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $ticket = Ticket::create([
            'customer_id' => $customer->id,
            'subject' => 'Support Request',
            'description' => 'Need help',
            'priority' => 'Medium',
            'ticketable_type' => Customer::class,
            'ticketable_id' => $customer->id,
            'status_id' => 1,
        ]);

        // 1. Customer comments -> Should notify Admin
        $customerComment = TicketComment::create([
            'ticket_id' => $ticket->id,
            'body' => 'I have a question',
            'commentable_type' => Customer::class,
            'commentable_id' => $customer->id,
        ]);

        // Admin should have a notification
        $this->assertCount(1, $admin->notifications);
        $this->assertEquals('New Ticket Comment', $admin->notifications->first()->data['title']);
        $this->assertStringContainsString('I have a question', $admin->notifications->first()->data['message']);

        // 2. Admin comments -> Should notify Customer
        $adminComment = TicketComment::create([
            'ticket_id' => $ticket->id,
            'body' => 'Here is your answer',
            'commentable_type' => User::class,
            'commentable_id' => $admin->id,
        ]);

        // Customer should have a notification
        $this->assertCount(1, $customer->notifications);
        $this->assertEquals('New Ticket Comment', $customer->notifications->first()->data['title']);
        $this->assertStringContainsString('Here is your answer', $customer->notifications->first()->data['message']);
    }

    /**
     * Test crm ticket status change triggers notification.
     */
    public function test_crm_ticket_status_change_triggers_notification(): void
    {
        if (!class_exists(Ticket::class)) {
            $this->markTestSkipped('CRM Tickets package is not installed.');
        }

        $customer = Customer::create([
            'name' => 'Jane Customer',
            'email' => 'jane@example.com',
            'password' => bcrypt('password'),
        ]);

        $ticket = Ticket::create([
            'customer_id' => $customer->id,
            'subject' => 'Support Request',
            'description' => 'Need help',
            'priority' => 'Medium',
            'ticketable_type' => Customer::class,
            'ticketable_id' => $customer->id,
            'status_id' => 1,
        ]);

        $this->assertCount(0, $customer->notifications);

        // Fetch or create a second status for status change
        $newStatus = \SGCart\CrmTickets\Models\TicketStatus::firstOrCreate(['name' => 'Resolve']);

        // Update ticket status
        $ticket->update(['status_id' => $newStatus->id]);

        $customer->refresh();
        $this->assertCount(1, $customer->notifications);
        $this->assertEquals('Ticket Status Updated', $customer->notifications->first()->data['title']);
        $this->assertStringContainsString("status has been changed to 'Resolve'", $customer->notifications->first()->data['message']);
    }

    /**
     * Test notifications controller action & endpoints.
     */
    public function test_customer_can_read_and_manage_notifications_via_endpoints(): void
    {
        $customer = Customer::create([
            'name' => 'Jane Customer',
            'email' => 'jane@example.com',
            'password' => bcrypt('password'),
        ]);
        $customer->email_verified_at = now();
        $customer->phone_verified_at = now();
        $customer->save();

        NotificationHelper::sendToCustomer($customer, 'Title 1', 'Message 1', '/url1', 'info', 'fa-info');
        NotificationHelper::sendToCustomer($customer, 'Title 2', 'Message 2', '/url2', 'success', 'fa-check');

        $this->actingAs($customer, 'customer');

        // Fetch notifications JSON
        $response = $this->getJson(route('store.account.notifications.json'));
        $response->assertStatus(200);
        $response->assertJsonCount(2, 'notifications');
        $response->assertJsonPath('unread_count', 2);

        // Fetch paginated all list
        $allViewResponse = $this->get(route('store.account', 'notifications'));
        $allViewResponse->assertStatus(200);
        $allViewResponse->assertSee('Title 1');
        $allViewResponse->assertSee('Title 2');

        // Mark single read
        $notifId = $customer->unreadNotifications->first()->id;
        $readResponse = $this->post(route('store.account.notifications.read', $notifId));
        $readResponse->assertStatus(302); // Redirect back
        $this->assertEquals(1, $customer->fresh()->unreadNotifications()->count());

        // Mark all read
        $readAllResponse = $this->post(route('store.account.notifications.read-all'));
        $readAllResponse->assertStatus(302);
        $this->assertEquals(0, $customer->fresh()->unreadNotifications()->count());
    }
}
