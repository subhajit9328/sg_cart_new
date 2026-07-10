<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreShopAccordionFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_shop_accordion_filter_with_empty_search(): void
    {
        // Create Parent Category
        $parent = Category::create([
            'name' => "Men's Clothing",
            'slug' => 'mens-clothing',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // Create Child Category
        $child = Category::create([
            'name' => "Shirts",
            'slug' => 'shirts',
            'parent_id' => $parent->id,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $response = $this->get(route('store.shop'));
        $response->assertStatus(200);

        // Sidebar categories should contain both parent and child
        $sidebarCategories = $response->viewData('sidebarCategories');
        $this->assertCount(1, $sidebarCategories);
        $this->assertEquals("Men's Clothing", $sidebarCategories[0]['name']);
        $this->assertCount(1, $sidebarCategories[0]['children']);
        $this->assertEquals("Shirts", $sidebarCategories[0]['children'][0]['name']);
    }

    public function test_store_shop_accordion_filter_with_non_empty_search(): void
    {
        // Create Categories
        $parent1 = Category::create(['name' => "Men's Clothing", 'slug' => 'mens-clothing', 'is_active' => true]);
        $child1 = Category::create(['name' => "Shirts", 'slug' => 'shirts', 'parent_id' => $parent1->id, 'is_active' => true]);

        $parent2 = Category::create(['name' => "Women's Clothing", 'slug' => 'womens-clothing', 'is_active' => true]);
        $child2 = Category::create(['name' => "Dresses", 'slug' => 'dresses', 'parent_id' => $parent2->id, 'is_active' => true]);

        // Create Products
        Product::create([
            'name' => 'Linen-blend shirt',
            'sku' => 'HM-SHIRT-001',
            'category_id' => $child1->id,
            'price' => 2299.00,
            'stock' => 50,
            'status' => 'active',
        ]);

        Product::create([
            'name' => 'Summer Floral Dress',
            'sku' => 'WN-DRESS-001',
            'category_id' => $child2->id,
            'price' => 1999.00,
            'stock' => 30,
            'status' => 'active',
        ]);

        // Search for 'shirt'
        $response = $this->get(route('store.shop', ['search' => 'shirt']));
        $response->assertStatus(200);

        $sidebarCategories = $response->viewData('sidebarCategories');

        // Only Men's Clothing & Shirts should show because only they have products in the search results
        $this->assertCount(1, $sidebarCategories);
        $this->assertEquals("Men's Clothing", $sidebarCategories[0]['name']);
        $this->assertCount(1, $sidebarCategories[0]['children']);
        $this->assertEquals("Shirts", $sidebarCategories[0]['children'][0]['name']);

        // The count for Shirts should be 1
        $categoryCounts = $response->viewData('categoryCounts');
        $this->assertEquals(1, $categoryCounts["Men's Clothing"]);
        $this->assertEquals(1, $categoryCounts["Shirts"]);
        $this->assertArrayNotHasKey("Women's Clothing", $categoryCounts);
        $this->assertArrayNotHasKey("Dresses", $categoryCounts);
    }

    public function test_store_shop_filter_by_subcategory(): void
    {
        // Create Categories
        $parent1 = Category::create(['name' => "Men's Clothing", 'slug' => 'mens-clothing', 'is_active' => true]);
        $child1 = Category::create(['name' => "Shirts", 'slug' => 'shirts', 'parent_id' => $parent1->id, 'is_active' => true]);
        $child2 = Category::create(['name' => "Polos", 'slug' => 'polos', 'parent_id' => $parent1->id, 'is_active' => true]);

        // Create Products
        Product::create([
            'name' => 'Linen-blend shirt',
            'sku' => 'HM-SHIRT-001',
            'category_id' => $child1->id,
            'price' => 2299.00,
            'stock' => 50,
            'status' => 'active',
        ]);

        Product::create([
            'name' => 'Casual Polo Tee',
            'sku' => 'HM-POLO-001',
            'category_id' => $child2->id,
            'price' => 1299.00,
            'stock' => 30,
            'status' => 'active',
        ]);

        // Filter by child category "Shirts"
        $response = $this->get(route('store.shop', ['sub_category' => ['Shirts']]));
        $response->assertStatus(200);

        $products = $response->viewData('products');
        $this->assertCount(1, $products);
        $this->assertEquals('Linen-blend shirt', $products[0]['name']);

        // Filter by parent category "Men's Clothing" (should return both products)
        $response = $this->get(route('store.shop', ['category' => ["Men's Clothing"]]));
        $response->assertStatus(200);

        $products = $response->viewData('products');
        $this->assertCount(2, $products);
    }

    public function test_store_shop_resets_filters_on_search_query_change(): void
    {
        // 1. Initial search for 'shirt' with filters applied
        $response = $this->withSession(['last_search' => 'shirt'])
            ->get(route('store.shop', [
                'search' => 'shirt',
                'category' => ["Men's Clothing"],
                'sub_category' => ["Shirts"],
                'price_max' => '5000'
            ]));
        $response->assertStatus(200);

        // Verify session is updated
        $this->assertEquals('shirt', session('last_search'));

        // 2. Perform a different search 'shoes' with the same query params in URL
        $response = $this->withSession(['last_search' => 'shirt'])
            ->get(route('store.shop', [
                'search' => 'shoes',
                'category' => ["Men's Clothing"],
                'sub_category' => ["Shirts"],
                'price_max' => '5000'
            ]));
        $response->assertStatus(200);

        // Verify filters are reset
        $response->assertViewHas('selectedCategories', []);
        $response->assertViewHas('selectedSubCategories', []);
        $response->assertViewHas('selectedPriceMax', 100000);
        $this->assertEquals('shoes', session('last_search'));

        // Verify paginator query parameters are reset
        $products = $response->viewData('products');
        $paginatorUrl = $products->url(2);
        $this->assertStringContainsString('search=shoes', $paginatorUrl);
        $this->assertStringContainsString('price_max=100000', $paginatorUrl);
        $this->assertStringNotContainsString('category', $paginatorUrl);
        $this->assertStringNotContainsString('sub_category', $paginatorUrl);
    }
}
