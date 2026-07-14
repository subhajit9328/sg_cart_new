<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PhoneOtpVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    public function test_phone_registration_redirects_to_otp_verification_page_and_does_not_send_email()
    {
        $response = $this->post(route('store.register.submit'), [
            'name' => 'Jane Doe',
            'email_or_phone' => '+1234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertEquals(0, Customer::count());
 
        // Verify redirect
        $response->assertRedirect(route('store.otp.verify'));
 
        // Verify OTP is in cache
        $otpKey = "customer_otp_+1234567890";
        $this->assertTrue(Cache::has($otpKey));
 
        // Verify no mail was sent
        Mail::assertNothingSent();
    }

    public function test_phone_otp_verification_succeeds_with_any_otp_when_test_mode_is_true()
    {
        config(['app.test_mode' => true]);

        $customer = Customer::create([
            'name' => 'Jane Doe',
            'phone_no' => '+1234567890',
            'password' => Hash::make('password123'),
        ]);

        $this->actingAs($customer, 'customer');

        Cache::put("customer_otp_{$customer->id}", '123456', 300);

        $response = $this->post(route('store.otp.verify.submit'), [
            'otp' => '999999', // Incorrect OTP but should succeed because of Test Mode
        ]);

        $response->assertRedirect(route('store.account'));
        $customer->refresh();
        $this->assertNotNull($customer->phone_verified_at);
        $this->assertFalse(Cache::has("customer_otp_{$customer->id}"));
    }

    public function test_phone_otp_verification_fails_with_incorrect_otp_when_test_mode_is_false()
    {
        config(['app.test_mode' => false]);

        $customer = Customer::create([
            'name' => 'Jane Doe',
            'phone_no' => '+1234567890',
            'password' => Hash::make('password123'),
        ]);

        $this->actingAs($customer, 'customer');

        Cache::put("customer_otp_{$customer->id}", '123456', 300);

        $response = $this->post(route('store.otp.verify.submit'), [
            'otp' => '999999', // Incorrect OTP
        ]);

        $response->assertSessionHasErrors('otp');
        $customer->refresh();
        $this->assertNull($customer->phone_verified_at);
    }

    public function test_phone_forgot_password_generates_otp_and_redirects_to_verify()
    {
        $customer = Customer::create([
            'name' => 'Jane Doe',
            'phone_no' => '+1234567890',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post(route('store.forgot-password.submit'), [
            'email_or_phone' => '+1234567890',
        ]);

        $response->assertRedirect(route('store.forgot-password.verify'));
        $this->assertEquals($customer->id, session('forgot_password_customer_id'));
        $this->assertEquals('phone', session('forgot_password_method'));
        $this->assertTrue(Cache::has("customer_otp_{$customer->id}"));
        Mail::assertNothingSent();
    }

    public function test_phone_profile_update_triggers_otp_verification()
    {
        $customer = Customer::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => Hash::make('password123'),
        ]);
        $customer->email_verified_at = now();
        $customer->save();

        $this->actingAs($customer, 'customer');

        $response = $this->post(route('store.account.profile.update'), [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'phone_no' => '+1234567890',
        ]);

        $customer->refresh();

        $response->assertRedirect(route('store.otp.verify'));
        $this->assertEquals('+1234567890', $customer->phone_no);
        $this->assertNull($customer->phone_verified_at);
        $this->assertTrue(Cache::has("customer_otp_{$customer->id}"));
    }

    public function test_phone_profile_update_wrong_phone_cancels_verification()
    {
        $customer = Customer::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => Hash::make('password123'),
        ]);
        $customer->email_verified_at = now();
        $customer->save();

        $this->actingAs($customer, 'customer');

        // First add the phone number from profile
        $response = $this->post(route('store.account.profile.update'), [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'phone_no' => '+1234567890',
        ]);

        $customer->refresh();
        $this->assertEquals('+1234567890', $customer->phone_no);
        $this->assertNull($customer->phone_verified_at);

        // Click "Wrong Phone No." cancel route
        $cancelResponse = $this->get(route('store.otp.cancel'));

        // Assert redirect back to profile page
        $cancelResponse->assertRedirect(route('store.account', 'profile'));
        $cancelResponse->assertSessionHas('info', 'Phone number verification cancelled.');

        // Verify the phone number was cleared (set to null) so they are not asked to verify OTP
        $customer->refresh();
        $this->assertNull($customer->phone_no);
        $this->assertNull($customer->phone_verified_at);
    }
}
