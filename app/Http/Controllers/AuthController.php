<?php

namespace App\Http\Controllers;

use App\Actions\LogActivity;
use App\Mail\CustomerOtpMail;
use App\Models\Cart;
use App\Models\Customer;
use App\Models\User;
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
        $identifier = $request->input('email');
        $loginAttemptsKey = 'login_attempts_'.md5('admin_'.$identifier);

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            // Check if user has dashboard permission
            if (! Auth::user()->can('view dashboard')) {
                $user = Auth::user();
                app(LogActivity::class)->capture(
                    description: 'Admin login denied: missing dashboard permission',
                    event: 'login.denied',
                    subject: $user,
                    properties: ['email' => $identifier],
                    causer: $user
                );

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                throw ValidationException::withMessages([
                    'email' => 'You do not have permission to access the admin area.',
                ]);
            }

            $user = Auth::user();
            Cache::forget($loginAttemptsKey);
            app(LogActivity::class)->capture(
                description: 'Admin user logged in',
                event: 'login.success',
                subject: $user,
                properties: ['email' => $identifier, 'attempt_count' => 0],
                causer: $user
            );

            return redirect()->intended(route('admin.dashboard'));
        }

        $attempts = Cache::increment($loginAttemptsKey);
        Cache::put($loginAttemptsKey, $attempts, 3600);
        $user = User::where('email', $identifier)->first();

        app(LogActivity::class)->capture(
            description: "Failed admin login attempt with email: {$identifier}",
            event: 'login.failed',
            subject: $user,
            properties: ['email' => $identifier, 'attempt_count' => $attempts]
        );

        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            app(LogActivity::class)->capture(
                description: 'Admin user logged out',
                event: 'logout',
                subject: $user,
                causer: $user
            );
        }

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
        $request->validate([
            'email_or_phone' => ['required', 'string'],
            'password' => ['required'],
        ]);

        $loginInput = $request->input('email_or_phone');
        $isEmail = str_contains($loginInput ?? '', '@');

        $credentials = [
            $isEmail ? 'email' : 'phone_no' => $loginInput,
            'password' => $request->password,
        ];

        $remember = $request->boolean('remember');
        $guestSessionId = $request->session()->getId();
        $loginAttemptsKey = 'login_attempts_'.md5('customer_'.$loginInput);

        // Check if customer exists
        $customer = Customer::where($isEmail ? 'email' : 'phone_no', $loginInput)->first();
        if (! $customer) {
            $attempts = Cache::increment($loginAttemptsKey);
            Cache::put($loginAttemptsKey, $attempts, 3600);

            app(LogActivity::class)->capture(
                description: "Failed customer login attempt (non-existent user): {$loginInput}",
                event: 'login.failed',
                subject: null,
                properties: ['email_or_phone' => $loginInput, 'attempt_count' => $attempts]
            );

            return redirect()->route('store.register', ['email_or_phone' => $loginInput])
                ->withInput(['email_or_phone' => $loginInput])
                ->with('info', 'No account found. Please register to continue.');
        }

        if (Auth::guard('customer')->attempt($credentials, $remember)) {
            $request->session()->regenerate();

            // Merge guest cart with customer cart
            $customerId = Auth::guard('customer')->id();
            Cart::mergeGuestCart($customerId, $guestSessionId);

            Cache::forget($loginAttemptsKey);
            app(LogActivity::class)->capture(
                description: 'Customer logged in',
                event: 'login.success',
                subject: $customer,
                properties: ['email_or_phone' => $loginInput, 'attempt_count' => 0],
                causer: $customer
            );

            return redirect()->intended(route('store.account'))->with('success', 'Logged in successfully!');
        }

        $attempts = Cache::increment($loginAttemptsKey);
        Cache::put($loginAttemptsKey, $attempts, 3600);

        app(LogActivity::class)->capture(
            description: "Failed customer login attempt with identifier: {$loginInput}",
            event: 'login.failed',
            subject: $customer,
            properties: ['email_or_phone' => $loginInput, 'attempt_count' => $attempts]
        );

        throw ValidationException::withMessages([
            'email_or_phone' => __('auth.failed'),
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
        $emailOrPhone = $request->input('email_or_phone');
        $isEmail = str_contains($emailOrPhone ?? '', '@');

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];

        $messages = [];

        if ($isEmail) {
            $rules['email_or_phone'] = ['required', 'string', 'email', 'max:255', 'unique:customers,email'];
            $messages['email_or_phone.unique'] = 'The email address has already been taken.';
            $messages['email_or_phone.email'] = 'The email address must be a valid email address.';
        } else {
            $rules['email_or_phone'] = [
                'required',
                'string',
                'regex:/^\+\d{7,15}$/',
                'unique:customers,phone_no',
            ];
            $messages['email_or_phone.regex'] = 'The phone number must include a country code starting with + followed by the number (e.g. +1234567890).';
            $messages['email_or_phone.unique'] = 'The phone number has already been taken.';
        }

        $request->validate($rules, $messages);

        $guestSessionId = $request->session()->getId();

        $customerData = [
            'name' => $request->name,
            'password' => Hash::make($request->password),
        ];

        if ($isEmail) {
            $customerData['email'] = $emailOrPhone;
            $customerData['phone_no'] = null;
        } else {
            $customerData['phone_no'] = $emailOrPhone;
            $customerData['email'] = null;
        }

        $customer = Customer::create($customerData);

        app(LogActivity::class)->capture(
            description: "Customer registered: {$emailOrPhone}",
            event: 'registration',
            subject: $customer,
            properties: ['email_or_phone' => $emailOrPhone],
            causer: $customer
        );

        if ($isEmail) {
            // Generate and cache OTP (5 minutes valid)
            $otp = sprintf('%06d', mt_rand(100000, 999999));
            $otpKey = "customer_otp_{$customer->id}";
            $cooldownKey = "customer_otp_cooldown_{$customer->id}";

            Cache::put($otpKey, $otp, 300); // 5 minutes
            Cache::put($cooldownKey, now()->addMinutes(5)->timestamp, 300); // 5 minutes cooldown

            // Send OTP mail
            try {
                Mail::to($customer->email)->send(new CustomerOtpMail($customer, $otp));
                app(LogActivity::class)->capture(
                    description: "OTP sent to email: {$customer->email}",
                    event: 'otp.sent',
                    subject: $customer,
                    properties: ['email' => $customer->email, 'otp_sent_time' => now()->toIso8601String()],
                    causer: $customer
                );
            } catch (\Exception $e) {
                Log::error('Failed to send OTP email on registration: '.$e->getMessage());
            }
        }

        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();

        // Merge guest cart with customer cart
        Cart::mergeGuestCart($customer->id, $guestSessionId);

        if ($isEmail) {
            return redirect()->route('store.otp.verify')->with('success', 'Account created successfully! Please verify your email.');
        }

        return redirect()->route('store.account')->with('success', 'Account created successfully! Welcome to SG CART.');
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

                app(LogActivity::class)->capture(
                    description: "Auto-generated and sent OTP to email: {$customer->email}",
                    event: 'otp.sent',
                    subject: $customer,
                    properties: ['email' => $customer->email, 'otp_sent_time' => now()->toIso8601String()],
                    causer: $customer
                );
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
            app(LogActivity::class)->capture(
                description: "Failed OTP verification attempt for email: {$customer->email}",
                event: 'otp.failed',
                subject: $customer,
                properties: ['email' => $customer->email, 'entered_otp' => $request->otp],
                causer: $customer
            );

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

        app(LogActivity::class)->capture(
            description: "Customer email verified successfully: {$customer->email}",
            event: 'otp.verified',
            subject: $customer,
            properties: ['email' => $customer->email],
            causer: $customer
        );

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
            app(LogActivity::class)->capture(
                description: "OTP resent to email: {$customer->email}",
                event: 'otp.sent',
                subject: $customer,
                properties: ['email' => $customer->email, 'otp_sent_time' => now()->toIso8601String()],
                causer: $customer
            );
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
        $customer = Auth::guard('customer')->user();
        if ($customer) {
            app(LogActivity::class)->capture(
                description: 'Customer logged out',
                event: 'logout',
                subject: $customer,
                causer: $customer
            );
        }

        Auth::guard('customer')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('store.home')->with('success', 'Logged out successfully!');
    }
}
