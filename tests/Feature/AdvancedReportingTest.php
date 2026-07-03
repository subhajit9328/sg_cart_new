<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\OrderItem;
use App\Models\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdvancedReportingTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::firstOrCreate(['name' => 'view reports', 'guard_name' => 'web']);

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
    public function customers_report_is_accessible_to_authorized_users(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.customers'));

        $response->assertOk();
        $response->assertViewIs('reporting::admin.customers.index');
    }

    #[Test]
    public function customers_report_is_forbidden_to_unauthorized_users(): void
    {
        $response = $this->actingAs($this->regularUser)
            ->get(route('admin.reports.customers'));

        $response->assertStatus(403);
    }

    #[Test]
    public function customers_report_export_downloads_csv(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.customers.export'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    #[Test]
    public function revenue_report_is_accessible_and_loads_correct_views(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.revenue'));

        $response->assertOk();
        $response->assertViewIs('reporting::admin.revenue.index');
    }

    #[Test]
    public function revenue_report_export_downloads_csv(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.revenue.export'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    #[Test]
    public function product_performance_report_loads_successfully(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.products'));

        $response->assertOk();
        $response->assertViewIs('reporting::admin.products.index');
    }

    #[Test]
    public function product_performance_export_downloads_csv(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.products.export'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    #[Test]
    public function customer_behavior_report_loads_successfully(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.behavior'));

        $response->assertOk();
        $response->assertViewIs('reporting::admin.behavior.index');
    }

    #[Test]
    public function customer_behavior_export_downloads_csv(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.behavior.export'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    #[Test]
    public function funnel_report_loads_successfully(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.conversion'));

        $response->assertOk();
        $response->assertViewIs('reporting::admin.conversion.index');
    }

    #[Test]
    public function funnel_report_export_downloads_csv(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.conversion.export'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    #[Test]
    public function traffic_report_loads_successfully(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.traffic'));

        $response->assertOk();
        $response->assertViewIs('reporting::admin.traffic.index');
    }

    #[Test]
    public function traffic_report_export_downloads_csv(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.traffic.export'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    #[Test]
    public function product_performance_filters_by_date_range(): void
    {
        $product = Product::create([
            'name' => 'Unique Testing Product',
            'sku' => 'TEST-PERF-1',
            'price' => 100.00,
            'stock' => 10,
        ]);

        // Order 1: Outside date range (5 days ago)
        $order1 = Order::factory()->create([
            'order_number' => 'ORD-PERF-OLD',
            'created_at' => Carbon::now()->subDays(5),
            'subtotal' => 500.00,
            'total' => 500.00,
            'status' => 'Delivered',
        ]);
        OrderItem::create([
            'order_id' => $order1->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => $product->price,
            'quantity' => 5,
        ]);

        // Order 2: Inside date range (Today)
        $order2 = Order::factory()->create([
            'order_number' => 'ORD-PERF-NEW',
            'created_at' => Carbon::now(),
            'subtotal' => 200.00,
            'total' => 200.00,
            'status' => 'Delivered',
        ]);
        OrderItem::create([
            'order_id' => $order2->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => $product->price,
            'quantity' => 2,
        ]);

        // Hit product performance report for TODAY only
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.products', [
                'date_from' => Carbon::now()->format('Y-m-d'),
                'date_to' => Carbon::now()->format('Y-m-d'),
            ]));

        $response->assertOk();

        // Retrieve the performance records passed to the view
        $viewData = $response->original->getData();
        $performance = $viewData['performance']->items();

        // Find our testing product
        $perfItem = collect($performance)->firstWhere('id', $product->id);

        $this->assertNotNull($perfItem);
        // Correctly filtered units_sold should be 2, not 7!
        $this->assertEquals(2, $perfItem->units_sold);
        $this->assertEquals(200.00, $perfItem->total_revenue);
    }

    #[Test]
    public function conversion_funnel_counts_logged_in_purchases(): void
    {
        $customer = Customer::create([
            'name' => 'John Doe Customer',
            'email' => 'customer@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create a cart for the logged-in customer (no session_id)
        $cart = Cart::create([
            'customer_id' => $customer->id,
            'session_id' => null,
            'created_at' => Carbon::now(),
        ]);

        // Create a completed order for the same customer (no matching session_id, but matching customer_id)
        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'session_id' => 'different-session-abc',
            'created_at' => Carbon::now(),
            'status' => 'Delivered',
        ]);

        // Hit funnel report for TODAY
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.conversion', [
                'date_from' => Carbon::now()->format('Y-m-d'),
                'date_to' => Carbon::now()->format('Y-m-d'),
            ]));

        $response->assertOk();

        $viewData = $response->original->getData();
        $funnel = $viewData['funnel'];
        $abandonedCarts = $viewData['abandoned_carts']->items();

        // 1. Abandoned carts should be 0 because the customer completed the purchase
        $this->assertEquals(0, $funnel['carts_abandoned']);
        $this->assertEmpty($abandonedCarts);

        // 2. Conversion rate should be computed correctly
        $this->assertEquals(0.0, $funnel['abandonment_rate']);
    }
}
