<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProductFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_filter_products_by_category_including_subcategories(): void
    {
        // Set up Spatie permission
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'manage products', 'guard_name' => 'web']);
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);
        $admin->givePermissionTo('manage products');

        // Create Parent Category
        $parentCategory = Category::create([
            'name' => "Men's Clothing",
            'slug' => 'mens-clothing',
            'is_active' => true,
        ]);

        // Create Child Category
        $childCategory = Category::create([
            'name' => "Shirts",
            'slug' => 'shirts',
            'parent_id' => $parentCategory->id,
            'is_active' => true,
        ]);

        // Create Products
        $productInChild = Product::create([
            'name' => 'H&M MEN Relaxed Fit Linen-blend shirt',
            'sku' => 'HM-SHIRT-001',
            'category_id' => $childCategory->id,
            'price' => 2299.00,
            'stock' => 50,
            'status' => 'active',
        ]);

        $productOther = Product::create([
            'name' => 'Women Casual Summer Dress',
            'sku' => 'WN-DRESS-001',
            'price' => 1999.00,
            'stock' => 30,
            'status' => 'active',
        ]);

        // 1. Query with search only
        $response = $this->actingAs($admin)
            ->get(route('admin.products.index', ['search' => 'Linen-blend']));
        $response->assertStatus(200);
        $products = $response->viewData('products');
        $this->assertTrue($products->contains($productInChild));
        $this->assertFalse($products->contains($productOther));

        // 2. Query with category_id only (parent category)
        $response = $this->actingAs($admin)
            ->get(route('admin.products.index', ['category_id' => $parentCategory->id]));
        $response->assertStatus(200);
        $products = $response->viewData('products');
        $this->assertTrue($products->contains($productInChild));
        $this->assertFalse($products->contains($productOther));

        // 3. Query with category_id (parent category) and search query combined
        $response = $this->actingAs($admin)
            ->get(route('admin.products.index', [
                'category_id' => $parentCategory->id,
                'search' => 'Linen-blend'
            ]));
        $response->assertStatus(200);
        $products = $response->viewData('products');
        $this->assertTrue($products->contains($productInChild));
        $this->assertFalse($products->contains($productOther));

        // 4. Query with category_id (child category) and search query combined
        $response = $this->actingAs($admin)
            ->get(route('admin.products.index', [
                'category_id' => $childCategory->id,
                'search' => 'Linen-blend'
            ]));
        $response->assertStatus(200);
        $products = $response->viewData('products');
        $this->assertTrue($products->contains($productInChild));
        $this->assertFalse($products->contains($productOther));

        // 5. Query with category_id (parent category) and mismatching search query
        $response = $this->actingAs($admin)
            ->get(route('admin.products.index', [
                'category_id' => $parentCategory->id,
                'search' => 'Dress'
            ]));
        $response->assertStatus(200);
        $products = $response->viewData('products');
        $this->assertFalse($products->contains($productInChild));
        $this->assertFalse($products->contains($productOther));
    }
}
