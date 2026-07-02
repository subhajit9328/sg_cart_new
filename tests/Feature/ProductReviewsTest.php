<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use SGCart\Reviews\Models\Review;
use SGCart\Reviews\Models\ReviewStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductReviewsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed review statuses if they don't exist using the seeder
        if (ReviewStatus::count() === 0) {
            $seeder = new \SGCart\Reviews\Database\Seeders\ReviewStatusSeeder();
            $seeder->run();
        }

        // Setup Spatie permissions for tests
        if (class_exists(\Spatie\Permission\Models\Permission::class)) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'manage reviews', 'guard_name' => 'web']);
        }
    }

    /**
     * Test customer can submit a review for a delivered order item with multiple images.
     */
    public function test_customer_can_submit_review_for_delivered_order_item(): void
    {
        Storage::fake('public');

        $customer = Customer::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
        ]);
        $customer->email_verified_at = now();
        $customer->phone_verified_at = now();
        $customer->save();

        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'SKU-REV-1',
            'price' => 100.00,
            'stock' => 10,
        ]);

        $order = Order::create([
            'ulid' => (string) Str::ulid(),
            'order_number' => 'ORD-REV-1',
            'customer_id' => $customer->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'Test State',
            'zip' => '12345',
            'country' => 'India',
            'subtotal' => 100.00,
            'tax' => 0,
            'shipping_charge' => 0,
            'total' => 100.00,
            'status' => 'Delivered',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => $product->price,
            'quantity' => 1,
        ]);

        $this->actingAs($customer, 'customer');

        $images = [
            UploadedFile::fake()->image('review1.jpg'),
            UploadedFile::fake()->image('review2.jpg'),
        ];

        $response = $this->post(route('store.reviews.store'), [
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'This is a fantastic test product!',
            'images' => $images,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        
        $this->assertDatabaseHas('reviews', [
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'This is a fantastic test product!',
            'status_id' => 1, // Pending
            'approved_at' => null,
        ]);

        $review = Review::with('images')->first();
        $this->assertCount(2, $review->images);

        foreach ($review->images as $img) {
            $this->assertNotNull($img->image_path);
            Storage::disk('public')->assertExists($img->image_path);
        }
    }

    /**
     * Test appending new images to existing ones, capping at 5 and deleting removed ones.
     */
    public function test_customer_can_append_images_and_cap_at_five(): void
    {
        Storage::fake('public');

        $customer = Customer::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
        ]);
        $customer->email_verified_at = now();
        $customer->phone_verified_at = now();
        $customer->save();

        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'SKU-REV-1B',
            'price' => 100.00,
            'stock' => 10,
        ]);

        $order = Order::create([
            'ulid' => (string) Str::ulid(),
            'order_number' => 'ORD-REV-1B',
            'customer_id' => $customer->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'Test State',
            'zip' => '12345',
            'country' => 'India',
            'subtotal' => 100.00,
            'tax' => 0,
            'shipping_charge' => 0,
            'total' => 100.00,
            'status' => 'Delivered',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => $product->price,
            'quantity' => 1,
        ]);

        // Create an existing review with 3 mock images stored in fake public storage
        $review = Review::create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'rating' => 4,
            'comment' => 'Initial comment',
        ]);

        $img1 = $review->images()->create(['image_path' => 'reviews/old1.jpg']);
        $img2 = $review->images()->create(['image_path' => 'reviews/old2.jpg']);
        $img3 = $review->images()->create(['image_path' => 'reviews/old3.jpg']);

        Storage::disk('public')->put('reviews/old1.jpg', 'fake image content');
        Storage::disk('public')->put('reviews/old2.jpg', 'fake image content');
        Storage::disk('public')->put('reviews/old3.jpg', 'fake image content');

        $this->actingAs($customer, 'customer');

        // Submit edit request:
        // - Retain the first 2 images: reviews/old1.jpg, reviews/old2.jpg (discard reviews/old3.jpg)
        // - Upload 4 new images: total would be 2 + 4 = 6. Capped at 5, so only the first 3 new ones should be accepted.
        $newFiles = [
            UploadedFile::fake()->image('new1.jpg'),
            UploadedFile::fake()->image('new2.jpg'),
            UploadedFile::fake()->image('new3.jpg'),
            UploadedFile::fake()->image('new4.jpg'),
        ];

        $response = $this->post(route('store.reviews.store'), [
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Appended comment',
            'keep_images' => ['reviews/old1.jpg', 'reviews/old2.jpg'],
            'images' => $newFiles,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        // 1. Verify database text attributes updated
        $review->refresh();
        $this->assertEquals(5, $review->rating);
        $this->assertEquals('Appended comment', $review->comment);

        // 2. Verify total images count is capped at exactly 5
        $this->assertCount(5, $review->images);

        // 3. Verify old removed image is deleted from storage and DB
        Storage::disk('public')->assertMissing('reviews/old3.jpg');
        $this->assertDatabaseMissing('review_images', ['image_path' => 'reviews/old3.jpg']);

        // 4. Verify kept images are still present
        Storage::disk('public')->assertExists('reviews/old1.jpg');
        Storage::disk('public')->assertExists('reviews/old2.jpg');

        // 5. Verify only 3 of the new images are stored (discarding the 4th)
        $allPaths = $review->images()->pluck('image_path')->toArray();
        $newImagePaths = array_filter($allPaths, function($path) {
            return !str_contains($path, 'old');
        });
        
        $this->assertCount(3, $newImagePaths);
        foreach ($newImagePaths as $path) {
            Storage::disk('public')->assertExists($path);
        }
    }

    /**
     * Test customer cannot submit a review for an undelivered order.
     */
    public function test_customer_cannot_submit_review_for_undelivered_order(): void
    {
        $customer = Customer::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
        ]);
        $customer->email_verified_at = now();
        $customer->phone_verified_at = now();
        $customer->save();

        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'SKU-REV-2',
            'price' => 100.00,
            'stock' => 10,
        ]);

        $order = Order::create([
            'ulid' => (string) Str::ulid(),
            'order_number' => 'ORD-REV-2',
            'customer_id' => $customer->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'Test State',
            'zip' => '12345',
            'country' => 'India',
            'subtotal' => 100.00,
            'tax' => 0,
            'shipping_charge' => 0,
            'total' => 100.00,
            'status' => 'Processing', // Undelivered
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => $product->price,
            'quantity' => 1,
        ]);

        $this->actingAs($customer, 'customer');

        $response = $this->post(route('store.reviews.store'), [
            'product_id' => $product->id,
            'rating' => 4,
            'comment' => 'Nice product!',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('reviews', [
            'product_id' => $product->id,
        ]);
    }

    /**
     * Test customer cannot submit review for a product not in the order.
     */
    public function test_customer_cannot_submit_review_for_unpurchased_product(): void
    {
        $customer = Customer::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
        ]);
        $customer->email_verified_at = now();
        $customer->phone_verified_at = now();
        $customer->save();

        $productInOrder = Product::create([
            'name' => 'Purchased Product',
            'sku' => 'SKU-REV-3A',
            'price' => 100.00,
            'stock' => 10,
        ]);

        $productNotInOrder = Product::create([
            'name' => 'Other Product',
            'sku' => 'SKU-REV-3B',
            'price' => 50.00,
            'stock' => 10,
        ]);

        $order = Order::create([
            'ulid' => (string) Str::ulid(),
            'order_number' => 'ORD-REV-3',
            'customer_id' => $customer->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'Test State',
            'zip' => '12345',
            'country' => 'India',
            'subtotal' => 100.00,
            'tax' => 0,
            'shipping_charge' => 0,
            'total' => 100.00,
            'status' => 'Delivered',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $productInOrder->id,
            'product_name' => $productInOrder->name,
            'price' => $productInOrder->price,
            'quantity' => 1,
        ]);

        $this->actingAs($customer, 'customer');

        $response = $this->post(route('store.reviews.store'), [
            'product_id' => $productNotInOrder->id,
            'rating' => 4,
            'comment' => 'Fake review',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('reviews', [
            'product_id' => $productNotInOrder->id,
        ]);
    }

    /**
     * Test editing an existing review resets status to Pending and clears approved_at.
     */
    public function test_customer_editing_review_resets_status_and_timestamp(): void
    {
        $customer = Customer::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
        ]);
        $customer->email_verified_at = now();
        $customer->phone_verified_at = now();
        $customer->save();

        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'SKU-REV-4',
            'price' => 100.00,
            'stock' => 10,
        ]);

        $order = Order::create([
            'ulid' => (string) Str::ulid(),
            'order_number' => 'ORD-REV-4',
            'customer_id' => $customer->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'Test State',
            'zip' => '12345',
            'country' => 'India',
            'subtotal' => 100.00,
            'tax' => 0,
            'shipping_charge' => 0,
            'total' => 100.00,
            'status' => 'Delivered',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => $product->price,
            'quantity' => 1,
        ]);

        // Create an already approved review
        $review = Review::create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Old comment',
            'status_id' => 2, // Approved
            'approved_at' => now()->subDay(),
        ]);

        $this->actingAs($customer, 'customer');

        $response = $this->post(route('store.reviews.store'), [
            'product_id' => $product->id,
            'rating' => 3,
            'comment' => 'New comment',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        
        $review->refresh();
        $this->assertEquals(3, $review->rating);
        $this->assertEquals('New comment', $review->comment);
        $this->assertEquals(1, $review->status_id); // Reset to Pending
        $this->assertNull($review->approved_at); // Reset approval timestamp
    }

    /**
     * Test admin can approve reviews, setting status and approved_at.
     */
    public function test_admin_can_approve_reviews(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo('manage reviews');

        $customer = Customer::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
        ]);
        $customer->email_verified_at = now();
        $customer->phone_verified_at = now();
        $customer->save();

        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'SKU-REV-5',
            'price' => 100.00,
            'stock' => 10,
        ]);

        $review = Review::create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Test comment',
            'status_id' => 1, // Pending
            'approved_at' => null,
        ]);

        $this->actingAs($admin);

        $response = $this->post(route('admin.reviews.approve', $review->id));

        $response->assertRedirect();
        
        $review->refresh();
        $this->assertEquals(2, $review->status_id); // Approved
        $this->assertNotNull($review->approved_at);
    }

    /**
     * Test admin can reject reviews, setting status and clearing approved_at.
     */
    public function test_admin_can_reject_reviews(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo('manage reviews');

        $customer = Customer::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
        ]);
        $customer->email_verified_at = now();
        $customer->phone_verified_at = now();
        $customer->save();

        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'SKU-REV-6',
            'price' => 100.00,
            'stock' => 10,
        ]);

        $review = Review::create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Test comment',
            'status_id' => 1, // Pending
            'approved_at' => null,
        ]);

        $this->actingAs($admin);

        $response = $this->post(route('admin.reviews.reject', $review->id));

        $response->assertRedirect();
        
        $review->refresh();
        $this->assertEquals(3, $review->status_id); // Rejected
        $this->assertNull($review->approved_at);
    }

    /**
     * Test admin can delete reviews and their attachments.
     */
    public function test_admin_can_delete_reviews(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create();
        $admin->givePermissionTo('manage reviews');

        $customer = Customer::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
        ]);
        $customer->email_verified_at = now();
        $customer->phone_verified_at = now();
        $customer->save();

        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'SKU-REV-7',
            'price' => 100.00,
            'stock' => 10,
        ]);

        $review = Review::create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Test comment',
            'status_id' => 1, // Pending
            'approved_at' => null,
        ]);

        $img = $review->images()->create(['image_path' => 'reviews/todelete.jpg']);
        Storage::disk('public')->put('reviews/todelete.jpg', 'fake image content');

        $this->actingAs($admin);

        $response = $this->delete(route('admin.reviews.destroy', $review->id));

        $response->assertRedirect();

        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
        $this->assertDatabaseMissing('review_images', ['id' => $img->id]);
        Storage::disk('public')->assertMissing('reviews/todelete.jpg');
    }
}
