<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UncategorizedProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_uncategorized_product_is_displayed_on_shop_page(): void
    {
        $product = Product::create([
            'name' => 'Uncategorized T-Shirt',
            'sku' => 'UNCAT-TSHIRT-01',
            'category_id' => null,
            'price' => 999.00,
            'stock' => 10,
            'status' => 'active',
        ]);

        $response = $this->get(route('store.shop'));
        $response->assertStatus(200);
        $response->assertSee('Uncategorized T-Shirt');
    }

    public function test_uncategorized_product_details_page_resolves_successfully(): void
    {
        $product = Product::create([
            'name' => 'Uncategorized T-Shirt',
            'sku' => 'UNCAT-TSHIRT-01',
            'category_id' => null,
            'price' => 999.00,
            'stock' => 10,
            'status' => 'active',
        ]);

        $response = $this->get(route('store.product', $product->slug));
        $response->assertStatus(200);
        $response->assertSee('Uncategorized T-Shirt');
        $response->assertSee('Uncategorized');
        $response->assertSee('Capsule');
    }
}
