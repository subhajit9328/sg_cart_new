<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ProductValidationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Category $category;

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

        $this->category = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'is_active' => true,
        ]);
    }

    public function test_weight_and_dimensions_validation_failures(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.products.store'), [
            'name' => 'New Product',
            'sku' => 'SKU-NEW-123',
            'price' => 100.00,
            'stock' => 10,
            'status' => 'active',
            'category_id' => $this->category->id,
            'weight' => '500', // Invalid: no unit
            'dimensions' => '10x5', // Invalid: not LxWxH
        ]);

        $response->assertSessionHasErrors(['weight', 'dimensions']);
    }

    public function test_weight_and_dimensions_validation_success(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.products.store'), [
            'name' => 'New Product',
            'sku' => 'SKU-NEW-123',
            'price' => 100.00,
            'stock' => 10,
            'status' => 'active',
            'category_id' => $this->category->id,
            'weight' => '1.5 kg',
            'dimensions' => '10 x 5 x 3.5 cm',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('products', [
            'sku' => 'SKU-NEW-123',
            'weight' => '1.5 kg',
            'dimensions' => '10 x 5 x 3.5 cm',
        ]);
    }

    public function test_weight_and_dimensions_update_validation(): void
    {
        $product = Product::create([
            'name' => 'Existing Product',
            'sku' => 'SKU-EX-123',
            'price' => 100.00,
            'stock' => 10,
            'status' => 'active',
            'category_id' => $this->category->id,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.products.update', $product->ulid), [
            'name' => 'Existing Product',
            'sku' => 'SKU-EX-123',
            'price' => 100.00,
            'stock' => 10,
            'status' => 'active',
            'category_id' => $this->category->id,
            'weight' => 'invalid_weight',
            'dimensions' => 'invalid_dimensions',
        ]);

        $response->assertSessionHasErrors(['weight', 'dimensions']);

        $responseSuccess = $this->actingAs($this->admin)->put(route('admin.products.update', $product->ulid), [
            'name' => 'Existing Product',
            'sku' => 'SKU-EX-123',
            'price' => 100.00,
            'stock' => 10,
            'status' => 'active',
            'category_id' => $this->category->id,
            'weight' => '500g',
            'dimensions' => '10x5x3',
        ]);

        $responseSuccess->assertSessionHasNoErrors();
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'weight' => '500g',
            'dimensions' => '10x5x3',
        ]);
    }
}
