<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfilePictureUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    private function createCustomer(array $attributes = []): Customer
    {
        $customer = Customer::create(array_merge([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ], $attributes));

        $customer->email_verified_at = now();
        $customer->save();

        return $customer;
    }

    public function test_profile_picture_can_be_uploaded_by_authenticated_customer()
    {
        $customer = $this->createCustomer();

        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->actingAs($customer, 'customer')
            ->postJson(route('store.account.profile-picture.update'), [
                'profile_picture' => $file,
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Profile picture updated successfully.',
        ]);

        $customer->refresh();
        $this->assertNotNull($customer->profile_picture);
        $this->assertTrue(Storage::disk('public')->exists($customer->profile_picture));
    }

    public function test_profile_picture_upload_requires_authentication()
    {
        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->postJson(route('store.account.profile-picture.update'), [
            'profile_picture' => $file,
        ]);

        $response->assertRedirect(route('store.login'));
    }

    public function test_profile_picture_must_be_an_image()
    {
        $customer = $this->createCustomer();

        $file = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

        $response = $this->actingAs($customer, 'customer')
            ->postJson(route('store.account.profile-picture.update'), [
                'profile_picture' => $file,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('profile_picture');
    }

    public function test_profile_picture_cannot_exceed_two_megabytes()
    {
        $customer = $this->createCustomer();

        // 3 MB file
        $file = UploadedFile::fake()->create('big_avatar.jpg', 3000, 'image/jpeg');

        $response = $this->actingAs($customer, 'customer')
            ->postJson(route('store.account.profile-picture.update'), [
                'profile_picture' => $file,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('profile_picture');
    }

    public function test_profile_picture_removes_old_image_on_update()
    {
        $customer = $this->createCustomer();

        // Upload first image
        $file1 = UploadedFile::fake()->image('avatar1.jpg');
        $this->actingAs($customer, 'customer')
            ->postJson(route('store.account.profile-picture.update'), [
                'profile_picture' => $file1,
            ]);

        $customer->refresh();
        $oldPath = $customer->profile_picture;
        $this->assertTrue(Storage::disk('public')->exists($oldPath));

        // Upload second image
        $file2 = UploadedFile::fake()->image('avatar2.jpg');
        $this->actingAs($customer, 'customer')
            ->postJson(route('store.account.profile-picture.update'), [
                'profile_picture' => $file2,
            ]);

        $customer->refresh();
        $newPath = $customer->profile_picture;

        $this->assertNotEquals($oldPath, $newPath);
        $this->assertFalse(Storage::disk('public')->exists($oldPath));
        $this->assertTrue(Storage::disk('public')->exists($newPath));
    }

    public function test_profile_picture_upload_creates_activity_log()
    {
        $customer = $this->createCustomer();

        $file = UploadedFile::fake()->image('avatar.jpg');

        $this->actingAs($customer, 'customer')
            ->postJson(route('store.account.profile-picture.update'), [
                'profile_picture' => $file,
            ]);

        $customer->refresh();

        $log = ActivityLog::where('event', 'customer.profile_picture_updated')->first();
        $this->assertNotNull($log);
        $this->assertEquals(Customer::class, $log->subject_type);
        $this->assertEquals($customer->id, $log->subject_id);
        $this->assertEquals(Customer::class, $log->causer_type);
        $this->assertEquals($customer->id, $log->causer_id);
        $this->assertStringContainsString(basename($customer->profile_picture), $log->description);
    }

    public function test_profile_picture_can_be_deleted_by_authenticated_customer()
    {
        $customer = $this->createCustomer([
            'profile_picture' => 'profile_pictures/avatar.jpg'
        ]);
        Storage::disk('public')->put('profile_pictures/avatar.jpg', 'content');

        $response = $this->actingAs($customer, 'customer')
            ->deleteJson(route('store.account.profile-picture.destroy'));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Profile picture deleted successfully.',
        ]);

        $customer->refresh();
        $this->assertNull($customer->profile_picture);
        $this->assertFalse(Storage::disk('public')->exists('profile_pictures/avatar.jpg'));
    }

    public function test_profile_picture_deletion_requires_authentication()
    {
        $response = $this->deleteJson(route('store.account.profile-picture.destroy'));
        $response->assertRedirect(route('store.login'));
    }

    public function test_profile_picture_deletion_creates_activity_log()
    {
        $customer = $this->createCustomer([
            'profile_picture' => 'profile_pictures/avatar.jpg'
        ]);
        Storage::disk('public')->put('profile_pictures/avatar.jpg', 'content');

        $this->actingAs($customer, 'customer')
            ->deleteJson(route('store.account.profile-picture.destroy'));

        $log = ActivityLog::where('event', 'customer.profile_picture_deleted')->first();
        $this->assertNotNull($log);
        $this->assertEquals(Customer::class, $log->subject_type);
        $this->assertEquals($customer->id, $log->subject_id);
        $this->assertEquals(Customer::class, $log->causer_type);
        $this->assertEquals($customer->id, $log->causer_id);
    }
}
