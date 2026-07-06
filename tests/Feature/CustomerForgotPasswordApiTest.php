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

class CustomerForgotPasswordApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_request_forgot_password_fails_for_non_existent_user(): void
    {
        $response = $this->postJson('/api/customer/forgot-password', [
            'email_or_phone' => 'nonexistent@example.com',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email_or_phone');
    }

    public function test_api_request_forgot_password_via_email_generates_otp_and_logs_event(): void
    {
        Mail::fake();

        $customer = Customer::create([
            'name' => 'John Reset',
            'email' => 'john.reset@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/customer/forgot-password', [
            'email_or_phone' => 'john.reset@example.com',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'email_or_phone' => 'john.reset@example.com',
                'is_email' => true,
            ],
        ]);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => ['email_or_phone', 'is_email', 'verification_code', 'otp'],
        ]);

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

    public function test_api_request_forgot_password_via_phone_generates_otp_and_logs_event(): void
    {
        Mail::fake();

        $customer = Customer::create([
            'name' => 'Jane Phone Reset',
            'phone_no' => '+919999999999',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/customer/forgot-password', [
            'email_or_phone' => '+919999999999',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'email_or_phone' => '+919999999999',
                'is_email' => false,
            ],
        ]);

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

    public function test_api_otp_verification_fails_for_incorrect_otp(): void
    {
        $customer = Customer::create([
            'name' => 'John Reset',
            'email' => 'john.reset@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Place a valid OTP in cache
        Cache::put("customer_otp_{$customer->id}", '123456', 300);

        $response = $this->postJson('/api/customer/forgot-password/verify', [
            'email_or_phone' => 'john.reset@example.com',
            'otp' => '654321', // incorrect OTP
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['otp', 'verification_code']);
        $this->assertFalse(Cache::has("api_password_reset_authorized_{$customer->id}"));
    }

    public function test_api_otp_verification_succeeds_for_correct_otp(): void
    {
        $customer = Customer::create([
            'name' => 'John Reset',
            'email' => 'john.reset@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Place a valid OTP in cache
        Cache::put("customer_otp_{$customer->id}", '123456', 300);

        $response = $this->postJson('/api/customer/forgot-password/verify', [
            'email_or_phone' => 'john.reset@example.com',
            'otp' => '123456',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'email_or_phone' => 'john.reset@example.com',
            ],
        ]);

        $this->assertTrue(Cache::has("api_password_reset_authorized_{$customer->id}"));

        // Cache OTP must be cleared after verification
        $this->assertFalse(Cache::has("customer_otp_{$customer->id}"));
    }

    public function test_api_resend_otp_respects_cooldown(): void
    {
        Mail::fake();

        $customer = Customer::create([
            'name' => 'John Reset',
            'email' => 'john.reset@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Set cooldown in cache
        Cache::put("customer_otp_cooldown_{$customer->id}", now()->addMinutes(5)->timestamp, 300);

        $response = $this->postJson('/api/customer/forgot-password/resend', [
            'email_or_phone' => 'john.reset@example.com',
        ]);

        $response->assertStatus(429);
        $response->assertJson([
            'success' => false,
        ]);
        $response->assertJsonStructure(['cooldown_remaining_seconds']);
        Mail::assertNotSent(CustomerOtpMail::class);

        // Remove cooldown to simulate 5 minutes passing
        Cache::forget("customer_otp_cooldown_{$customer->id}");

        $response2 = $this->postJson('/api/customer/forgot-password/resend', [
            'email_or_phone' => 'john.reset@example.com',
        ]);

        $response2->assertStatus(200);
        $response2->assertJson([
            'success' => true,
        ]);
        Mail::assertSent(CustomerOtpMail::class);
    }

    public function test_api_reset_password_updates_password_and_logs_activity(): void
    {
        $customer = Customer::create([
            'name' => 'John Reset',
            'email' => 'john.reset@example.com',
            'password' => Hash::make('old_password'),
        ]);

        Cache::put("api_password_reset_authorized_{$customer->id}", true, 300);

        $response = $this->postJson('/api/customer/forgot-password/reset', [
            'email_or_phone' => 'john.reset@example.com',
            'password' => 'new_password123',
            'password_confirmation' => 'new_password123',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $this->assertFalse(Cache::has("api_password_reset_authorized_{$customer->id}"));

        $customer->refresh();
        $this->assertTrue(Hash::check('new_password123', $customer->password));

        // Verify activity log
        $log = ActivityLog::where('event', 'password_reset.success')->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('Customer password reset successfully', $log->description);
    }

    public function test_api_reset_password_fails_if_not_authorized(): void
    {
        $customer = Customer::create([
            'name' => 'John Reset',
            'email' => 'john.reset@example.com',
            'password' => Hash::make('old_password'),
        ]);

        $response = $this->postJson('/api/customer/forgot-password/reset', [
            'email_or_phone' => 'john.reset@example.com',
            'password' => 'new_password123',
            'password_confirmation' => 'new_password123',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'Please verify your OTP code first.',
        ]);
    }

    public function test_api_request_forgot_password_within_cooldown_returns_info_without_generating_new_otp(): void
    {
        Mail::fake();

        $customer = Customer::create([
            'name' => 'John Reset',
            'email' => 'john.reset@example.com',
            'password' => Hash::make('password123'),
        ]);

        // First request: generates OTP and sets cooldown
        $this->postJson('/api/customer/forgot-password', [
            'email_or_phone' => 'john.reset@example.com',
        ]);

        $otpKey = "customer_otp_{$customer->id}";
        $firstOtp = Cache::get($otpKey);
        $this->assertNotNull($firstOtp);

        Mail::assertSentCount(1);

        // Second request within cooldown: should return info without generating new OTP
        $response = $this->postJson('/api/customer/forgot-password', [
            'email_or_phone' => 'john.reset@example.com',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'A verification code was already sent recently. You can verify it here.',
            'data' => [
                'email_or_phone' => 'john.reset@example.com',
                'is_email' => true,
            ]
        ]);
        $response->assertJsonStructure([
            'data' => ['cooldown_remaining_seconds']
        ]);

        // Verify OTP was not changed
        $this->assertEquals($firstOtp, Cache::get($otpKey));

        // Mail should still have been sent only once
        Mail::assertSentCount(1);
    }
}
