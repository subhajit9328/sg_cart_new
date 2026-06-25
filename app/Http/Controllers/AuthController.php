<?php

namespace App\Http\Controllers;

use App\Mail\CustomerOtpMail;
use App\Mail\RegistrationSuccessMail;
use App\Models\Cart;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle authentication attempt.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            // Check if user has dashboard permission
            if (! Auth::user()->can('view dashboard')) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                throw ValidationException::withMessages([
                    'email' => 'You do not have permission to access the admin area.',
                ]);
            }

            return redirect()->intended(route('admin.dashboard'));
        }

        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Show storefront login form.
     */
    public function showStorefrontLogin()
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('store.account');
        }

        return view('store.auth.login');
    }

    /**
     * Handle storefront login attempt.
     */
    public function storefrontLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');
        $guestSessionId = $request->session()->getId();

        if (Auth::guard('customer')->attempt($credentials, $remember)) {
            $request->session()->regenerate();

            // Merge guest cart with customer cart
            $customerId = Auth::guard('customer')->id();
            Cart::mergeGuestCart($customerId, $guestSessionId);

            return redirect()->intended(route('store.account'))->with('success', 'Logged in successfully!');
        }

        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }

    /**
     * Show storefront registration form.
     */
    public function showStorefrontRegister()
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('store.account');
        }

        return view('store.auth.register');
    }

    /**
     * Handle storefront registration request.
     */
    public function storefrontRegister(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:customers'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $guestSessionId = $request->session()->getId();

        $customer = Customer::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Generate and cache OTP (5 minutes valid)
        $otp = sprintf('%06d', mt_rand(100000, 999999));
        $otpKey = "customer_otp_{$customer->id}";
        $cooldownKey = "customer_otp_cooldown_{$customer->id}";

        Cache::put($otpKey, $otp, 300); // 5 minutes
        Cache::put($cooldownKey, now()->addMinutes(5)->timestamp, 300); // 5 minutes cooldown

        // Send OTP mail
        try {
            Mail::to($customer->email)->send(new RegistrationSuccessMail($customer));
            Mail::to($customer->email)->send(new CustomerOtpMail($customer, $otp));
        } catch (\Exception $e) {
            Log::error('Failed to send OTP email on registration: '.$e->getMessage());
        }

        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();

        // Merge guest cart with customer cart
        Cart::mergeGuestCart($customer->id, $guestSessionId);

        return redirect()->route('store.otp.verify')->with('success', 'Account created successfully! Please verify your email.');
    }

    /**
     * Show the OTP verification form.
     */
    public function showOtpVerify()
    {
        $customer = Auth::guard('customer')->user();
        if ($customer->email_verified_at) {
            return redirect()->route('store.account');
        }

        $otpKey = "customer_otp_{$customer->id}";
        $cooldownKey = "customer_otp_cooldown_{$customer->id}";

        $otp = Cache::get($otpKey);
        $cooldownTimestamp = Cache::get($cooldownKey);

        // If no OTP exists and there is no cooldown, automatically generate and send a new one
        if (! $otp && ! $cooldownTimestamp) {
            $otp = sprintf('%06d', mt_rand(100000, 999999));
            Cache::put($otpKey, $otp, 300);

            $cooldownTimestamp = now()->addMinutes(5)->timestamp;
            Cache::put($cooldownKey, $cooldownTimestamp, 300);

            try {
                Mail::to($customer->email)->send(new CustomerOtpMail($customer, $otp));
                session()->flash('success', 'A new verification code has been sent to your email.');
            } catch (\Exception $e) {
                Log::error('Failed to auto-send OTP email: '.$e->getMessage());
            }
        }

        $remainingSeconds = $cooldownTimestamp ? max(0, $cooldownTimestamp - now()->timestamp) : 0;

        return view('store.auth.otp-verify', [
            'email' => $customer->email,
            'remainingSeconds' => $remainingSeconds,
        ]);
    }

    /**
     * Handle the OTP verification request.
     */
    public function otpVerify(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $customer = Auth::guard('customer')->user();
        if ($customer->email_verified_at) {
            return redirect()->route('store.account');
        }

        $otpKey = "customer_otp_{$customer->id}";
        $cachedOtp = Cache::get($otpKey);

        if (! $cachedOtp || $cachedOtp !== $request->otp) {
            throw ValidationException::withMessages([
                'otp' => 'The entered OTP is incorrect or has expired.',
            ]);
        }

        // Mark customer email as verified
        $customer->email_verified_at = now();
        $customer->save();

        // Clear cached OTP and cooldown
        Cache::forget($otpKey);
        Cache::forget("customer_otp_cooldown_{$customer->id}");



        return redirect()->intended(route('store.account'))->with('success', 'Email verified successfully! Welcome to SG CART.');
    }

    /**
     * Handle resending/regenerating the OTP.
     */
    public function otpResend(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        if ($customer->email_verified_at) {
            return redirect()->route('store.account');
        }

        $cooldownKey = "customer_otp_cooldown_{$customer->id}";
        $cooldownTimestamp = Cache::get($cooldownKey);

        if ($cooldownTimestamp && $cooldownTimestamp > now()->timestamp) {
            $remaining = $cooldownTimestamp - now()->timestamp;
            $minutes = ceil($remaining / 60);

            return back()->with('error', "Please wait {$minutes} minute(s) before requesting a new OTP.");
        }

        // Generate and store new OTP (valid for 5 minutes)
        $otp = sprintf('%06d', mt_rand(100000, 999999));
        $otpKey = "customer_otp_{$customer->id}";
        Cache::put($otpKey, $otp, 300); // 5 minutes

        // Reset cooldown (5 minutes)
        Cache::put($cooldownKey, now()->addMinutes(5)->timestamp, 300);

        // Send OTP mail
        try {
            Mail::to($customer->email)->send(new CustomerOtpMail($customer, $otp));
        } catch (\Exception $e) {
            Log::error('Failed to send OTP email during regeneration: '.$e->getMessage());

            return back()->with('error', 'Failed to send OTP email. Please try again.');
        }

        return back()->with('success', 'A new OTP has been sent to your email.');
    }

    /**
     * Log out storefront user.
     */
    public function storefrontLogout(Request $request)
    {
        Auth::guard('customer')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('store.home')->with('success', 'Logged out successfully!');
    }
}
