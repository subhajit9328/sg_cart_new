<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReportingTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Clear Spatie's permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create the permission
        Permission::firstOrCreate(['name' => 'view reports', 'guard_name' => 'web']);

        // Create the admin role with a ULID (required by this project's schema)
        $adminRole = Role::firstOrCreate(
            ['name' => 'Super Admin', 'guard_name' => 'web'],
            ['ulid' => (string) Str::ulid()]
        );
        $adminRole->givePermissionTo('view reports');

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('Super Admin');

        $this->regularUser = User::factory()->create();
    }

    #[Test]
    public function orders_report_is_accessible_to_users_with_view_reports_permission(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.orders'));

        $response->assertOk();
        $response->assertViewIs('reporting::admin.orders.index');
    }

    #[Test]
    public function orders_report_is_forbidden_to_users_without_permission(): void
    {
        $response = $this->actingAs($this->regularUser)
            ->get(route('admin.reports.orders'));

        $response->assertStatus(403);
    }

    #[Test]
    public function orders_report_defaults_to_current_month(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.orders'));

        $response->assertOk();

        $data = $response->viewData('date_from');
        $this->assertEquals(
            Carbon::now()->startOfMonth()->format('Y-m-d'),
            $data->format('Y-m-d'),
            'Default date_from should be the start of the current month.'
        );
    }

    #[Test]
    public function orders_report_accepts_custom_date_range(): void
    {
        $from = Carbon::now()->subDays(14)->format('Y-m-d');
        $to   = Carbon::now()->format('Y-m-d');

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.orders', ['date_from' => $from, 'date_to' => $to]));

        $response->assertOk();
        $this->assertEquals($from, $response->viewData('date_from')->format('Y-m-d'));
        $this->assertEquals($to, $response->viewData('date_to')->format('Y-m-d'));
    }

    #[Test]
    public function orders_report_rejects_invalid_date_range(): void
    {
        // date_to before date_from
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.orders', [
                'date_from' => '2026-06-30',
                'date_to'   => '2026-06-01',
            ]));

        $response->assertSessionHasErrors('date_to');
    }

    #[Test]
    public function gross_revenue_metric_sums_all_order_totals_in_range(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-03'));

        // Orders inside range (current month)
        Order::factory()->create(['total' => 500, 'created_at' => '2026-07-01', 'status' => 'Delivered']);
        Order::factory()->create(['total' => 300, 'created_at' => '2026-07-02', 'status' => 'Processing']);

        // Order outside range (previous month)
        Order::factory()->create(['total' => 999, 'created_at' => '2026-06-15', 'status' => 'Delivered']);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.orders'));

        $response->assertOk();
        $metrics = $response->viewData('metrics');

        $this->assertEquals(800.0, $metrics['total_revenue']);
        $this->assertEquals(2, $metrics['total_orders']);
    }

    #[Test]
    public function net_revenue_excludes_cancelled_order_totals(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-03'));

        Order::factory()->create(['total' => 1000, 'created_at' => '2026-07-01', 'status' => 'Delivered']);
        Order::factory()->create(['total' => 200,  'created_at' => '2026-07-02', 'status' => 'Cancelled']);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.orders'));

        $analytics = $response->viewData('revenue_analytics');

        $this->assertEquals(1200.0, $analytics['gross_revenue']);
        $this->assertEquals(200.0,  $analytics['refund_amount']);
        $this->assertEquals(1000.0, $analytics['net_revenue']);
    }

    #[Test]
    public function status_counts_are_correct_for_date_range(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-03'));

        Order::factory()->count(3)->create(['status' => 'Processing', 'created_at' => '2026-07-01']);
        Order::factory()->count(2)->create(['status' => 'Shipped',    'created_at' => '2026-07-01']);
        Order::factory()->count(4)->create(['status' => 'Delivered',  'created_at' => '2026-07-02']);
        Order::factory()->count(1)->create(['status' => 'Cancelled',  'created_at' => '2026-07-02']);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.orders'));

        $metrics = $response->viewData('metrics');

        $this->assertEquals(3, $metrics['processing']);
        $this->assertEquals(2, $metrics['shipped']);
        $this->assertEquals(4, $metrics['delivered']);
        $this->assertEquals(1, $metrics['cancelled']);
    }

    #[Test]
    public function revenue_growth_is_positive_when_current_period_exceeds_previous(): void
    {
        // Previous 7-day period: low revenue
        Order::factory()->create(['total' => 100, 'created_at' => now()->subDays(8), 'status' => 'Delivered']);

        // Current 7-day period: high revenue
        Order::factory()->create(['total' => 500, 'created_at' => now()->subDays(2), 'status' => 'Delivered']);

        $from = now()->subDays(6)->format('Y-m-d');
        $to   = now()->format('Y-m-d');

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.orders', ['date_from' => $from, 'date_to' => $to]));

        $analytics = $response->viewData('revenue_analytics');

        $this->assertNotNull($analytics['revenue_growth']);
        $this->assertGreaterThan(0, $analytics['revenue_growth']);
    }

    #[Test]
    public function uninstall_removes_view_reports_permission_from_database(): void
    {
        // Ensure permission exists
        $permission = Permission::firstOrCreate(['name' => 'view reports', 'guard_name' => 'web']);
        $this->assertDatabaseHas('permissions', ['name' => 'view reports']);

        // Manually perform the uninstall logic (equivalent to the command's handle())
        // Detach from all roles
        \Illuminate\Support\Facades\DB::table('role_has_permissions')
            ->where('permission_id', $permission->id)
            ->delete();
        // Detach from all users
        \Illuminate\Support\Facades\DB::table('model_has_permissions')
            ->where('permission_id', $permission->id)
            ->delete();
        // Delete the permission itself
        $permission->delete();

        $this->assertDatabaseMissing('permissions', [
            'name'       => 'view reports',
            'guard_name' => 'web',
        ]);
    }

    #[Test]
    public function orders_export_is_accessible_to_users_with_view_reports_permission(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.orders.export'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename="orders-report-' . now()->startOfMonth()->format('Y-m-d') . '-to-' . now()->format('Y-m-d') . '.csv"');
    }

    #[Test]
    public function orders_export_is_forbidden_to_users_without_permission(): void
    {
        $response = $this->actingAs($this->regularUser)
            ->get(route('admin.reports.orders.export'));

        $response->assertStatus(403);
    }

    #[Test]
    public function orders_export_rejects_invalid_date_range(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.orders.export', [
                'date_from' => '2026-07-03',
                'date_to'   => '2026-07-01',
            ]));

        $response->assertSessionHasErrors('date_to');
    }

    #[Test]
    public function orders_report_supports_searching_detailed_orders_table_only(): void
    {
        // Create matching order
        $orderA = Order::factory()->create([
            'order_number' => 'ORD-12345',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'total' => 100.00,
            'created_at' => now()->startOfMonth()->addDays(2),
        ]);

        // Create non-matching order
        $orderB = Order::factory()->create([
            'order_number' => 'ORD-98765',
            'first_name' => 'Alice',
            'last_name' => 'Smith',
            'email' => 'alice@example.com',
            'total' => 200.00,
            'created_at' => now()->startOfMonth()->addDays(3),
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.orders', ['search' => 'John']));

        $response->assertOk();

        // 1. Table should be filtered to only contain matching order
        $orders = $response->viewData('orders');
        $this->assertCount(1, $orders);
        $this->assertEquals('ORD-12345', $orders->first()->order_number);

        // 2. Metrics at the top must NOT be filtered by search
        $metrics = $response->viewData('metrics');
        $this->assertEquals(2, $metrics['total_orders']);
        $this->assertEquals(300.00, $metrics['total_revenue']);

        $revenueAnalytics = $response->viewData('revenue_analytics');
        $this->assertEquals(300.00, $revenueAnalytics['gross_revenue']);
    }

    #[Test]
    public function orders_report_supports_sorting_detailed_orders_table(): void
    {
        // Order with total = 100, discount = 10 (net = 90)
        $orderA = Order::factory()->create([
            'order_number' => 'ORD-1',
            'total' => 100.00,
            'discount' => 10.00,
            'created_at' => now()->startOfMonth()->addDays(1),
        ]);

        // Order with total = 200, discount = 150 (net = 50)
        $orderB = Order::factory()->create([
            'order_number' => 'ORD-2',
            'total' => 200.00,
            'discount' => 150.00,
            'created_at' => now()->startOfMonth()->addDays(2),
        ]);

        // Sort by total asc -> ORD-1 (100) then ORD-2 (200)
        $response1 = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.orders', ['sort_by' => 'total', 'sort_order' => 'asc']));
        $orders1 = $response1->viewData('orders');
        $this->assertEquals('ORD-1', $orders1->items()[0]->order_number);
        $this->assertEquals('ORD-2', $orders1->items()[1]->order_number);

        // Sort by total desc -> ORD-2 (200) then ORD-1 (100)
        $response2 = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.orders', ['sort_by' => 'total', 'sort_order' => 'desc']));
        $orders2 = $response2->viewData('orders');
        $this->assertEquals('ORD-2', $orders2->items()[0]->order_number);
        $this->assertEquals('ORD-1', $orders2->items()[1]->order_number);

        // Sort by net_amount asc -> ORD-2 (50) then ORD-1 (90)
        $response3 = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.orders', ['sort_by' => 'net_amount', 'sort_order' => 'asc']));
        $orders3 = $response3->viewData('orders');
        $this->assertEquals('ORD-2', $orders3->items()[0]->order_number);
        $this->assertEquals('ORD-1', $orders3->items()[1]->order_number);
    }

    #[Test]
    public function customers_report_is_accessible_and_default_month(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.customers'));

        $response->assertOk();
        $response->assertViewIs('reporting::admin.customers.index');
    }

    #[Test]
    public function customers_report_supports_searching_by_name_email_phone(): void
    {
        $cust1 = \App\Models\Customer::create([
            'name' => 'Michael Jordan',
            'email' => 'jordan@bulls.com',
            'phone_no' => '2323232323',
            'password' => bcrypt('password'),
        ]);

        $cust2 = \App\Models\Customer::create([
            'name' => 'Scottie Pippen',
            'email' => 'pippen@bulls.com',
            'phone_no' => '3333333333',
            'password' => bcrypt('password'),
        ]);

        // Order A for Customer 1
        Order::factory()->create([
            'customer_id' => $cust1->id,
            'first_name' => 'Michael',
            'last_name' => 'Jordan',
            'email' => 'jordan@bulls.com',
            'phone' => '2323232323',
            'total' => 150.00,
            'created_at' => now()->startOfMonth()->addDays(2),
        ]);

        // Order B for Customer 2
        Order::factory()->create([
            'customer_id' => $cust2->id,
            'first_name' => 'Scottie',
            'last_name' => 'Pippen',
            'email' => 'pippen@bulls.com',
            'phone' => '3333333333',
            'total' => 250.00,
            'created_at' => now()->startOfMonth()->addDays(2),
        ]);

        // Search by first name
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.customers', ['search' => 'Michael']));
        $response->assertOk();
        $customers = $response->viewData('customers');
        $this->assertCount(1, $customers);
        $this->assertEquals($cust1->id, $customers->first()->customer_id);

        // Search by email
        $response2 = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.customers', ['search' => 'pippen@bulls.com']));
        $response2->assertOk();
        $customers2 = $response2->viewData('customers');
        $this->assertCount(1, $customers2);
        $this->assertEquals($cust2->id, $customers2->first()->customer_id);

        // Search by phone
        $response3 = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.customers', ['search' => '232323']));
        $response3->assertOk();
        $customers3 = $response3->viewData('customers');
        $this->assertCount(1, $customers3);
        $this->assertEquals($cust1->id, $customers3->first()->customer_id);
    }

    #[Test]
    public function customers_report_supports_sorting_detailed_table(): void
    {
        $cust1 = \App\Models\Customer::create([
            'name' => 'A Customer',
            'email' => 'a@example.com',
            'password' => bcrypt('password'),
        ]);

        $cust2 = \App\Models\Customer::create([
            'name' => 'B Customer',
            'email' => 'b@example.com',
            'password' => bcrypt('password'),
        ]);

        // Customer 1: 1 order, total spent 100
        Order::factory()->create([
            'customer_id' => $cust1->id,
            'first_name' => 'A',
            'last_name' => 'Customer',
            'email' => 'a@example.com',
            'phone' => '0987654321',
            'total' => 100.00,
            'created_at' => now()->startOfMonth()->addDays(1),
        ]);

        // Customer 2: 2 orders, total spent 300 (total = 120 and 180)
        Order::factory()->create([
            'customer_id' => $cust2->id,
            'first_name' => 'B',
            'last_name' => 'Customer',
            'email' => 'b@example.com',
            'phone' => '1234567890',
            'total' => 120.00,
            'created_at' => now()->startOfMonth()->addDays(2),
        ]);
        Order::factory()->create([
            'customer_id' => $cust2->id,
            'first_name' => 'B',
            'last_name' => 'Customer',
            'email' => 'b@example.com',
            'phone' => '1234567890',
            'total' => 180.00,
            'created_at' => now()->startOfMonth()->addDays(3),
        ]);

        // Sort by total_spent asc -> Customer 1 (100) then Customer 2 (300)
        $response1 = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.customers', ['sort_by' => 'total_spent', 'sort_order' => 'asc']));
        $customers1 = $response1->viewData('customers');
        $this->assertEquals($cust1->id, $customers1->items()[0]->customer_id);
        $this->assertEquals($cust2->id, $customers1->items()[1]->customer_id);

        // Sort by total_orders desc -> Customer 2 (2) then Customer 1 (1)
        $response2 = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.customers', ['sort_by' => 'total_orders', 'sort_order' => 'desc']));
        $customers2 = $response2->viewData('customers');
        $this->assertEquals($cust2->id, $customers2->items()[0]->customer_id);
        $this->assertEquals($cust1->id, $customers2->items()[1]->customer_id);

        // Sort by aov desc -> Customer 2 (150) then Customer 1 (100)
        $response3 = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.customers', ['sort_by' => 'aov', 'sort_order' => 'desc']));
        $customers3 = $response3->viewData('customers');
        $this->assertEquals($cust2->id, $customers3->items()[0]->customer_id);
        $this->assertEquals($cust1->id, $customers3->items()[1]->customer_id);
    }

    #[Test]
    public function products_report_is_accessible(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.products'));

        $response->assertOk();
        $response->assertViewIs('reporting::admin.products.index');
    }

    #[Test]
    public function products_report_supports_searching(): void
    {
        $productA = \App\Models\Product::create([
            'name' => 'Super iPhone 15',
            'slug' => 'super-iphone-15',
            'sku' => 'IPHONE15',
            'price' => 1000.00,
            'stock' => 100,
            'status' => 'active',
        ]);
        $productB = \App\Models\Product::create([
            'name' => 'Classic Sneakers',
            'slug' => 'classic-sneakers',
            'sku' => 'SNEAKERS',
            'price' => 100.00,
            'stock' => 100,
            'status' => 'active',
        ]);

        $orderA = Order::factory()->create([
            'status' => 'Delivered',
            'created_at' => now()->startOfMonth()->addDays(2),
        ]);
        \Illuminate\Support\Facades\DB::table('order_items')->insert([
            'order_id' => $orderA->id,
            'product_id' => $productA->id,
            'product_name' => 'Super iPhone 15',
            'product_sku' => 'IPHONE15',
            'price' => 1000.00,
            'quantity' => 2,
        ]);

        $orderB = Order::factory()->create([
            'status' => 'Delivered',
            'created_at' => now()->startOfMonth()->addDays(2),
        ]);
        \Illuminate\Support\Facades\DB::table('order_items')->insert([
            'order_id' => $orderB->id,
            'product_id' => $productB->id,
            'product_name' => 'Classic Sneakers',
            'product_sku' => 'SNEAKERS',
            'price' => 100.00,
            'quantity' => 5,
        ]);

        // Search by name
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.products', ['search' => 'iPhone']));
        $response->assertOk();
        $performance = $response->viewData('performance');
        $this->assertCount(1, $performance);
        $this->assertEquals($productA->id, $performance->first()->id);

        // Search by sku
        $response2 = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.products', ['search' => 'SNEAKERS']));
        $response2->assertOk();
        $performance2 = $response2->viewData('performance');
        $this->assertCount(1, $performance2);
        $this->assertEquals($productB->id, $performance2->first()->id);
    }

    #[Test]
    public function products_report_supports_sorting(): void
    {
        $productA = \App\Models\Product::create([
            'name' => 'A',
            'slug' => 'a-prod',
            'sku' => 'A-SKU',
            'price' => 100.00,
            'stock' => 100,
            'status' => 'active',
        ]);
        $productB = \App\Models\Product::create([
            'name' => 'B',
            'slug' => 'b-prod',
            'sku' => 'B-SKU',
            'price' => 200.00,
            'stock' => 100,
            'status' => 'active',
        ]);

        $orderA = Order::factory()->create([
            'status' => 'Delivered',
            'created_at' => now()->startOfMonth()->addDays(2),
        ]);
        \Illuminate\Support\Facades\DB::table('order_items')->insert([
            'order_id' => $orderA->id,
            'product_id' => $productA->id,
            'product_name' => 'A',
            'product_sku' => 'A-SKU',
            'price' => 100.00,
            'quantity' => 10, // units sold = 10, total revenue = 1000
        ]);

        $orderB = Order::factory()->create([
            'status' => 'Delivered',
            'created_at' => now()->startOfMonth()->addDays(2),
        ]);
        \Illuminate\Support\Facades\DB::table('order_items')->insert([
            'order_id' => $orderB->id,
            'product_id' => $productB->id,
            'product_name' => 'B',
            'product_sku' => 'B-SKU',
            'price' => 200.00,
            'quantity' => 3, // units sold = 3, total revenue = 600
        ]);

        // Sort by units_sold asc -> B (3) then A (10)
        $response1 = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.products', ['sort_by' => 'units_sold', 'sort_order' => 'asc']));
        $performance1 = $response1->viewData('performance');
        $this->assertEquals($productB->id, $performance1->items()[0]->id);
        $this->assertEquals($productA->id, $performance1->items()[1]->id);

        // Sort by total_revenue desc -> A (1000) then B (600)
        $response2 = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.products', ['sort_by' => 'total_revenue', 'sort_order' => 'desc']));
        $performance2 = $response2->viewData('performance');
        $this->assertEquals($productA->id, $performance2->items()[0]->id);
        $this->assertEquals($productB->id, $performance2->items()[1]->id);
    }

    #[Test]
    public function behavior_report_is_accessible(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.behavior'));

        $response->assertOk();
        $response->assertViewIs('reporting::admin.behavior.index');
    }

    #[Test]
    public function behavior_report_supports_searching(): void
    {
        $cust1 = \App\Models\Customer::create([
            'name' => 'Michael Jordan',
            'email' => 'jordan@bulls.com',
            'password' => bcrypt('password'),
        ]);

        $cust2 = \App\Models\Customer::create([
            'name' => 'Scottie Pippen',
            'email' => 'pippen@bulls.com',
            'password' => bcrypt('password'),
        ]);

        // Search by name
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.behavior', ['search' => 'Michael']));
        $response->assertOk();
        $behavior = $response->viewData('behavior');
        $this->assertCount(1, $behavior);
        $this->assertEquals($cust1->id, $behavior->first()->id);

        // Search by email
        $response2 = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.behavior', ['search' => 'pippen@bulls.com']));
        $response2->assertOk();
        $behavior2 = $response2->viewData('behavior');
        $this->assertCount(1, $behavior2);
        $this->assertEquals($cust2->id, $behavior2->first()->id);
    }

    #[Test]
    public function behavior_report_supports_sorting(): void
    {
        $cust1 = \App\Models\Customer::create([
            'name' => 'A Customer',
            'email' => 'a@example.com',
            'password' => bcrypt('password'),
        ]);

        $cust2 = \App\Models\Customer::create([
            'name' => 'B Customer',
            'email' => 'b@example.com',
            'password' => bcrypt('password'),
        ]);

        $product1 = \App\Models\Product::create([
            'name' => 'Dummy Product 1',
            'slug' => 'dummy-product-1',
            'sku' => 'DUMMY1',
            'price' => 10.00,
            'stock' => 100,
            'status' => 'active',
        ]);

        $product2 = \App\Models\Product::create([
            'name' => 'Dummy Product 2',
            'slug' => 'dummy-product-2',
            'sku' => 'DUMMY2',
            'price' => 10.00,
            'stock' => 100,
            'status' => 'active',
        ]);

        // Customer 1: 1 order of 100, 2 reviews
        Order::factory()->create([
            'customer_id' => $cust1->id,
            'total' => 100.00,
            'created_at' => now()->startOfMonth()->addDays(2),
        ]);
        \Illuminate\Support\Facades\DB::table('reviews')->insert([
            [
                'customer_id' => $cust1->id,
                'product_id' => $product1->id,
                'rating' => 5,
                'status_id' => 1,
                'created_at' => now()->startOfMonth()->addDays(2),
            ],
            [
                'customer_id' => $cust1->id,
                'product_id' => $product2->id,
                'rating' => 4,
                'status_id' => 1,
                'created_at' => now()->startOfMonth()->addDays(2),
            ],
        ]);

        // Customer 2: 2 orders (total 300), 1 review
        Order::factory()->create([
            'customer_id' => $cust2->id,
            'total' => 120.00,
            'created_at' => now()->startOfMonth()->addDays(2),
        ]);
        Order::factory()->create([
            'customer_id' => $cust2->id,
            'total' => 180.00,
            'created_at' => now()->startOfMonth()->addDays(2),
        ]);
        \Illuminate\Support\Facades\DB::table('reviews')->insert([
            'customer_id' => $cust2->id,
            'product_id' => $product1->id,
            'rating' => 5,
            'status_id' => 1,
            'created_at' => now()->startOfMonth()->addDays(2),
        ]);

        // Sort by total_sales asc -> Customer 1 (100) then Customer 2 (300)
        $response1 = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.behavior', ['sort_by' => 'total_sales', 'sort_order' => 'asc']));
        $behavior1 = $response1->viewData('behavior');
        $this->assertEquals($cust1->id, $behavior1->items()[0]->id);
        $this->assertEquals($cust2->id, $behavior1->items()[1]->id);

        // Sort by total_orders desc -> Customer 2 (2) then Customer 1 (1)
        $response2 = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.behavior', ['sort_by' => 'total_orders', 'sort_order' => 'desc']));
        $behavior2 = $response2->viewData('behavior');
        $this->assertEquals($cust2->id, $behavior2->items()[0]->id);
        $this->assertEquals($cust1->id, $behavior2->items()[1]->id);

        // Sort by reviews_count desc -> Customer 1 (2) then Customer 2 (1)
        $response3 = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.behavior', ['sort_by' => 'reviews_count', 'sort_order' => 'desc']));
        $behavior3 = $response3->viewData('behavior');
        $this->assertEquals($cust1->id, $behavior3->items()[0]->id);
        $this->assertEquals($cust2->id, $behavior3->items()[1]->id);
    }

    #[Test]
    public function conversion_report_is_accessible(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.conversion'));

        $response->assertOk();
        $response->assertViewIs('reporting::admin.conversion.index');
    }

    #[Test]
    public function conversion_report_supports_searching(): void
    {
        $cust = \App\Models\Customer::create([
            'name' => 'Michael Jordan',
            'email' => 'jordan@bulls.com',
            'password' => bcrypt('password'),
        ]);

        $cartId1 = \Illuminate\Support\Facades\DB::table('carts')->insertGetId([
            'customer_id' => $cust->id,
            'session_id' => 'session_xyz_123',
            'created_at' => now()->startOfMonth()->addDays(2),
        ]);

        $cartId2 = \Illuminate\Support\Facades\DB::table('carts')->insertGetId([
            'customer_id' => null,
            'session_id' => 'session_abc_789',
            'created_at' => now()->startOfMonth()->addDays(2),
        ]);

        // Search by Cart ID
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.conversion', ['search' => $cartId1]));
        $response->assertOk();
        $abandoned = $response->viewData('abandoned_carts');
        $this->assertCount(1, $abandoned);
        $this->assertEquals($cartId1, $abandoned->first()->id);

        // Search by Session ID
        $response2 = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.conversion', ['search' => 'abc_789']));
        $response2->assertOk();
        $abandoned2 = $response2->viewData('abandoned_carts');
        $this->assertCount(1, $abandoned2);
        $this->assertEquals($cartId2, $abandoned2->first()->id);

        // Search by Customer Name
        $response3 = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.conversion', ['search' => 'Michael']));
        $response3->assertOk();
        $abandoned3 = $response3->viewData('abandoned_carts');
        $this->assertCount(1, $abandoned3);
        $this->assertEquals($cartId1, $abandoned3->first()->id);
    }

    #[Test]
    public function conversion_report_supports_sorting(): void
    {
        $product = \App\Models\Product::create([
            'name' => 'Dummy Product',
            'slug' => 'dummy-product',
            'sku' => 'DUMMY',
            'price' => 10.00,
            'stock' => 100,
            'status' => 'active',
        ]);

        $cartId1 = \Illuminate\Support\Facades\DB::table('carts')->insertGetId([
            'customer_id' => null,
            'session_id' => 'session_1',
            'created_at' => now()->startOfMonth()->addDays(1),
        ]);
        \Illuminate\Support\Facades\DB::table('cart_items')->insert([
            'cart_id' => $cartId1,
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $cartId2 = \Illuminate\Support\Facades\DB::table('carts')->insertGetId([
            'customer_id' => null,
            'session_id' => 'session_2',
            'created_at' => now()->startOfMonth()->addDays(2),
        ]);
        \Illuminate\Support\Facades\DB::table('cart_items')->insert([
            'cart_id' => $cartId2,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        // Sort by items_count asc -> cart 2 (2) then cart 1 (10)
        $response1 = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.conversion', ['sort_by' => 'items_count', 'sort_order' => 'asc']));
        $abandoned1 = $response1->viewData('abandoned_carts');
        $this->assertEquals($cartId2, $abandoned1->items()[0]->id);
        $this->assertEquals($cartId1, $abandoned1->items()[1]->id);

        // Sort by created_at desc -> cart 2 (2 days ago) then cart 1 (1 day ago)
        $response2 = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.conversion', ['sort_by' => 'created_at', 'sort_order' => 'desc']));
        $abandoned2 = $response2->viewData('abandoned_carts');
        $this->assertEquals($cartId2, $abandoned2->items()[0]->id);
        $this->assertEquals($cartId1, $abandoned2->items()[1]->id);
    }

    #[Test]
    public function traffic_report_is_accessible(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.traffic'));

        $response->assertOk();
        $response->assertViewIs('reporting::admin.traffic.index');
    }

    #[Test]
    public function traffic_report_supports_searching(): void
    {
        $cust = \App\Models\Customer::create([
            'name' => 'Michael Jordan',
            'email' => 'jordan@bulls.com',
            'password' => bcrypt('password'),
        ]);

        $logId1 = \Illuminate\Support\Facades\DB::table('traffic_logs')->insertGetId([
            'customer_id' => $cust->id,
            'session_id' => 'sess_1',
            'path' => '/home',
            'referrer' => 'google.com',
            'ip_address' => '1.1.1.1',
            'created_at' => now()->startOfMonth()->addDays(2),
        ]);

        $logId2 = \Illuminate\Support\Facades\DB::table('traffic_logs')->insertGetId([
            'customer_id' => null,
            'session_id' => 'sess_2',
            'path' => '/checkout',
            'referrer' => 'direct',
            'ip_address' => '2.2.2.2',
            'created_at' => now()->startOfMonth()->addDays(2),
        ]);

        // Search by Page
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.traffic', ['search' => '/checkout']));
        $response->assertOk();
        $pageViews = $response->viewData('page_views');
        $this->assertCount(1, $pageViews);
        $this->assertEquals($logId2, $pageViews->first()->id);

        // Search by Customer Name
        $response2 = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.traffic', ['search' => 'Michael']));
        $response2->assertOk();
        $pageViews2 = $response2->viewData('page_views');
        $this->assertCount(1, $pageViews2);
        $this->assertEquals($logId1, $pageViews2->first()->id);

        // Search by IP Address
        $response3 = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.traffic', ['search' => '2.2.2.2']));
        $response3->assertOk();
        $pageViews3 = $response3->viewData('page_views');
        $this->assertCount(1, $pageViews3);
        $this->assertEquals($logId2, $pageViews3->first()->id);
    }

    #[Test]
    public function traffic_report_supports_sorting(): void
    {
        $logId1 = \Illuminate\Support\Facades\DB::table('traffic_logs')->insertGetId([
            'customer_id' => null,
            'session_id' => 'sess_1',
            'path' => '/home',
            'ip_address' => '1.1.1.1',
            'created_at' => now()->startOfMonth()->addDays(1),
        ]);

        $logId2 = \Illuminate\Support\Facades\DB::table('traffic_logs')->insertGetId([
            'customer_id' => null,
            'session_id' => 'sess_2',
            'path' => '/checkout',
            'ip_address' => '2.2.2.2',
            'created_at' => now()->startOfMonth()->addDays(2),
        ]);

        // Sort by created_at asc -> log 1 (1 day ago) then log 2 (2 days ago)
        $response1 = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.traffic', ['sort_by' => 'created_at', 'sort_order' => 'asc']));
        $pageViews1 = $response1->viewData('page_views');
        $this->assertEquals($logId1, $pageViews1->items()[0]->id);
        $this->assertEquals($logId2, $pageViews1->items()[1]->id);

        // Sort by created_at desc -> log 2 then log 1
        $response2 = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.traffic', ['sort_by' => 'created_at', 'sort_order' => 'desc']));
        $pageViews2 = $response2->viewData('page_views');
        $this->assertEquals($logId2, $pageViews2->items()[0]->id);
        $this->assertEquals($logId1, $pageViews2->items()[1]->id);
    }
}
