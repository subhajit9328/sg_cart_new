<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Cart;
use App\Models\Customer;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CartVariantsTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_variant_pricing(): void
    {
        $hasVariants = class_exists(\SGCart\ProductVariants\Models\ProductVariant::class);
        if (!$hasVariants) {
            $this->markTestSkipped('ProductVariants package is not installed.');
            return;
        }

        // Setup COD payment method in DB as enabled
        PaymentMethod::updateOrCreate(
            ['id' => 'cod'],
            [
                'name' => 'Cash on Delivery',
                'description' => 'Pay with cash upon delivery of your order.',
                'is_installed' => true,
                'is_enabled' => true,
                'config' => ['instructions' => 'Test COD details.'],
            ]
        );

        // Create Customer and log in
        $customer = Customer::create([
            'name' => 'Test Customer',
            'email' => 'test@example.com',
            'phone_no' => '+919876543210',
            'password' => Hash::make('password123'),
        ]);
        $customer->email_verified_at = now();
        $customer->phone_verified_at = now();
        $customer->save();

        $this->actingAs($customer, 'customer');

        // Create Category
        $category = Category::create([
            'name' => 'Fashion',
            'slug' => 'fashion',
            'is_active' => true,
        ]);

        // Create Base Product
        $product = Product::create([
            'name' => 'T-Shirt',
            'slug' => 't-shirt',
            'sku' => 'TSHIRT-001',
            'category_id' => $category->id,
            'price' => 100.00,
            'stock' => 50,
            'status' => 'active',
        ]);

        // Create Color
        $color = \SGCart\ProductVariants\Models\Color::create([
            'name' => 'Red',
            'hex_code' => '#ff0000',
        ]);

        // Create Size
        $size = \SGCart\ProductVariants\Models\Size::create([
            'name' => 'Medium',
            'code' => 'M',
        ]);

        // Create Product Variant with specific price
        $variant = \SGCart\ProductVariants\Models\ProductVariant::create([
            'product_id' => $product->id,
            'color_id' => $color->id,
            'size_id' => $size->id,
            'sku' => 'TSHIRT-RED-M',
            'price' => 150.00,
            'sale_price' => 140.00,
            'stock' => 10,
            'is_active' => true,
        ]);

        // Add the variant to cart (using hex code and code)
        $response = $this->post(route('store.cart.add'), [
            'product_id' => $product->id,
            'quantity' => 2,
            'color' => '#ff0000',
            'size' => 'M',
        ]);

        $response->assertRedirect(route('store.cart'));

        // Visit cart page and verify that pricing and totals are calculated using variant sale_price (140)
        $cartResponse = $this->get(route('store.cart'));
        $cartResponse->assertStatus(200);

        $cartData = $cartResponse->viewData('cart');
        $subtotal = $cartResponse->viewData('subtotal');
        $tax = $cartResponse->viewData('tax');
        $total = $cartResponse->viewData('total');

        // Cart items should have the variant price
        $key = $product->id . '_M_#ff0000';
        $this->assertArrayHasKey($key, $cartData);
        $this->assertEquals(140.00, $cartData[$key]['price']);
        $this->assertEquals(2, $cartData[$key]['quantity']);
        $this->assertEquals(280.00, $subtotal);
        $this->assertEquals(280.00 + $tax, $total);

        // Place the order
        $orderResponse = $this->post(route('store.checkout.order'), [
            'first_name' => 'Test',
            'last_name' => 'Customer',
            'email' => 'test@example.com',
            'address' => '123 Test St',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'zip' => '400001',
            'country' => 'India',
            'payment_method' => 'cod',
        ]);

        // Assert order created successfully
        $this->assertEquals(1, OrderItem::count());
        $orderItem = OrderItem::first();
        $this->assertEquals(140.00, $orderItem->price);
        $this->assertEquals('TSHIRT-RED-M', $orderItem->product_sku);
        $this->assertEquals(2, $orderItem->quantity);
    }

    public function test_cart_variant_out_of_stock(): void
    {
        $hasVariants = class_exists(\SGCart\ProductVariants\Models\ProductVariant::class);
        if (!$hasVariants) {
            $this->markTestSkipped('ProductVariants package is not installed.');
            return;
        }

        // Create Category
        $category = Category::create([
            'name' => 'Fashion',
            'slug' => 'fashion',
            'is_active' => true,
        ]);

        // Create Base Product
        $product = Product::create([
            'name' => 'T-Shirt',
            'slug' => 't-shirt',
            'sku' => 'TSHIRT-001',
            'category_id' => $category->id,
            'price' => 100.00,
            'stock' => 50,
            'status' => 'active',
        ]);

        // Create Color
        $color = \SGCart\ProductVariants\Models\Color::create([
            'name' => 'Blue',
            'hex_code' => '#0000ff',
        ]);

        // Create Size
        $size = \SGCart\ProductVariants\Models\Size::create([
            'name' => 'Large',
            'code' => 'L',
        ]);

        // Create Product Variant with 0 stock
        $variant = \SGCart\ProductVariants\Models\ProductVariant::create([
            'product_id' => $product->id,
            'color_id' => $color->id,
            'size_id' => $size->id,
            'sku' => 'TSHIRT-BLUE-L',
            'price' => 150.00,
            'stock' => 0,
            'is_active' => true,
        ]);

        // Try to add the variant to cart
        $response = $this->post(route('store.cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
            'color' => '#0000ff',
            'size' => 'L',
        ]);

        // Should restrict and redirect back with error
        $response->assertSessionHas('error', 'Sorry, the selected variant is currently out of stock.');

        // Test product detail page with out of stock variant selected in query parameters
        $responseProduct = $this->get(route('store.product', [
            'slug' => $product->slug,
            'color' => '#0000ff',
            'size' => 'L',
        ]));
        
        // Should flash error
        $responseProduct->assertSessionHas('error', 'Sorry, the selected variant is currently out of stock.');
    }
}
