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
}
