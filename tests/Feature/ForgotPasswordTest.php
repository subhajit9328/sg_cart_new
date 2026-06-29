<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mail\CustomerOtpMail;
use App\Models\ActivityLog;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_request_forgot_password_fails_for_non_existent_user(): void
    {
        $response = $this->post(route('store.forgot-password.submit'), [
            'email_or_phone' => 'nonexistent@example.com',
        ]);

        $response->assertSessionHasErrors('email_or_phone');
    }

    public function test_request_forgot_password_via_email_generates_otp_and_logs_event(): void
    {
        Mail::fake();

        $customer = Customer::create([
            'name' => 'John Reset',
            'email' => 'john.reset@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post(route('store.forgot-password.submit'), [
            'email_or_phone' => 'john.reset@example.com',
        ]);

        $response->assertRedirect(route('store.forgot-password.verify'));
        $this->assertEquals($customer->id, session('forgot_password_customer_id'));

        // Verify OTP is generated in cache
        $otpKey = "customer_otp_{$customer->id}";
        $this->assertTrue(Cache::has($otpKey));

        // Verify mail sent with forgot_password reason
        Mail::assertSent(CustomerOtpMail::class, function ($mail) use ($customer) {
            return $mail->customer->id === $customer->id && $mail->reason === 'forgot_password';
        });

        // Verify Activity Log
        $log = ActivityLog::where('event', 'forgot_password.request')->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('Forgot password requested via email', $log->description);
        $this->assertEquals('email', $log->properties['method']);
    }

    public function test_request_forgot_password_via_phone_generates_otp_and_logs_event(): void
    {
        Mail::fake();

        $customer = Customer::create([
            'name' => 'Jane Phone Reset',
            'phone_no' => '+919999999999',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post(route('store.forgot-password.submit'), [
            'email_or_phone' => '+919999999999',
        ]);

        $response->assertRedirect(route('store.forgot-password.verify'));
        $response->assertSessionHas('success');

        // Verify OTP is generated in cache
        $otpKey = "customer_otp_{$customer->id}";
        $this->assertTrue(Cache::has($otpKey));
        Mail::assertNothingSent();

        // Verify Activity Log
        $log = ActivityLog::where('event', 'forgot_password.request')->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('Forgot password requested via phone', $log->description);
        $this->assertEquals('phone', $log->properties['method']);
    }

    public function test_otp_verification_fails_for_incorrect_otp(): void
    {
        $customer = Customer::create([
            'name' => 'John Reset',
            'email' => 'john.reset@example.com',
            'password' => Hash::make('password123'),
        ]);

        session(['forgot_password_customer_id' => $customer->id]);

        // Place a valid OTP in cache
        Cache::put("customer_otp_{$customer->id}", '123456', 300);

        $response = $this->post(route('store.forgot-password.verify.submit'), [
            'otp' => '654321', // incorrect OTP
        ]);

        $response->assertSessionHasErrors('otp');
        $this->assertNull(session('password_reset_authorized_customer_id'));
    }

    public function test_otp_verification_succeeds_for_correct_otp(): void
    {
        $customer = Customer::create([
            'name' => 'John Reset',
            'email' => 'john.reset@example.com',
            'password' => Hash::make('password123'),
        ]);

        session(['forgot_password_customer_id' => $customer->id]);

        // Place a valid OTP in cache
        Cache::put("customer_otp_{$customer->id}", '123456', 300);

        $response = $this->post(route('store.forgot-password.verify.submit'), [
            'otp' => '123456',
        ]);

        $response->assertRedirect(route('store.forgot-password.reset'));
        $this->assertEquals($customer->id, session('password_reset_authorized_customer_id'));
        $this->assertNull(session('forgot_password_customer_id'));

        // Cache must be cleared
        $this->assertFalse(Cache::has("customer_otp_{$customer->id}"));
    }

    public function test_resend_otp_respects_cooldown(): void
    {
        Mail::fake();

        $customer = Customer::create([
            'name' => 'John Reset',
            'email' => 'john.reset@example.com',
            'password' => Hash::make('password123'),
        ]);

        session(['forgot_password_customer_id' => $customer->id]);

        // Set cooldown in cache
        Cache::put("customer_otp_cooldown_{$customer->id}", now()->addMinutes(5)->timestamp, 300);

        $response = $this->post(route('store.forgot-password.resend'));
        $response->assertSessionHas('error');
        Mail::assertNotSent(CustomerOtpMail::class);

        // Remove cooldown to simulate 5 minutes passing
        Cache::forget("customer_otp_cooldown_{$customer->id}");

        $response2 = $this->post(route('store.forgot-password.resend'));
        $response2->assertSessionHas('success');
        Mail::assertSent(CustomerOtpMail::class);
    }

    public function test_reset_password_updates_password_and_logs_activity(): void
    {
        $customer = Customer::create([
            'name' => 'John Reset',
            'email' => 'john.reset@example.com',
            'password' => Hash::make('old_password'),
        ]);

        session(['password_reset_authorized_customer_id' => $customer->id]);

        $response = $this->post(route('store.forgot-password.reset.submit'), [
            'password' => 'new_password123',
            'password_confirmation' => 'new_password123',
        ]);

        $response->assertRedirect(route('store.login'));
        $this->assertNull(session('password_reset_authorized_customer_id'));

        $customer->refresh();
        $this->assertTrue(Hash::check('new_password123', $customer->password));

        // Verify activity log
        $log = ActivityLog::where('event', 'password_reset.success')->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('Customer password reset successfully', $log->description);
    }

    public function test_request_forgot_password_within_cooldown_redirects_to_verify_without_generating_new_otp(): void
    {
        Mail::fake();

        $customer = Customer::create([
            'name' => 'John Reset',
            'email' => 'john.reset@example.com',
            'password' => Hash::make('password123'),
        ]);

        // First request: generates OTP and sets cooldown
        $this->post(route('store.forgot-password.submit'), [
            'email_or_phone' => 'john.reset@example.com',
        ]);

        $otpKey = "customer_otp_{$customer->id}";
        $firstOtp = Cache::get($otpKey);
        $this->assertNotNull($firstOtp);

        Mail::assertSentCount(1);

        // Second request within cooldown: should redirect to verify page without generating new OTP
        $response = $this->post(route('store.forgot-password.submit'), [
            'email_or_phone' => 'john.reset@example.com',
        ]);

        $response->assertRedirect(route('store.forgot-password.verify'));
        $response->assertSessionHas('info', 'A verification code was already sent recently. You can verify it here.');

        // Verify OTP was not changed
        $this->assertEquals($firstOtp, Cache::get($otpKey));

        // Mail should still have been sent only once
        Mail::assertSentCount(1);
    }
}
