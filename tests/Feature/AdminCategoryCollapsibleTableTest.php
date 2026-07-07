<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCategoryCollapsibleTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_parent_categories_collapsible_table(): void
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

        // Create Parent Categories
        $parent1 = Category::create([
            'name' => 'Men\'s Clothing',
            'slug' => 'mens-clothing',
            'description' => 'Apparel for men',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $parent2 = Category::create([
            'name' => 'Women\'s Clothing',
            'slug' => 'womens-clothing',
            'description' => 'Apparel for women',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        // Create Child Categories under Parent 1
        $child1 = Category::create([
            'name' => 'T-Shirts',
            'slug' => 'mens-clothing-tshirts',
            'parent_id' => $parent1->id,
            'description' => 'Casual t-shirts for men',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $child2 = Category::create([
            'name' => 'Shirts',
            'slug' => 'mens-clothing-shirts',
            'parent_id' => $parent1->id,
            'description' => 'Formal shirts for men',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        // Access categories index
        $response = $this->actingAs($admin)
            ->get(route('admin.categories.index'));

        $response->assertStatus(200);

        // Verify parents are loaded in pagination data
        $categories = $response->viewData('categories');
        $this->assertCount(2, $categories); // Only parent categories at top level
        $this->assertTrue($categories->contains($parent1));
        $this->assertTrue($categories->contains($parent2));
        $this->assertFalse($categories->contains($child1));

        // Verify parent 1 has child categories loaded
        $loadedParent1 = $categories->where('id', $parent1->id)->first();
        $this->assertCount(2, $loadedParent1->children);
        $this->assertEquals('T-Shirts', $loadedParent1->children[0]->name);
        $this->assertEquals('Shirts', $loadedParent1->children[1]->name);
    }

    public function test_admin_category_table_search_matches_parents_and_children(): void
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

        $parent1 = Category::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $child1 = Category::create([
            'name' => 'Smartphones',
            'slug' => 'electronics-smartphones',
            'parent_id' => $parent1->id,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $child2 = Category::create([
            'name' => 'Laptops',
            'slug' => 'electronics-laptops',
            'parent_id' => $parent1->id,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $parent2 = Category::create([
            'name' => 'Home & Kitchen',
            'slug' => 'home-kitchen',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        // Search for a child category ("Smartphones")
        $response = $this->actingAs($admin)
            ->get(route('admin.categories.index', ['search' => 'Smartphones']));

        $response->assertStatus(200);
        $categories = $response->viewData('categories');
        
        // It should match and load Electronics (the parent) because it has child "Smartphones" matching the search query
        $this->assertTrue($categories->contains($parent1));
        $this->assertFalse($categories->contains($parent2));

        // Eager-loaded children must only contain the matched child and exclude others
        $loadedParent = $categories->where('id', $parent1->id)->first();
        $this->assertCount(1, $loadedParent->children);
        $this->assertEquals('Smartphones', $loadedParent->children->first()->name);
    }

    public function test_admin_category_table_search_parent_matches_and_has_no_matching_children(): void
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

        $parent1 = Category::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $child1 = Category::create([
            'name' => 'Smartphones',
            'slug' => 'electronics-smartphones',
            'parent_id' => $parent1->id,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $child2 = Category::create([
            'name' => 'Laptops',
            'slug' => 'electronics-laptops',
            'parent_id' => $parent1->id,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        // Search for parent name ("Electronics") which has no matching child category
        $response = $this->actingAs($admin)
            ->get(route('admin.categories.index', ['search' => 'Electronics']));

        $response->assertStatus(200);
        $categories = $response->viewData('categories');
        
        $this->assertTrue($categories->contains($parent1));

        // Since parent matched but no child matched, all child categories should be loaded
        $loadedParent = $categories->where('id', $parent1->id)->first();
        $this->assertCount(2, $loadedParent->children);
        $this->assertEquals('Smartphones', $loadedParent->children[0]->name);
        $this->assertEquals('Laptops', $loadedParent->children[1]->name);
    }

    public function test_admin_can_reorder_categories(): void
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

        $cat1 = Category::create(['name' => 'Cat 1', 'slug' => 'cat-1', 'sort_order' => 1]);
        $cat2 = Category::create(['name' => 'Cat 2', 'slug' => 'cat-2', 'sort_order' => 2]);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.categories.reorder'), [
                'order' => [$cat2->id, $cat1->id]
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verify database order updated
        $this->assertEquals(0, $cat2->fresh()->sort_order);
        $this->assertEquals(1, $cat1->fresh()->sort_order);
    }
}
