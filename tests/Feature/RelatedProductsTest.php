<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RelatedProductsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup initial default category
        Category::create([
            'name' => 'Fashion',
            'slug' => 'fashion',
            'is_active' => true,
        ]);

        // Create Super Admin role for Spatie permissions guard
        if (class_exists(\Spatie\Permission\Models\Role::class)) {
            \Spatie\Permission\Models\Role::firstOrCreate(
                ['name' => 'Super Admin', 'guard_name' => 'web'],
                ['ulid' => (string) \Illuminate\Support\Str::ulid()]
            );
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        }
    }

    /**
     * Test manual selection logic when manual associations are saved.
     */
    public function test_manual_related_products_association(): void
    {
        $category = Category::first();

        $productA = Product::create([
            'name' => 'Main Product',
            'sku' => 'MAIN-001',
            'price' => 100.00,
            'stock' => 10,
            'status' => 'active',
            'category_id' => $category->id,
        ]);

        $productB = Product::create([
            'name' => 'Related Product B',
            'sku' => 'REL-002',
            'price' => 50.00,
            'stock' => 5,
            'status' => 'active',
            'category_id' => $category->id,
        ]);

        $productC = Product::create([
            'name' => 'Related Product C',
            'sku' => 'REL-003',
            'price' => 60.00,
            'stock' => 5,
            'status' => 'active',
            'category_id' => $category->id,
        ]);

        // Assure class exists before sync
        if (class_exists(\SGCart\RelatedProducts\Models\RelatedProduct::class)) {
            $productA->relatedProducts()->sync([$productB->id]);
            
            $response = $this->get(route('store.product', $productA->slug));
            $response->assertStatus(200);
            
            // Assert manually selected product B is in the response view
            $response->assertSee($productB->name);
            // Product C was not selected, so it should not show up (only manual is shown)
            $response->assertDontSee($productC->name);
        } else {
            $this->markTestSkipped('Related Products package is not registered.');
        }
    }

    /**
     * Test fallback sequence: Name -> Search Tag -> Category.
     */
    public function test_fallback_logic_sequence(): void
    {
        $category1 = Category::first();
        $category2 = Category::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'is_active' => true,
        ]);

        // Main product: Nike Running Shoes in Category 1
        $productA = Product::create([
            'name' => 'Nike Running Shoes',
            'sku' => 'NIKE-RUN-01',
            'price' => 120.00,
            'stock' => 10,
            'status' => 'active',
            'category_id' => $category1->id,
        ]);

        // Product B: matches by Name word ("Nike"), but is in Category 2 (different category)
        $productB = Product::create([
            'name' => 'Nike Socks',
            'sku' => 'NIKE-SOCK-02',
            'price' => 10.00,
            'stock' => 15,
            'status' => 'active',
            'category_id' => $category2->id,
        ]);

        // Product C: matches by Search Tag ("running"), but name and category are different
        $productC = Product::create([
            'name' => 'Adidas Shorts',
            'sku' => 'ADI-SH-03',
            'price' => 25.00,
            'stock' => 10,
            'status' => 'active',
            'category_id' => $category2->id,
        ]);
        $productC->searchTerms()->create(['term' => 'running']);

        // Product D: matches by Category only (Category 1, no name/tag overlap)
        $productD = Product::create([
            'name' => 'Generic Belt',
            'sku' => 'GEN-BELT-04',
            'price' => 15.00,
            'stock' => 5,
            'status' => 'active',
            'category_id' => $category1->id,
        ]);

        // Visit details page of Product A
        $response = $this->get(route('store.product', $productA->slug));
        $response->assertStatus(200);

        // Verify that B, C, D are present in the response
        $response->assertSee($productB->name);
        $response->assertSee($productC->name);
        $response->assertSee($productD->name);

        // We can inspect the order of the related products passed to the view
        $relatedProducts = $response->viewData('related');
        $this->assertCount(3, $relatedProducts);

        // Order should be B (Name Match) -> C (Tag Match) -> D (Category Match)
        $this->assertEquals($productB->id, $relatedProducts[0]['id']);
        $this->assertEquals($productC->id, $relatedProducts[1]['id']);
        $this->assertEquals($productD->id, $relatedProducts[2]['id']);
    }

    /**
     * Test admin can assign related products via admin controller.
     */
    public function test_admin_can_save_related_products(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin'); // Ensure has admin permissions

        $category = Category::first();

        $productA = Product::create([
            'name' => 'Main Product',
            'sku' => 'MAIN-001',
            'price' => 100.00,
            'stock' => 10,
            'status' => 'active',
            'category_id' => $category->id,
        ]);

        $productB = Product::create([
            'name' => 'Related Product B',
            'sku' => 'REL-002',
            'price' => 50.00,
            'stock' => 5,
            'status' => 'active',
            'category_id' => $category->id,
        ]);

        if (class_exists(\SGCart\RelatedProducts\Models\RelatedProduct::class)) {
            $response = $this->actingAs($user)->put(route('admin.products.update', $productA->ulid), [
                'name' => 'Main Product Updated',
                'sku' => 'MAIN-001',
                'price' => 105.00,
                'stock' => 10,
                'status' => 'active',
                'category_id' => $category->id,
                'related_product_ids' => [$productB->id]
            ]);

            $response->assertRedirect(route('admin.products.index'));
            $this->assertDatabaseHas('related_products', [
                'product_id' => $productA->id,
                'related_id' => $productB->id
            ]);
        } else {
            $this->markTestSkipped('Related Products package is not registered.');
        }
    }

    /**
     * Test storefront falls back to default matching logic when the package is installed
     * but the admin has not assigned any related products for the specific product.
     */
    public function test_fallback_logic_when_package_is_active_but_no_related_products_are_assigned(): void
    {
        $category = Category::first();

        // Main product
        $productA = Product::create([
            'name' => 'Main Nike Shoes',
            'sku' => 'MAIN-NIKE-01',
            'price' => 100.00,
            'stock' => 10,
            'status' => 'active',
            'category_id' => $category->id,
        ]);

        // Fallback matched product: same category
        $productB = Product::create([
            'name' => 'Fallback Fashion Belt',
            'sku' => 'FALL-BELT-02',
            'price' => 20.00,
            'stock' => 5,
            'status' => 'active',
            'category_id' => $category->id,
        ]);

        // Visit details page of Product A
        $response = $this->get(route('store.product', $productA->slug));
        $response->assertStatus(200);

        // Even though package is active, because no manual relation is mapped,
        // it should fallback to category-matching and list Product B.
        $response->assertSee($productB->name);

        $relatedProducts = $response->viewData('related');
        $this->assertCount(1, $relatedProducts);
        $this->assertEquals($productB->id, $relatedProducts[0]['id']);
    }
}
