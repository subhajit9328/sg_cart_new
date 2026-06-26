<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\PaymentMethod;
use App\Payments\Services\PaymentManager;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Spatie\Permission\Models\Permission;

class PaymentPackageTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        PaymentMethod::query()->delete();

        // Create and authenticate Admin User with required permissions
        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
        ]);

        $permission = Permission::firstOrCreate(['name' => 'manage payments', 'guard_name' => 'web']);
        $this->admin->givePermissionTo($permission);

        // Create Storefront Customer
        $this->customer = Customer::create([
            'name' => 'Customer User',
            'email' => 'customer_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    /**
     * Test admin can view registered payment methods in the settings panel.
     */
    public function test_admin_can_view_payment_gateways(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.payments.settings'));

        $response->assertStatus(200);
        $response->assertSee('Cash on Delivery');
        $response->assertSee('Razorpay');
        $response->assertSee('Authorize.Net');
    }

    /**
     * Test admin can configure and toggle payment gateways.
     */
    public function test_admin_can_configure_payment_gateways(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.payments.settings.update'), [
            'settings' => [
                'cod' => [
                    'name' => 'COD Custom Name',
                    'description' => 'COD Custom Desc',
                    'is_enabled' => '1',
                    'config' => ['instructions' => 'Custom instruction']
                ],
                'razorpay' => [
                    'name' => 'Razorpay Pay',
                    'description' => 'Razorpay Desc',
                    'is_enabled' => '1',
                    'config' => ['key_id' => 'rzp_key', 'key_secret' => 'rzp_secret']
                ]
            ]
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Check COD is configured and enabled in DB
        $codMethod = PaymentMethod::find('cod');
        $this->assertNotNull($codMethod);
        $this->assertEquals('COD Custom Name', $codMethod->name);
        $this->assertEquals('COD Custom Desc', $codMethod->description);
        $this->assertTrue((bool)$codMethod->is_enabled);
        $this->assertEquals('Custom instruction', $codMethod->config['instructions'] ?? null);

        // Check Razorpay is configured and enabled in DB
        $rzpMethod = PaymentMethod::find('razorpay');
        $this->assertNotNull($rzpMethod);
        $this->assertEquals('Razorpay Pay', $rzpMethod->name);
        $this->assertTrue((bool)$rzpMethod->is_enabled);
        $this->assertEquals('rzp_key', $rzpMethod->config['key_id'] ?? null);
    }

    /**
     * Test customer checkout with Cash on Delivery gateway.
     */
    public function test_customer_can_checkout_with_cod(): void
    {
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

        // Create a product
        $product = Product::create([
            'name' => 'Test Product',
            'slug' => 'test-product-' . uniqid(),
            'sku' => 'TEST-SKU-' . uniqid(),
            'price' => 100.00,
            'stock' => 10,
            'status' => 'active',
        ]);

        // Setup customer cart
        $cart = Cart::create(['customer_id' => $this->customer->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        // Add saved address for customer
        $address = $this->customer->addresses()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address' => '123 Test Street',
            'city' => 'Test City',
            'state' => 'TS',
            'zip' => '12345',
            'country' => 'USA',
            'is_default' => true,
        ]);

        // Perform checkout post request
        $response = $this->actingAs($this->customer, 'customer')->post(route('store.checkout.order'), [
            'address_id' => $address->id,
            'payment_method' => 'cod',
        ]);

        // Assert redirect to success
        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'customer_id' => $this->customer->id,
        ]);
        $this->assertDatabaseHas('payments', [
            'payment_method' => 'Cash on Delivery',
            'status' => 'Pending',
        ]);
    }

    /**
     * Test customer checkout with Razorpay gateway.
     */
    public function test_customer_can_checkout_with_razorpay(): void
    {
        // Setup Razorpay payment method in DB as enabled
        PaymentMethod::updateOrCreate(
            ['id' => 'razorpay'],
            [
                'name' => 'Razorpay',
                'description' => 'Pay securely using Razorpay.',
                'is_installed' => true,
                'is_enabled' => true,
                'config' => ['key_id' => 'test_key', 'key_secret' => 'test_secret'],
            ]
        );

        // Create a product
        $product = Product::create([
            'name' => 'Test Product',
            'slug' => 'test-product-' . uniqid(),
            'sku' => 'TEST-SKU-' . uniqid(),
            'price' => 100.00,
            'stock' => 10,
            'status' => 'active',
        ]);

        // Setup customer cart
        $cart = Cart::create(['customer_id' => $this->customer->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        // Add saved address for customer
        $address = $this->customer->addresses()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address' => '123 Test Street',
            'city' => 'Test City',
            'state' => 'TS',
            'zip' => '12345',
            'country' => 'USA',
            'is_default' => true,
        ]);

        // Perform checkout post request
        $response = $this->actingAs($this->customer, 'customer')->post(route('store.checkout.order'), [
            'address_id' => $address->id,
            'payment_method' => 'razorpay',
        ]);

        // Assert redirect to success
        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'customer_id' => $this->customer->id,
        ]);
        $this->assertDatabaseHas('payments', [
            'payment_method' => 'Razorpay',
            'status' => 'Paid',
        ]);
    }

    /**
     * Test customer checkout with Authorize.Net gateway.
     */
    public function test_customer_can_checkout_with_authorizenet(): void
    {
        // Setup Authorize.Net payment method in DB as enabled
        PaymentMethod::updateOrCreate(
            ['id' => 'authorizenet'],
            [
                'name' => 'Authorize.Net',
                'description' => 'Secure credit card payments via Authorize.Net.',
                'is_installed' => true,
                'is_enabled' => true,
                'config' => ['merchant_login_id' => 'test_login', 'merchant_transaction_key' => 'test_key', 'sandbox' => '1'],
            ]
        );

        // Create a product
        $product = Product::create([
            'name' => 'Test Product',
            'slug' => 'test-product-' . uniqid(),
            'sku' => 'TEST-SKU-' . uniqid(),
            'price' => 200.00,
            'stock' => 5,
            'status' => 'active',
        ]);

        // Setup customer cart
        $cart = Cart::create(['customer_id' => $this->customer->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        // Add saved address for customer
        $address = $this->customer->addresses()->create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'address' => '456 Lane Road',
            'city' => 'Sample Town',
            'state' => 'ST',
            'zip' => '54321',
            'country' => 'Canada',
            'is_default' => true,
        ]);

        // Perform checkout post request with Card credentials
        $response = $this->actingAs($this->customer, 'customer')->post(route('store.checkout.order'), [
            'address_id' => $address->id,
            'payment_method' => 'authorizenet',
            'authorizenet_card_name' => 'Jane Smith',
            'authorizenet_card_num' => '4111 2222 3333 4444',
            'authorizenet_card_expiry' => '12/28',
            'authorizenet_card_cvv' => '123',
        ]);

        // Assert redirect to success
        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'customer_id' => $this->customer->id,
        ]);
        $this->assertDatabaseHas('payments', [
            'payment_method' => 'Authorize.Net',
            'status' => 'Paid',
            'card_name' => 'Jane Smith',
            'card_number_masked' => '**** **** **** 4444',
        ]);
    }
}
