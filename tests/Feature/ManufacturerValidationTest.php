<?php

namespace Tests\Feature;

use App\Models\Manufacturer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ManufacturerValidationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::firstOrCreate(['name' => 'manage products', 'guard_name' => 'web']);
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);
        $this->admin->givePermissionTo('manage products');
    }

    public function test_phone_validation_failures(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.manufacturers.store'), [
            'name' => 'Manufacturer Test',
            'phone' => 'invalid-phone-123', // Invalid character '-'
        ]);
        $response->assertSessionHasErrors(['phone']);

        $responseSpaces = $this->actingAs($this->admin)->post(route('admin.manufacturers.store'), [
            'name' => 'Manufacturer Test 2',
            'phone' => '+123 4567', // Invalid character space
        ]);
        $responseSpaces->assertSessionHasErrors(['phone']);

        // Under 7 digits (e.g., 6 digits)
        $responseUnderLimit = $this->actingAs($this->admin)->post(route('admin.manufacturers.store'), [
            'name' => 'Manufacturer Test 3',
            'phone' => '+123456',
        ]);
        $responseUnderLimit->assertSessionHasErrors(['phone']);

        // Over 15 digits (e.g., 16 digits)
        $responseOverLimit = $this->actingAs($this->admin)->post(route('admin.manufacturers.store'), [
            'name' => 'Manufacturer Test 4',
            'phone' => '+1234567890123456',
        ]);
        $responseOverLimit->assertSessionHasErrors(['phone']);
    }

    public function test_phone_validation_success(): void
    {
        // 7 digits (minimum)
        $responseMin = $this->actingAs($this->admin)->post(route('admin.manufacturers.store'), [
            'name' => 'Manufacturer Test Min',
            'phone' => '+1234567',
        ]);
        $responseMin->assertSessionHasNoErrors();

        // 15 digits (maximum)
        $responseMax = $this->actingAs($this->admin)->post(route('admin.manufacturers.store'), [
            'name' => 'Manufacturer Test Max',
            'phone' => '+123456789012345',
        ]);
        $responseMax->assertSessionHasNoErrors();
    }

    public function test_phone_update_validation(): void
    {
        $manufacturer = Manufacturer::create([
            'name' => 'Existing Manufacturer',
            'slug' => 'existing-manufacturer',
            'phone' => '+1234567',
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.manufacturers.update', $manufacturer), [
            'name' => 'Existing Manufacturer',
            'phone' => 'invalid-phone',
        ]);
        $response->assertSessionHasErrors(['phone']);

        $responseSuccess = $this->actingAs($this->admin)->put(route('admin.manufacturers.update', $manufacturer), [
            'name' => 'Existing Manufacturer',
            'phone' => '9876543210',
        ]);
        $responseSuccess->assertSessionHasNoErrors();
        $this->assertDatabaseHas('manufacturers', [
            'id' => $manufacturer->id,
            'phone' => '9876543210',
        ]);
    }
}
