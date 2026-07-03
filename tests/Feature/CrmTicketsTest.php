<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SGCart\CrmTickets\Enums\TicketPriority;
use SGCart\CrmTickets\Enums\TicketStatus;
use SGCart\CrmTickets\Models\Ticket;
use SGCart\CrmTickets\Models\TicketStatus as TicketStatusModel;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CrmTicketsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed ticket statuses if not present
        if (TicketStatusModel::count() === 0) {
            TicketStatusModel::create(['name' => 'Open']);
            TicketStatusModel::create(['name' => 'Review']);
            TicketStatusModel::create(['name' => 'Resolve']);
            TicketStatusModel::create(['name' => 'Reject']);
        }

        // Create Spatie Permissions for tests
        if (class_exists(Permission::class)) {
            Permission::firstOrCreate(['name' => 'view tickets', 'guard_name' => 'web']);
            Permission::firstOrCreate(['name' => 'manage tickets', 'guard_name' => 'web']);
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        }
    }

    /**
     * Test customer can create a ticket linked to an order.
     */
    public function test_customer_can_create_ticket_linked_to_order(): void
    {
        Storage::fake('public');

        $customer = Customer::create([
            'name' => 'Customer A',
            'email' => 'customer.a@example.com',
            'password' => bcrypt('password'),
        ]);
        $customer->email_verified_at = now();
        $customer->phone_verified_at = now();
        $customer->save();

        $order = Order::create([
            'ulid' => (string) Str::ulid(),
            'order_number' => 'ORD-12345',
            'customer_id' => $customer->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'customer.a@example.com',
            'phone' => '1234567890',
            'address' => '123 Main St',
            'city' => 'Metropolis',
            'state' => 'NY',
            'zip' => '10001',
            'country' => 'USA',
            'subtotal' => 50.00,
            'tax' => 0.00,
            'shipping_charge' => 0.00,
            'total' => 50.00,
            'status' => 'Processing',
        ]);

        $this->actingAs($customer, 'customer');

        $images = [
            UploadedFile::fake()->image('issue1.png'),
            UploadedFile::fake()->image('issue2.png'),
        ];

        $response = $this->post(route('store.tickets.store'), [
            'subject' => 'Incorrect Items in Order',
            'description' => 'I received red shirts instead of blue.',
            'priority' => 'High',
            'ticketable_type' => Order::class,
            'ticketable_id' => $order->id,
            'images' => $images,
        ]);

        $response->assertStatus(302);
        
        $this->assertDatabaseHas('tickets', [
            'customer_id' => $customer->id,
            'subject' => 'Incorrect Items in Order',
            'priority' => 'High',
            'ticketable_type' => Order::class,
            'ticketable_id' => $order->id,
            'status_id' => 1, // Open
        ]);

        $ticket = Ticket::first();
        $this->assertCount(2, $ticket->attachments);
        Storage::disk('public')->assertExists($ticket->attachments->first()->file_path);
    }

    /**
     * Test customer can view own ticket and add comments with attachments.
     */
    public function test_customer_can_view_own_ticket_and_comment(): void
    {
        Storage::fake('public');

        $customer = Customer::create([
            'name' => 'Customer A',
            'email' => 'customer.a@example.com',
            'password' => bcrypt('password'),
        ]);
        $customer->email_verified_at = now();
        $customer->phone_verified_at = now();
        $customer->save();

        $ticket = Ticket::create([
            'customer_id' => $customer->id,
            'subject' => 'Help needed',
            'description' => 'Please reply',
            'priority' => 'Low',
            'ticketable_type' => Customer::class, // Poly testing
            'ticketable_id' => $customer->id,
            'status_id' => 1,
        ]);

        $this->actingAs($customer, 'customer');

        // View ticket
        $response = $this->get(route('store.tickets.show', $ticket->id));
        $response->assertStatus(200);
        $response->assertSee('Help needed');

        // Post a comment
        $commentImg = UploadedFile::fake()->image('details.jpg');
        $commentResponse = $this->post(route('store.tickets.comments.store', $ticket->id), [
            'body' => 'Adding more details',
            'images' => [$commentImg],
        ]);

        $commentResponse->assertStatus(302);
        $this->assertDatabaseHas('ticket_comments', [
            'ticket_id' => $ticket->id,
            'body' => 'Adding more details',
            'commentable_type' => Customer::class,
            'commentable_id' => $customer->id,
        ]);

        $comment = $ticket->comments()->first();
        $this->assertCount(1, $comment->attachments);
    }

    /**
     * Test customer cannot view another customer's ticket.
     */
    public function test_customer_cannot_view_other_customers_ticket(): void
    {
        $customerA = Customer::create([
            'name' => 'Customer A',
            'email' => 'customer.a@example.com',
            'password' => bcrypt('password'),
        ]);
        $customerA->email_verified_at = now();
        $customerA->phone_verified_at = now();
        $customerA->save();

        $customerB = Customer::create([
            'name' => 'Customer B',
            'email' => 'customer.b@example.com',
            'password' => bcrypt('password'),
        ]);
        $customerB->email_verified_at = now();
        $customerB->phone_verified_at = now();
        $customerB->save();

        $ticket = Ticket::create([
            'customer_id' => $customerA->id,
            'subject' => 'Private Issue',
            'description' => 'Top secret description',
            'ticketable_type' => Customer::class,
            'ticketable_id' => $customerA->id,
            'status_id' => 1,
        ]);

        $this->actingAs($customerB, 'customer');

        $response = $this->get(route('store.tickets.show', $ticket->id));
        $response->assertStatus(403);

        $commentResponse = $this->post(route('store.tickets.comments.store', $ticket->id), [
            'body' => 'Spam comment',
        ]);
        $commentResponse->assertStatus(403);
    }

    /**
     * Test admin permissions.
     */
    public function test_admin_permissions_to_view_and_manage_tickets(): void
    {
        Storage::fake('public');

        $customer = Customer::create([
            'name' => 'Customer A',
            'email' => 'customer.a@example.com',
            'password' => bcrypt('password'),
        ]);

        $ticket = Ticket::create([
            'customer_id' => $customer->id,
            'subject' => 'Need refund',
            'description' => 'Damaged item received',
            'ticketable_type' => Customer::class,
            'ticketable_id' => $customer->id,
            'status_id' => 1,
        ]);

        $viewOnlyAdmin = User::create([
            'name' => 'View Only Staff',
            'email' => 'view@admin.com',
            'password' => bcrypt('password'),
        ]);
        $viewOnlyAdmin->givePermissionTo('view tickets');

        $fullAdmin = User::create([
            'name' => 'Full Admin Staff',
            'email' => 'full@admin.com',
            'password' => bcrypt('password'),
        ]);
        $fullAdmin->givePermissionTo('view tickets', 'manage tickets');

        // 1. View only admin can view ticket index and show
        $this->actingAs($viewOnlyAdmin);
        $this->get(route('admin.tickets.index'))->assertStatus(200);
        $this->get(route('admin.tickets.show', $ticket->id))->assertStatus(200);

        // 2. View only admin cannot update status, priority or comment
        $this->post(route('admin.tickets.updateStatus', $ticket->id), ['status_id' => 2])->assertStatus(403);
        $this->post(route('admin.tickets.updatePriority', $ticket->id), ['priority' => 'Urgent'])->assertStatus(403);
        $this->post(route('admin.tickets.comments.store', $ticket->id), ['body' => 'Illegal reply'])->assertStatus(403);

        // 3. Full admin can perform all actions
        $this->actingAs($fullAdmin);
        
        $this->post(route('admin.tickets.updateStatus', $ticket->id), ['status_id' => 2])->assertStatus(302);
        $this->assertEquals(2, $ticket->refresh()->status_id);

        $this->post(route('admin.tickets.updatePriority', $ticket->id), ['priority' => 'Urgent'])->assertStatus(302);
        $this->assertEquals(TicketPriority::URGENT, $ticket->refresh()->priority);

        $replyImg = UploadedFile::fake()->image('staff-notes.png');
        $this->post(route('admin.tickets.comments.store', $ticket->id), [
            'body' => 'We are processing your refund request.',
            'images' => [$replyImg],
        ])->assertStatus(302);

        $this->assertDatabaseHas('ticket_comments', [
            'ticket_id' => $ticket->id,
            'body' => 'We are processing your refund request.',
            'commentable_type' => User::class,
            'commentable_id' => $fullAdmin->id,
        ]);
    }

    /**
     * Test customer can view order details and see the support tickets section.
     */
    public function test_customer_can_view_order_details_and_see_support_section(): void
    {
        $customer = Customer::create([
            'name' => 'Customer A',
            'email' => 'customer.a@example.com',
            'password' => bcrypt('password'),
        ]);
        $customer->email_verified_at = now();
        $customer->phone_verified_at = now();
        $customer->save();

        $order = Order::create([
            'ulid' => (string) Str::ulid(),
            'order_number' => 'ORD-999',
            'customer_id' => $customer->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'customer.a@example.com',
            'phone' => '1234567890',
            'address' => '123 Main St',
            'city' => 'Metropolis',
            'state' => 'NY',
            'zip' => '10001',
            'country' => 'USA',
            'subtotal' => 50.00,
            'tax' => 0.00,
            'shipping_charge' => 0.00,
            'total' => 50.00,
            'status' => 'Processing',
        ]);

        $ticket = Ticket::create([
            'customer_id' => $customer->id,
            'subject' => 'Delivery delay inquiry',
            'description' => 'Where is my order?',
            'priority' => 'Medium',
            'ticketable_type' => Order::class,
            'ticketable_id' => $order->id,
            'status_id' => 1,
        ]);

        $this->actingAs($customer, 'customer');

        $response = $this->get(route('store.account.order.view', $order->ulid));
        $response->assertStatus(200);

        // Assert support card section and ticket listing exist on the page
        $response->assertSee('Order Support');
        $response->assertSee('Raise Support Ticket');
        $response->assertSee('Delivery delay inquiry');
    }

    /**
     * Test admin can delete a single ticket.
     */
    public function test_admin_can_delete_ticket(): void
    {
        $customer = Customer::create([
            'name' => 'Customer A',
            'email' => 'customer.a@example.com',
            'password' => bcrypt('password'),
        ]);

        $ticket = Ticket::create([
            'customer_id' => $customer->id,
            'subject' => 'Ticket to delete',
            'description' => 'Delete me please',
            'ticketable_type' => Customer::class,
            'ticketable_id' => $customer->id,
            'status_id' => 1,
        ]);

        $viewOnlyAdmin = User::create([
            'name' => 'View Only',
            'email' => 'view.only@admin.com',
            'password' => bcrypt('password'),
        ]);
        $viewOnlyAdmin->givePermissionTo('view tickets');

        $fullAdmin = User::create([
            'name' => 'Full Admin',
            'email' => 'full.admin@admin.com',
            'password' => bcrypt('password'),
        ]);
        $fullAdmin->givePermissionTo('view tickets', 'manage tickets');

        // View-only admin should be forbidden from deleting
        $this->actingAs($viewOnlyAdmin);
        $response = $this->delete(route('admin.tickets.destroy', $ticket->id));
        $response->assertStatus(403);
        $this->assertDatabaseHas('tickets', ['id' => $ticket->id]);

        // Full admin should succeed
        $this->actingAs($fullAdmin);
        $response = $this->delete(route('admin.tickets.destroy', $ticket->id));
        $response->assertStatus(302);
        $this->assertDatabaseMissing('tickets', ['id' => $ticket->id]);
    }

    /**
     * Test admin can bulk delete tickets.
     */
    public function test_admin_can_bulk_delete_tickets(): void
    {
        $customer = Customer::create([
            'name' => 'Customer A',
            'email' => 'customer.a@example.com',
            'password' => bcrypt('password'),
        ]);

        $ticket1 = Ticket::create([
            'customer_id' => $customer->id,
            'subject' => 'Ticket 1',
            'description' => 'Desc 1',
            'ticketable_type' => Customer::class,
            'ticketable_id' => $customer->id,
            'status_id' => 1,
        ]);

        $ticket2 = Ticket::create([
            'customer_id' => $customer->id,
            'subject' => 'Ticket 2',
            'description' => 'Desc 2',
            'ticketable_type' => Customer::class,
            'ticketable_id' => $customer->id,
            'status_id' => 1,
        ]);

        $fullAdmin = User::create([
            'name' => 'Full Admin',
            'email' => 'full.admin@admin.com',
            'password' => bcrypt('password'),
        ]);
        $fullAdmin->givePermissionTo('view tickets', 'manage tickets');

        $this->actingAs($fullAdmin);

        // Bulk delete
        $response = $this->post(route('admin.tickets.bulkAction'), [
            'bulk_ids' => [$ticket1->id, $ticket2->id],
            'action' => 'delete',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseMissing('tickets', ['id' => $ticket1->id]);
        $this->assertDatabaseMissing('tickets', ['id' => $ticket2->id]);
    }
}
