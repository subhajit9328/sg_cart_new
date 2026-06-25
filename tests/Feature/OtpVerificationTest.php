<?php

namespace Tests\Feature;

use App\Mail\CustomerOtpMail;
use App\Mail\RegistrationSuccessMail;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OtpVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_redirects_to_otp_verification_page_and_sends_email()
    {
        Mail::fake();

        $response = $this->post(route('store.register.submit'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $customer = Customer::where('email', 'john@example.com')->first();
        $this->assertNotNull($customer);
        $this->assertNull($customer->email_verified_at);

        // Verify redirect
        $response->assertRedirect(route('store.otp.verify'));

        // Verify OTP is in cache
        $otpKey = "customer_otp_{$customer->id}";
        $cooldownKey = "customer_otp_cooldown_{$customer->id}";
        $this->assertTrue(Cache::has($otpKey));
        $this->assertTrue(Cache::has($cooldownKey));

        // Verify mail sent
        Mail::assertSent(CustomerOtpMail::class, function ($mail) use ($customer) {
            return $mail->customer->id === $customer->id;
        });
    }

    public function test_incorrect_otp_fails_verification()
    {
        $customer = Customer::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->actingAs($customer, 'customer');

        // Put a valid OTP in cache
        Cache::put("customer_otp_{$customer->id}", '123456', 300);

        $response = $this->post(route('store.otp.verify.submit'), [
            'otp' => '111111',
        ]);

        $response->assertSessionHasErrors('otp');
        $customer->refresh();
        $this->assertNull($customer->email_verified_at);
    }

    public function test_correct_otp_verifies_email_and_clears_cache()
    {
        Mail::fake();

        $customer = Customer::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->actingAs($customer, 'customer');

        Cache::put("customer_otp_{$customer->id}", '123456', 300);
        Cache::put("customer_otp_cooldown_{$customer->id}", now()->addMinutes(5)->timestamp, 300);

        $response = $this->post(route('store.otp.verify.submit'), [
            'otp' => '123456',
        ]);

        $response->assertRedirect(route('store.account'));
        $customer->refresh();
        $this->assertNotNull($customer->email_verified_at);

        // Verify cache is cleared
        $this->assertFalse(Cache::has("customer_otp_{$customer->id}"));
        $this->assertFalse(Cache::has("customer_otp_cooldown_{$customer->id}"));

        // Verify welcome mail sent
        Mail::assertSent(RegistrationSuccessMail::class);
    }

    public function test_resend_fails_during_cooldown_and_succeeds_after()
    {
        Mail::fake();

        $customer = Customer::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->actingAs($customer, 'customer');

        // Set cooldown in the future
        Cache::put("customer_otp_cooldown_{$customer->id}", now()->addMinutes(5)->timestamp, 300);

        $response = $this->post(route('store.otp.resend'));
        $response->assertSessionHas('error');
        Mail::assertNotSent(CustomerOtpMail::class);

        // Remove cooldown to simulate time passing
        Cache::forget("customer_otp_cooldown_{$customer->id}");

        $response = $this->post(route('store.otp.resend'));
        $response->assertSessionHas('success');
        
        $this->assertTrue(Cache::has("customer_otp_{$customer->id}"));
        $this->assertTrue(Cache::has("customer_otp_cooldown_{$customer->id}"));
        Mail::assertSent(CustomerOtpMail::class);
    }

    public function test_visiting_verification_page_auto_generates_otp_if_expired_and_no_cooldown()
    {
        Mail::fake();

        $customer = Customer::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->actingAs($customer, 'customer');

        // No OTP or cooldown in cache
        $this->assertFalse(Cache::has("customer_otp_{$customer->id}"));

        $response = $this->get(route('store.otp.verify'));

        $this->assertTrue(Cache::has("customer_otp_{$customer->id}"));
        $this->assertTrue(Cache::has("customer_otp_cooldown_{$customer->id}"));
        Mail::assertSent(CustomerOtpMail::class);
    }

    public function test_unverified_customer_is_redirected_to_otp_verification()
    {
        $customer = Customer::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->actingAs($customer, 'customer');

        $response = $this->get(route('store.checkout'));
        $response->assertRedirect(route('store.otp.verify'));
    }
}
