<?php

namespace SGCart\Marketplace\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use SGCart\Marketplace\Models\Seller;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use SGCart\Marketplace\Mail\SellerOtpMail;
use SGCart\Marketplace\Mail\SellerOnboardingCompletedMail;
use App\Helpers\NotificationHelper;

class AuthController extends Controller
{
    /**
     * Show seller login form.
     */
    public function showLogin()
    {
        if (Auth::guard('seller')->check()) {
            return redirect()->route('seller.dashboard');
        }
        return view('marketplace::seller.auth.login');
    }

    /**
     * Handle seller login attempt.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email_or_phone' => ['required', 'string'],
            'password' => ['required'],
        ]);

        $loginInput = $request->input('email_or_phone');
        $isEmail = str_contains($loginInput ?? '', '@') || preg_match('/[a-zA-Z]/', $loginInput ?? '');

        $authCredentials = [
            $isEmail ? 'email' : 'phone_no' => $loginInput,
            'password' => $request->password,
        ];

        $remember = $request->boolean('remember');

        if (Auth::guard('seller')->attempt($authCredentials, $remember)) {
            $request->session()->regenerate();
            
            $seller = Auth::guard('seller')->user();
            if ($seller->status->value === 'pending_onboarding') {
                return redirect()->route('seller.onboarding');
            }
            
            return redirect()->intended(route('seller.dashboard'));
        }

        throw ValidationException::withMessages([
            'email_or_phone' => [__('auth.failed')],
        ]);
    }

    /**
     * Show seller registration form.
     */
    public function showRegister()
    {
        if (Auth::guard('seller')->check()) {
            return redirect()->route('seller.dashboard');
        }
        return view('marketplace::seller.auth.register');
    }

    /**
     * Handle seller registration request.
     */
    public function register(Request $request)
    {
        $emailOrPhone = $request->input('email_or_phone');
        $isEmail = str_contains($emailOrPhone ?? '', '@') || preg_match('/[a-zA-Z]/', $emailOrPhone ?? '');

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];

        $messages = [];

