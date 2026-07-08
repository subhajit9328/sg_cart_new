<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerRememberMeTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_login_with_remember_me_sets_cookie()
    {
        $customer = Customer::create([
            'name' => 'Remember Me User',
            'email' => 'remember@example.com',
            'phone_no' => '+919876543210',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post(route('store.login.submit'), [
            'email_or_phone' => 'remember@example.com',
            'password' => 'password123',
            'remember' => '1',
        ]);

        $response->assertRedirect(route('store.account'));

        // Assert cookie in response
        $response->assertCookie('remember_customer_identifier', 'remember@example.com');

        $cookie = collect($response->headers->getCookies())
            ->first(fn($c) => $c->getName() === 'remember_customer_identifier');

        $this->assertNotNull($cookie);
        $this->assertTrue($cookie->isHttpOnly());
        $this->assertEquals('lax', $cookie->getSameSite());

        // Check if duration is 14 days (approx 1209600 seconds)
        $expires = $cookie->getExpiresTime();
        $remaining = $expires - time();
        $this->assertTrue($remaining > 1209000 && $remaining <= 1209600);
    }

    public function test_successful_login_without_remember_me_clears_cookie()
    {
        $customer = Customer::create([
            'name' => 'No Remember User',
            'email' => 'noremember@example.com',
            'phone_no' => '+919876543211',
            'password' => Hash::make('password123'),
        ]);

        // Start with a request that has the cookie
        $response = $this->withCookie('remember_customer_identifier', 'noremember@example.com')
            ->post(route('store.login.submit'), [
                'email_or_phone' => 'noremember@example.com',
                'password' => 'password123',
                // remember is NOT checked
            ]);

        $response->assertRedirect(route('store.account'));

        // Assert cookie is expired/cleared
        $response->assertCookieExpired('remember_customer_identifier');
    }

    public function test_login_page_prefills_when_cookie_is_present()
    {
        $response = $this->withCookie('remember_customer_identifier', 'remembered@example.com')
            ->get(route('store.login'));

        $response->assertStatus(200);

        // Verify the hidden input has the prefilled value
        $response->assertSee('value="remembered@example.com"', false);

        // Verify the remember checkbox is checked
        $response->assertSee('name="remember" class="accent-ink w-4 h-4" checked', false);
    }
}
