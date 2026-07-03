<?php

namespace Tests\Feature;

use App\Mail\CustomerOtpMail;
use App\Mail\RegistrationSuccessMail;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class CustomerApiAuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful registration with email triggers OTP and caches data.
     */
    public function test_api_registration_returns_otp_and_caches_pending_data()
    {
        $response = $this->postJson('/api/customer/register', [
            'name' => 'API John',
            'email_or_phone' => 'apijohn@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_email' => true,
        ]);
        $response->assertJsonStructure(['otp', 'verification_code']);

        // Customer should NOT be in the DB yet
        $this->assertEquals(0, Customer::count());

        // Cache should have registration data and OTP
        $pendingKey = 'api_pending_reg_' . md5('apijohn@example.com');
        $this->assertTrue(Cache::has($pendingKey));
        $this->assertTrue(Cache::has('customer_otp_apijohn@example.com'));
        $this->assertEquals(Cache::get('customer_otp_apijohn@example.com'), $response->json('otp'));
    }

    /**
     * Test successful registration with phone number.
     */
    public function test_api_registration_sends_otp_for_phone()
    {
        $response = $this->postJson('/api/customer/register', [
            'name' => 'API Jane',
            'email_or_phone' => '+12345678901',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_email' => false,
        ]);
        $response->assertJsonStructure(['otp']);

        $pendingKey = 'api_pending_reg_' . md5('+12345678901');
        $this->assertTrue(Cache::has($pendingKey));
        $this->assertTrue(Cache::has('customer_otp_+12345678901'));
    }

    /**
     * Test registration validation failures.
     */
    public function test_api_registration_fails_validation()
    {
        // Missing name and mismatch password
        $response = $this->postJson('/api/customer/register', [
            'email_or_phone' => 'bad_email',
            'password' => 'password123',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'email_or_phone', 'password']);
    }

    /**
     * Test phone format validation in registration.
     */
    public function test_api_registration_fails_on_bad_phone_format()
    {
        // No country code '+'
        $response = $this->postJson('/api/customer/register', [
            'name' => 'API Phone Fail',
            'email_or_phone' => '1234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email_or_phone']);
    }

    /**
     * Test successful verification of OTP creating the customer and returning JWT.
     */
    public function test_api_successful_otp_verification_creates_customer_and_returns_jwt()
    {
        Mail::fake();

        // 1. Submit registration
        $this->postJson('/api/customer/register', [
            'name' => 'Verified User',
            'email_or_phone' => 'verify@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $otp = Cache::get('customer_otp_verify@example.com');
        $this->assertNotNull($otp);

        // 2. Submit correct verification code
        $response = $this->postJson('/api/customer/verify-otp', [
            'email_or_phone' => 'verify@example.com',
            'verification_code' => $otp,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'access_token',
            'token_type',
            'expires_in',
            'user' => ['id', 'ulid', 'name', 'email', 'phone_no'],
        ]);

        // Customer should be in DB and verified
        $customer = Customer::where('email', 'verify@example.com')->first();
        $this->assertNotNull($customer);
        $this->assertNotNull($customer->email_verified_at);

        // Welcome success email sent
        Mail::assertSent(RegistrationSuccessMail::class);
    }

    /**
     * Test incorrect OTP validation failure.
     */
    public function test_api_incorrect_otp_fails_verification()
    {
        $this->postJson('/api/customer/register', [
            'name' => 'Wrong OTP User',
            'email_or_phone' => 'wrongotp@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response = $this->postJson('/api/customer/verify-otp', [
            'email_or_phone' => 'wrongotp@example.com',
            'otp' => '000000', // Incorrect OTP
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['otp']);
    }

    /**
     * Test login returns JWT for verified customer.
     */
    public function test_api_login_returns_jwt_for_verified_customer()
    {
        $customer = new Customer([
            'name' => 'API Login User',
            'email' => 'login@example.com',
            'password' => Hash::make('password123'),
        ]);
        $customer->email_verified_at = now();
        $customer->save();

        $response = $this->postJson('/api/customer/login', [
            'email_or_phone' => 'login@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'access_token',
            'token_type',
            'expires_in',
            'user',
        ]);
    }

    /**
     * Test login rejects unverified customer and triggers OTP verification flow.
     */
    public function test_api_login_returns_verification_required_for_unverified_customer()
    {
        $customer = Customer::create([
            'name' => 'Unverified API User',
            'email' => 'unverified@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => null, // Unverified
        ]);

        $response = $this->postJson('/api/customer/login', [
            'email_or_phone' => 'unverified@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'code' => 'VERIFICATION_REQUIRED',
            'email_or_phone' => 'unverified@example.com',
        ]);
        $response->assertJsonStructure(['otp']);

        // Should have generated a verification OTP
        $this->assertTrue(Cache::has('customer_otp_unverified@example.com'));
    }

    /**
     * Test login detects non-existent user and notifies app.
     */
    public function test_api_login_returns_user_not_found_code()
    {
        $response = $this->postJson('/api/customer/login', [
            'email_or_phone' => 'no_user@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'code' => 'USER_NOT_FOUND',
        ]);
    }

    /**
     * Test resending OTP works for pending registration.
     */
    public function test_api_resend_otp_pending_registration()
    {
        $this->postJson('/api/customer/register', [
            'name' => 'Resend User',
            'email_or_phone' => 'resend@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // Remove OTP and cooldown to simulate expiry/resend readiness
        Cache::forget('customer_otp_resend@example.com');
        Cache::forget('customer_otp_cooldown_resend@example.com');

        $response = $this->postJson('/api/customer/resend-otp', [
            'email_or_phone' => 'resend@example.com',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure(['otp']);
        $this->assertTrue(Cache::has('customer_otp_resend@example.com'));
    }

    /**
     * Test authenticated routes (me and logout).
     */
    public function test_api_authenticated_me_and_logout()
    {
        $customer = new Customer([
            'name' => 'API Authenticated User',
            'email' => 'authed@example.com',
            'password' => Hash::make('password123'),
        ]);
        $customer->email_verified_at = now();
        $customer->save();

        $loginResponse = $this->postJson('/api/customer/login', [
            'email_or_phone' => 'authed@example.com',
            'password' => 'password123',
        ]);

        $token = $loginResponse->json('access_token');
        $this->assertNotNull($token);

        // Access "me" route
        $meResponse = $this->getJson('/api/customer/me', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $meResponse->assertStatus(200);
        $meResponse->assertJson([
            'success' => true,
            'user' => [
                'email' => 'authed@example.com',
            ],
        ]);

        // Access "logout" route
        $logoutResponse = $this->postJson('/api/customer/logout', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $logoutResponse->assertStatus(200);
        $logoutResponse->assertJson(['success' => true]);

        // Attempting to access "me" after logout should fail
        $meResponse2 = $this->getJson('/api/customer/me', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $meResponse2->assertStatus(401);
    }
}
