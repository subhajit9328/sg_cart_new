<?php

namespace Tests\Feature;

use App\Mail\CustomerOtpMail;
use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SecurityLoggingTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_registration_logs_activity_and_otp_sent()
    {
        Mail::fake();

        $response = $this->post(route('store.register.submit'), [
            'name' => 'Jane Doe',
            'email_or_phone' => 'jane@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertEquals(0, Customer::count());

        // Assert OTP sent log was written (subject is null since customer not created yet)
        $otpSentLog = ActivityLog::where('event', 'otp.sent')->first();
        $this->assertNotNull($otpSentLog);
        $this->assertEquals("OTP sent to email: jane@example.com", $otpSentLog->description);
        $this->assertEquals(Customer::class, $otpSentLog->subject_type);
        $this->assertNull($otpSentLog->subject_id);
        $this->assertEquals('jane@example.com', $otpSentLog->properties['email']);
        $this->assertArrayHasKey('otp_sent_time', $otpSentLog->properties);
        $this->assertArrayHasKey('ip', $otpSentLog->properties);
        $this->assertArrayHasKey('user_agent', $otpSentLog->properties);

        // Get OTP from cache
        $otp = Cache::get("customer_otp_jane@example.com");
        $this->assertNotNull($otp);

        // Submit the correct OTP to trigger account creation
        $response2 = $this->post(route('store.otp.verify.submit'), [
            'otp' => $otp,
        ]);

        $customer = Customer::where('email', 'jane@example.com')->first();
        $this->assertNotNull($customer);

        // Assert registration log was written
        $registrationLog = ActivityLog::where('event', 'registration')->first();
        $this->assertNotNull($registrationLog);
        $this->assertEquals("Customer registered: jane@example.com", $registrationLog->description);
        $this->assertEquals(Customer::class, $registrationLog->subject_type);
        $this->assertEquals($customer->id, $registrationLog->subject_id);
        $this->assertEquals(Customer::class, $registrationLog->causer_type);
        $this->assertEquals($customer->id, $registrationLog->causer_id);
        $this->assertEquals('jane@example.com', $registrationLog->properties['email_or_phone']);
    }

    public function test_customer_login_failure_increments_attempts_and_success_resets_them()
    {
        $customer = Customer::create([
            'name' => 'Login User',
            'email' => 'login@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Attempt 1: Fail
        $response = $this->post(route('store.login.submit'), [
            'email_or_phone' => 'login@example.com',
            'password' => 'wrong_password',
        ]);

        $response->assertSessionHasErrors('email_or_phone');

        $failLog1 = ActivityLog::where('event', 'login.failed')->orderBy('id', 'desc')->first();
        $this->assertNotNull($failLog1);
        $this->assertEquals(Customer::class, $failLog1->subject_type);
        $this->assertEquals($customer->id, $failLog1->subject_id);
        $this->assertEquals(1, $failLog1->properties['attempt_count']);

        // Attempt 2: Fail again
        $response = $this->post(route('store.login.submit'), [
            'email_or_phone' => 'login@example.com',
            'password' => 'wrong_password_again',
        ]);

        $failLog2 = ActivityLog::where('event', 'login.failed')->orderBy('id', 'desc')->first();
        $this->assertNotNull($failLog2);
        $this->assertEquals(2, $failLog2->properties['attempt_count']);

        // Attempt 3: Success
        $response = $this->post(route('store.login.submit'), [
            'email_or_phone' => 'login@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('store.account'));

        $successLog = ActivityLog::where('event', 'login.success')->first();
        $this->assertNotNull($successLog);
        $this->assertEquals(Customer::class, $successLog->subject_type);
        $this->assertEquals($customer->id, $successLog->subject_id);
        $this->assertEquals(0, $successLog->properties['attempt_count']);

        // Assert attempt count is cleared in cache
        $this->assertFalse(Cache::has('login_attempts_' . md5('customer_login@example.com')));
    }

    public function test_admin_login_failure_increments_attempts_and_success_logs_activity()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);
        \Spatie\Permission\Models\Permission::create(['name' => 'view dashboard']);
        $admin->givePermissionTo('view dashboard');

        // Attempt 1: Fail
        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'wrong_password',
        ]);

        $failLog = ActivityLog::where('event', 'login.failed')->first();
        $this->assertNotNull($failLog);
        $this->assertEquals(User::class, $failLog->subject_type);
        $this->assertEquals($admin->id, $failLog->subject_id);
        $this->assertEquals(1, $failLog->properties['attempt_count']);

        // Attempt 2: Success
        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $successLog = ActivityLog::where('event', 'login.success')->first();
        $this->assertNotNull($successLog);
        $this->assertEquals(User::class, $successLog->subject_type);
        $this->assertEquals($admin->id, $successLog->subject_id);
        $this->assertEquals(0, $successLog->properties['attempt_count']);
    }

    public function test_otp_verification_success_and_failure_logs()
    {
        $customer = Customer::create([
            'name' => 'Otp User',
            'email' => 'otp@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->actingAs($customer, 'customer');

        Cache::put("customer_otp_otp@example.com", '123456', 300);

        // Verification Failure
        $response = $this->post(route('store.otp.verify.submit'), [
            'otp' => '654321',
        ]);

        $failLog = ActivityLog::where('event', 'otp.failed')->first();
        $this->assertNotNull($failLog);
        $this->assertEquals(Customer::class, $failLog->subject_type);
        $this->assertEquals($customer->id, $failLog->subject_id);
        $this->assertEquals('654321', $failLog->properties['entered_otp']);

        // Verification Success
        $response = $this->post(route('store.otp.verify.submit'), [
            'otp' => '123456',
        ]);

        $successLog = ActivityLog::where('event', 'otp.verified')->first();
        $this->assertNotNull($successLog);
        $this->assertEquals(Customer::class, $successLog->subject_type);
        $this->assertEquals($customer->id, $successLog->subject_id);
        $this->assertEquals('otp@example.com', $successLog->properties['email']);
    }

    public function test_customer_profile_update_logs_changes_in_attribute_changes()
    {
        $customer = Customer::create([
            'name' => 'John Original',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);
        $customer->email_verified_at = now();
        $customer->save();

        $this->actingAs($customer, 'customer');

        $response = $this->post(route('store.account.profile.update'), [
            'first_name' => 'John',
            'last_name' => 'Updated',
            'phone_no' => '+919999999999',
        ]);

        $response->assertRedirect(route('store.otp.verify'));

        $profileLog = ActivityLog::where('event', 'profile.update')->first();
        $this->assertNotNull($profileLog);
        $this->assertEquals(Customer::class, $profileLog->subject_type);
        $this->assertEquals($customer->id, $profileLog->subject_id);

        $changes = $profileLog->attribute_changes;
        $this->assertEquals('John Original', $changes['old']['name']);
        $this->assertEquals('John Updated', $changes['new']['name']);
        $this->assertNull($changes['old']['phone_no']);
        $this->assertEquals('+919999999999', $changes['new']['phone_no']);
    }

    public function test_logouts_are_logged()
    {
        $customer = Customer::create([
            'name' => 'Logout User',
            'email' => 'logout@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->actingAs($customer, 'customer');

        $response = $this->post(route('store.logout'));

        $logoutLog = ActivityLog::where('event', 'logout')->first();
        $this->assertNotNull($logoutLog);
        $this->assertEquals(Customer::class, $logoutLog->subject_type);
        $this->assertEquals($customer->id, $logoutLog->subject_id);
        $this->assertEquals(Customer::class, $logoutLog->causer_type);
        $this->assertEquals($customer->id, $logoutLog->causer_id);
    }

    public function test_non_existent_customer_login_redirects_to_registration_page_with_prefilled_input()
    {
        $response = $this->post(route('store.login.submit'), [
            'email_or_phone' => 'nonexistent@example.com',
            'password' => 'some_password',
        ]);

        $response->assertRedirect(route('store.register', ['email_or_phone' => 'nonexistent@example.com']));
        $response->assertSessionHasInput('email_or_phone', 'nonexistent@example.com');

        $failLog = ActivityLog::where('event', 'login.failed')->first();
        $this->assertNotNull($failLog);
        $this->assertNull($failLog->subject_type);
        $this->assertNull($failLog->subject_id);
        $this->assertEquals(1, $failLog->properties['attempt_count']);
        $this->assertEquals('nonexistent@example.com', $failLog->properties['email_or_phone']);
    }
}