        if ($isEmail) {
            $rules['email_or_phone'] = ['required', 'string', 'email', 'max:255', 'unique:sellers,email'];
            $messages['email_or_phone.unique'] = 'The email address has already been taken by another seller.';
            $messages['email_or_phone.email'] = 'The email address must be a valid email address.';
        } else {
            $rules['email_or_phone'] = [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!str_starts_with($value, '+')) {
                        $fail('The phone number must include a country code starting with +.');
                        return;
                    }
                    $digits = substr($value, 1);
                    if (!ctype_digit($digits)) {
                        $fail('The phone number must contain only digits after the + country code.');
                        return;
                    }
                    if (strlen($digits) < 7 || strlen($digits) > 15) {
                        if (strlen($digits) > 15) {
                            $fail('The phone number must not be more than 15 digits.');
                        } else {
                            $fail('The phone number must be at least 7 digits.');
                        }
                        return;
                    }
                },
                'unique:sellers,phone_no',
            ];
            $messages['email_or_phone.unique'] = 'The phone number has already been taken by another seller.';
        }

        $request->validate($rules, $messages);

        $registrationData = [
            'name' => $request->name,
            'password' => Hash::make($request->password),
            'email' => $isEmail ? $emailOrPhone : null,
            'phone_no' => $isEmail ? null : $emailOrPhone,
        ];

        // Store registration details in session
        session(['seller_registration_data' => $registrationData]);

        // Generate OTP
        $this->generateOtp($registrationData['name'], $emailOrPhone, $isEmail);

        return redirect()->route('seller.otp.verify')->with('success', $isEmail
            ? 'A verification code has been sent to your email.'
            : 'A verification code has been generated.');
    }

    /**
     * Show OTP verification form for sellers.
     */
    public function showOtpVerify()
    {
        if (Auth::guard('seller')->check()) {
            $seller = Auth::guard('seller')->user();
            if ($seller->status->value !== 'pending_onboarding') {
                return redirect()->route('seller.dashboard');
            }
            $isEmail = !empty($seller->email);
            $identifier = $isEmail ? $seller->email : $seller->phone_no;
            $name = $seller->name;
        } elseif (session()->has('seller_registration_data')) {
            $regData = session('seller_registration_data');
            $isEmail = !empty($regData['email']);
            $identifier = $isEmail ? $regData['email'] : $regData['phone_no'];
            $name = $regData['name'];
        } else {
            return redirect()->route('seller.register')->with('error', 'Please register first.');
        }

        $otpKey = "seller_otp_" . $identifier;
        $cooldownKey = "seller_otp_cooldown_" . $identifier;

        $otp = Cache::get($otpKey);
        $cooldownTimestamp = Cache::get($cooldownKey);

        if (!$otp && !$cooldownTimestamp) {
            $this->generateOtp($name, $identifier, $isEmail);
            $cooldownTimestamp = Cache::get($cooldownKey);
            session()->flash('success', $isEmail
                ? 'A new verification code has been sent to your email.'
                : 'A new verification code has been generated.');
        }

        $remainingSeconds = $cooldownTimestamp ? max(0, $cooldownTimestamp - now()->timestamp) : 0;

        return view('marketplace::seller.auth.otp-verify', [
            'identifier' => $identifier,
            'isEmail' => $isEmail,
            'remainingSeconds' => $remainingSeconds,
        ]);
    }

    /**
     * Handle OTP verification.
     */
    public function otpVerify(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        if (Auth::guard('seller')->check()) {
            $seller = Auth::guard('seller')->user();
            if ($seller->status->value !== 'pending_onboarding') {
                return redirect()->route('seller.dashboard');
            }
            $isEmail = !empty($seller->email);
            $identifier = $isEmail ? $seller->email : $seller->phone_no;
        } elseif (session()->has('seller_registration_data')) {
            $regData = session('seller_registration_data');
            $isEmail = !empty($regData['email']);
            $identifier = $isEmail ? $regData['email'] : $regData['phone_no'];
        } else {
            return redirect()->route('seller.register')->with('error', 'Please register first.');
        }

        $otpKey = "seller_otp_" . $identifier;
        $cachedOtp = Cache::get($otpKey);

        // Allow bypass in test mode for phone verification
        $isMobileOtp = !$isEmail;
        $bypass = $isMobileOtp && config('app.test_mode');

        if (!$bypass && (!$cachedOtp || $cachedOtp !== $request->otp)) {
            throw ValidationException::withMessages([
                'otp' => 'The entered OTP is incorrect or has expired.',
            ]);
        }

        // Clear Cache
        Cache::forget($otpKey);
        Cache::forget("seller_otp_cooldown_" . $identifier);

        if (session()->has('seller_registration_data')) {
            $regData = session('seller_registration_data');

            // Create authenticatable Seller
            $seller = Seller::create([
                'name' => $regData['name'],
                'email' => $regData['email'],
                'password' => $regData['password'],
                'phone_no' => $regData['phone_no'],
                'status' => 'pending_onboarding', // Awaiting onboarding form
            ]);

            session()->forget('seller_registration_data');

            Auth::guard('seller')->login($seller);
            $request->session()->regenerate();

            return redirect()->route('seller.onboarding')->with('success', 'Email/Phone verified successfully! Please complete your shop onboarding.');
        }

        // Already logged-in seller verifying
        return redirect()->route('seller.onboarding');
    }

    /**
     * Resend OTP.
     */
    public function otpResend()
    {
        if (Auth::guard('seller')->check()) {
            $seller = Auth::guard('seller')->user();
            if ($seller->status->value !== 'pending_onboarding') {
                return redirect()->route('seller.dashboard');
            }
            $isEmail = !empty($seller->email);
            $identifier = $isEmail ? $seller->email : $seller->phone_no;
            $name = $seller->name;
        } elseif (session()->has('seller_registration_data')) {
            $regData = session('seller_registration_data');
            $isEmail = !empty($regData['email']);
            $identifier = $isEmail ? $regData['email'] : $regData['phone_no'];
            $name = $regData['name'];
        } else {
            return redirect()->route('seller.register')->with('error', 'Please register first.');
        }

        $cooldownKey = "seller_otp_cooldown_" . $identifier;
        $cooldownTimestamp = Cache::get($cooldownKey);

        if ($cooldownTimestamp && $cooldownTimestamp > now()->timestamp) {
            $remaining = $cooldownTimestamp - now()->timestamp;
            $minutes = ceil($remaining / 60);
            return back()->with('error', "Please wait {$minutes} minute(s) before requesting a new OTP.");
        }

        $this->generateOtp($name, $identifier, $isEmail);

        return back()->with('success', $isEmail
            ? 'A new OTP has been sent to your email.'
            : 'A new OTP has been generated.');
    }

    /**
     * Show onboarding page.
     */
    public function showOnboarding()
    {
        $seller = Auth::guard('seller')->user();
        if ($seller->status->value !== 'pending_onboarding') {
            return redirect()->route('seller.dashboard');
        }
        return view('marketplace::seller.onboarding', compact('seller'));
    }

    /**
     * Submit onboarding page.
     */
    public function submitOnboarding(Request $request)
    {
        $seller = Auth::guard('seller')->user();
        if ($seller->status->value !== 'pending_onboarding') {
            return redirect()->route('seller.dashboard');
        }

        $request->validate([
            'shop_name' => ['required', 'string', 'max:255', 'unique:sellers,shop_name,' . $seller->id],
            'shop_description' => ['nullable', 'string', 'max:1000'],
            'address' => ['nullable', 'string', 'max:1000'],
            'phone_no' => ['required', 'string', 'max:20'], // Contact Number
        ]);

        $seller->update([
            'shop_name' => $request->shop_name,
            'shop_slug' => Str::slug($request->shop_name),
            'shop_description' => $request->shop_description,
            'address' => $request->address,
            'phone_no' => $request->phone_no,
            'status' => 'pending', // change status to pending admin approval
        ]);

        NotificationHelper::sendToAdmin(
            'New Shop Onboarded',
            "The seller '{$seller->name}' has completed onboarding for shop '{$request->shop_name}' and is awaiting approval.",
            route('admin.sellers.show', $seller->ulid),
            'product',
            'fa-store'
        );

        if ($seller->email && !str_starts_with($seller->email, 'temp_')) {
            try {
                Mail::to($seller->email)->send(new SellerOnboardingCompletedMail($seller));
            } catch (\Exception $e) {
                Log::error('Failed to send onboarding completed email: ' . $e->getMessage());
            }
        }

        return redirect()->route('seller.dashboard')->with('success', 'Onboarding completed! Please wait for administration review.');
    }

    /**
     * Helper to generate OTP code, cache it, and send the email.
     */
    private function generateOtp(string $name, string $identifier, bool $isEmail): string
    {
        $otpKey = "seller_otp_" . $identifier;
        $cooldownKey = "seller_otp_cooldown_" . $identifier;

        $existingOtp = Cache::get($otpKey);

        if ($existingOtp) {
            $otp = $existingOtp;
        } else {
            $otp = sprintf('%06d', mt_rand(100000, 999999));
            Cache::put($otpKey, $otp, 300); // 5 minutes
            Cache::put($cooldownKey, now()->addMinutes(5)->timestamp, 300); // 5 minutes cooldown
        }

        if (!$existingOtp && $isEmail) {
            try {
                Mail::to($identifier)->send(new SellerOtpMail($name, $otp));
            } catch (\Exception $e) {
                Log::error('Failed to send seller OTP email: ' . $e->getMessage());
            }
        }

        return $otp;
    }

    /**
     * Log the seller out of the application.
     */
    public function logout(Request $request)
    {
        Auth::guard('seller')->logout();
        
        // We only clear seller session attributes to avoid breaking admin session if logged in in the same browser
        $request->session()->forget('guard_seller_val');
        
        return redirect()->route('seller.login')->with('success', 'Logged out successfully.');
    }
}
