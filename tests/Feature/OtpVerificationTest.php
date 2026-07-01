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

class OtpVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_redirects_to_otp_verification_page_and_sends_email()
    {
        Mail::fake();

        $response = $this->post(route('store.register.submit'), [
            'name' => 'John Doe',
            'email_or_phone' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // Customer should NOT be in the database yet
        $this->assertEquals(0, Customer::count());

        // Verify redirect
        $response->assertRedirect(route('store.otp.verify'));

        // Verify OTP is in cache under email identifier
        $otpKey = "customer_otp_john@example.com";
        $cooldownKey = "customer_otp_cooldown_john@example.com";
        $this->assertTrue(Cache::has($otpKey));
        $this->assertTrue(Cache::has($cooldownKey));

        // Verify mail sent
        Mail::assertSent(CustomerOtpMail::class, function ($mail) {
            return $mail->customer->email === 'john@example.com';
        });
    }

    public function test_successful_email_registration_after_otp_verification()
    {
        Mail::fake();

        $response = $this->post(route('store.register.submit'), [
            'name' => 'John Doe',
            'email_or_phone' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertEquals(0, Customer::count());

        $otp = Cache::get("customer_otp_john@example.com");
        $this->assertNotNull($otp);

        // Submit the correct OTP
        $response2 = $this->post(route('store.otp.verify.submit'), [
            'otp' => $otp,
        ]);

        // Should create customer, verify email, and login
        $customer = Customer::where('email', 'john@example.com')->first();
        $this->assertNotNull($customer);
        $this->assertNotNull($customer->email_verified_at);
        $this->assertEquals('John Doe', $customer->name);
        $this->assertTrue(Auth::guard('customer')->check());
        $this->assertEquals($customer->id, Auth::guard('customer')->id());

        $response2->assertRedirect(route('store.account'));

        // Welcome mail sent
        Mail::assertSent(RegistrationSuccessMail::class);
    }

    public function test_successful_phone_registration_after_otp_verification()
    {
        $response = $this->post(route('store.register.submit'), [
            'name' => 'Jane Phone',
            'email_or_phone' => '+919876543210',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertEquals(0, Customer::count());

        $otp = Cache::get("customer_otp_+919876543210");
        $this->assertNotNull($otp);

        // Submit the correct OTP
        $response2 = $this->post(route('store.otp.verify.submit'), [
            'otp' => $otp,
        ]);

        // Should create customer, verify phone, and login
        $customer = Customer::where('phone_no', '+919876543210')->first();
        $this->assertNotNull($customer);
        $this->assertNotNull($customer->phone_verified_at);
        $this->assertNull($customer->email);
        $this->assertTrue(Auth::guard('customer')->check());

        $response2->assertRedirect(route('store.account'));
    }

    public function test_incorrect_otp_fails_verification()
    {
        $customer = Customer::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->actingAs($customer, 'customer');

        // Put a valid OTP in cache using new key format
        Cache::put("customer_otp_john@example.com", '123456', 300);

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

        Cache::put("customer_otp_john@example.com", '123456', 300);
        Cache::put("customer_otp_cooldown_john@example.com", now()->addMinutes(5)->timestamp, 300);

        $response = $this->post(route('store.otp.verify.submit'), [
            'otp' => '123456',
        ]);

        $response->assertRedirect(route('store.account'));
        $customer->refresh();
        $this->assertNotNull($customer->email_verified_at);

        // Verify cache is cleared
        $this->assertFalse(Cache::has("customer_otp_john@example.com"));
        $this->assertFalse(Cache::has("customer_otp_cooldown_john@example.com"));

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
        Cache::put("customer_otp_cooldown_john@example.com", now()->addMinutes(5)->timestamp, 300);

        $response = $this->post(route('store.otp.resend'));
        $response->assertSessionHas('error');
        Mail::assertNotSent(CustomerOtpMail::class);

        // Remove cooldown to simulate time passing
        Cache::forget("customer_otp_cooldown_john@example.com");

        $response = $this->post(route('store.otp.resend'));
        $response->assertSessionHas('success');
        
        $this->assertTrue(Cache::has("customer_otp_john@example.com"));
        $this->assertTrue(Cache::has("customer_otp_cooldown_john@example.com"));
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
        $this->assertFalse(Cache::has("customer_otp_john@example.com"));

        $response = $this->get(route('store.otp.verify'));

        $this->assertTrue(Cache::has("customer_otp_john@example.com"));
        $this->assertTrue(Cache::has("customer_otp_cooldown_john@example.com"));
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

    public function test_registration_with_phone_number_redirects_to_otp_verification()
    {
        Mail::fake();

        $response = $this->post(route('store.register.submit'), [
            'name' => 'Jane Phone',
            'email_or_phone' => '+919876543210',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // Customer should NOT be in database yet
        $this->assertEquals(0, Customer::count());

        // Verify redirect is to OTP verify page
        $response->assertRedirect(route('store.otp.verify'));

        // Verify OTP is in cache and no mail sent
        $otpKey = "customer_otp_+919876543210";
        $this->assertTrue(Cache::has($otpKey));
        Mail::assertNothingSent();
    }

    public function test_registration_fails_on_invalid_phone_format()
    {
        // Missing '+' country code prefix
        $response = $this->post(route('store.register.submit'), [
            'name' => 'Invalid Phone',
            'email_or_phone' => '9876543210',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email_or_phone');
        $this->assertEquals(0, Customer::count());

        // Too short or contains letters
        $response2 = $this->post(route('store.register.submit'), [
            'name' => 'Invalid Phone 2',
            'email_or_phone' => '+12345abc',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response2->assertSessionHasErrors('email_or_phone');
        $this->assertEquals(0, Customer::count());
    }

    public function test_registration_uniqueness_checks()
    {
        // Create an existing customer with email and one with phone
        Customer::create([
            'name' => 'Existing Email User',
            'email' => 'existing@example.com',
            'password' => Hash::make('password123'),
        ]);

        Customer::create([
            'name' => 'Existing Phone User',
            'phone_no' => '+918888888888',
            'password' => Hash::make('password123'),
        ]);

        // Attempting to register with the same email should fail
        $response1 = $this->post(route('store.register.submit'), [
            'name' => 'New User',
            'email_or_phone' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response1->assertSessionHasErrors('email_or_phone');

        // Attempting to register with the same phone number should fail
        $response2 = $this->post(route('store.register.submit'), [
            'name' => 'New User 2',
            'email_or_phone' => '+918888888888',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response2->assertSessionHasErrors('email_or_phone');
    }

    public function test_login_succeeds_with_email_or_phone()
    {
        $customer = Customer::create([
            'name' => 'Dual User',
            'email' => 'dual@example.com',
            'phone_no' => '+919999999999',
            'password' => Hash::make('password123'),
        ]);

        // Attempt login via email
        $responseEmail = $this->post(route('store.login.submit'), [
            'email_or_phone' => 'dual@example.com',
            'password' => 'password123',
        ]);
        $responseEmail->assertRedirect(route('store.account'));
        $this->assertTrue(Auth::guard('customer')->check());

        Auth::guard('customer')->logout();

        // Attempt login via phone_no
        $responsePhone = $this->post(route('store.login.submit'), [
            'email_or_phone' => '+919999999999',
            'password' => 'password123',
        ]);
        $responsePhone->assertRedirect(route('store.account'));
        $this->assertTrue(Auth::guard('customer')->check());
    }

    public function test_profile_update_adds_missing_email_and_sends_otp()
    {
        Mail::fake();

        $customer = Customer::create([
            'name' => 'Phone User',
            'phone_no' => '+919999999999',
            'password' => Hash::make('password123'),
        ]);
        $customer->phone_verified_at = now();
        $customer->save();

        $this->actingAs($customer, 'customer');

        $response = $this->post(route('store.account.profile.update'), [
            'first_name' => 'Updated',
            'last_name' => 'User',
            'email' => 'newemail@example.com',
        ]);

        $customer->refresh();
        $this->assertEquals('Updated User', $customer->name);
        $this->assertEquals('newemail@example.com', $customer->email);
        $this->assertNull($customer->email_verified_at);

        // Verify redirect to OTP page
        $response->assertRedirect(route('store.otp.verify'));

        // Verify OTP is generated
        $otpKey = "customer_otp_newemail@example.com";
        $this->assertTrue(Cache::has($otpKey));

        // Verify OTP mail sent
        Mail::assertSent(CustomerOtpMail::class);
    }

    public function test_profile_update_adds_missing_phone_and_generates_otp()
    {
        Mail::fake();

        $customer = Customer::create([
            'name' => 'Email User',
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);
        $customer->email_verified_at = now();
        $customer->save();

        $this->actingAs($customer, 'customer');

        $response = $this->post(route('store.account.profile.update'), [
            'first_name' => 'Updated',
            'last_name' => 'User',
            'phone_no' => '+919999999999',
        ]);

        $customer->refresh();
        $this->assertEquals('Updated User', $customer->name);
        $this->assertEquals('+919999999999', $customer->phone_no);

        // Redirects to OTP verify page
        $response->assertRedirect(route('store.otp.verify'));

        // OTP generated but no mail sent
        $otpKey = "customer_otp_+919999999999";
        $this->assertTrue(Cache::has($otpKey));
        Mail::assertNotSent(CustomerOtpMail::class);
    }

    public function test_profile_update_strict_validation_preventions()
    {
        // 1. Email is already set. Cannot update email.
        $customer1 = Customer::create([
            'name' => 'Has Email',
            'email' => 'hasemail@example.com',
            'password' => Hash::make('password123'),
        ]);
        $customer1->email_verified_at = now();
        $customer1->save();

        $this->actingAs($customer1, 'customer');

        $response1 = $this->post(route('store.account.profile.update'), [
            'first_name' => 'Has',
            'last_name' => 'Email',
            'email' => 'newemail@example.com',
        ]);

        $response1->assertSessionHasErrors('email');
        $customer1->refresh();
        $this->assertEquals('hasemail@example.com', $customer1->email);

        Auth::guard('customer')->logout();

        // 2. Phone is already set. Cannot update phone.
        $customer2 = Customer::create([
            'name' => 'Has Phone',
            'phone_no' => '+917777777777',
            'password' => Hash::make('password123'),
        ]);
        $customer2->phone_verified_at = now();
        $customer2->save();

        $this->actingAs($customer2, 'customer');

        $response2 = $this->post(route('store.account.profile.update'), [
            'first_name' => 'Has',
            'last_name' => 'Phone',
            'phone_no' => '+916666666666',
        ]);

        $response2->assertSessionHasErrors('phone_no');
        $customer2->refresh();
        $this->assertEquals('+917777777777', $customer2->phone_no);

        Auth::guard('customer')->logout();

        // 3. Cannot submit both.
        $customer3 = Customer::create([
            'name' => 'No Contact Info',
            'password' => Hash::make('password123'),
        ]);

        $this->actingAs($customer3, 'customer');

        $response3 = $this->post(route('store.account.profile.update'), [
            'first_name' => 'No',
            'last_name' => 'Contact',
            'email' => 'email@example.com',
            'phone_no' => '+919999999999',
        ]);

        $response3->assertSessionHasErrors('email');
    }

    public function test_registration_phone_number_separated_validation_errors()
    {
        // 1. Missing + country code
        $response1 = $this->post(route('store.register.submit'), [
            'name' => 'Test User',
            'email_or_phone' => '1234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response1->assertSessionHasErrors(['email_or_phone' => 'The phone number must include a country code starting with +.']);

        // 2. Non-digits after +
        $response2 = $this->post(route('store.register.submit'), [
            'name' => 'Test User',
            'email_or_phone' => '+12345678#0',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response2->assertSessionHasErrors(['email_or_phone' => 'The phone number must contain only digits after the + country code.']);

        // 3. More than 15 digits
        $response3 = $this->post(route('store.register.submit'), [
            'name' => 'Test User',
            'email_or_phone' => '+1234567890123456',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response3->assertSessionHasErrors(['email_or_phone' => 'The phone number must not be more than 15 digits.']);

        // 4. Less than 7 digits
        $response4 = $this->post(route('store.register.submit'), [
            'name' => 'Test User',
            'email_or_phone' => '+123456',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response4->assertSessionHasErrors(['email_or_phone' => 'The phone number must be at least 7 digits.']);
    }
}
