<?php

namespace App\Http\Controllers\Api;

use App\Actions\CustomerEmailVerifiedAction;
use App\Actions\LogActivity;
use App\Actions\ManageOtp;
use App\Http\Controllers\Controller;
use App\Mail\RegistrationSuccessMail;
use App\Models\Customer;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class CustomerAuthController extends Controller
{
    private const string PENDING_REG_PREFIX = 'api_pending_reg_';
    private const int PENDING_REG_TTL = 900; // 15 minutes

    protected LogActivity $logActivity;
    protected ManageOtp $manageOtp;

    public function __construct(LogActivity $logActivity, ManageOtp $manageOtp)
    {
        $this->logActivity = $logActivity;
        $this->manageOtp = $manageOtp;
    }

    /**
     * Helper to get md5 hash for registration key.
     */
    private function getPendingRegKey(string $emailOrPhone): string
    {
        return self::PENDING_REG_PREFIX . md5($emailOrPhone);
    }

    /**
     * Handle customer registration API request.
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
            $rules['email_or_phone'] = ['required', 'string', 'email', 'max:255', 'unique:customers,email'];
            $messages['email_or_phone.unique'] = 'The email address has already been taken.';
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
                'unique:customers,phone_no',
            ];
            $messages['email_or_phone.unique'] = 'The phone number has already been taken.';
        }

        $request->validate($rules, $messages);

        $registrationData = [
            'name' => $request->name,
            'password' => Hash::make($request->password),
            'email' => $isEmail ? $emailOrPhone : null,
            'phone_no' => $isEmail ? null : $emailOrPhone,
        ];

        // Store registration details in cache for stateless OTP flow
        $cacheKey = $this->getPendingRegKey($emailOrPhone);
        Cache::put($cacheKey, $registrationData, self::PENDING_REG_TTL);

        // Generate OTP using temporary customer model
        $tempCustomer = new Customer([
            'name' => $request->name,
            'email' => $isEmail ? $emailOrPhone : null,
            'phone_no' => $isEmail ? null : $emailOrPhone,
        ]);

        $otp = $this->manageOtp->generate($tempCustomer, ManageOtp::REASON_REGISTRATION, null, false);

        return response()->json([
            'success' => true,
            'message' => 'Verification code generated.',
            'email_or_phone' => $emailOrPhone,
            'is_email' => $isEmail,
            'verification_code' => $otp,
            'otp' => $otp,
        ]);
    }

    /**
     * Handle OTP verification API request.
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email_or_phone' => ['required', 'string'],
            'otp' => ['required_without:verification_code', 'nullable', 'string', 'size:6'],
            'verification_code' => ['required_without:otp', 'nullable', 'string', 'size:6'],
        ]);

        $emailOrPhone = $request->input('email_or_phone');
        $isEmail = str_contains($emailOrPhone ?? '', '@') || preg_match('/[a-zA-Z]/', $emailOrPhone ?? '');
        $cacheKey = $this->getPendingRegKey($emailOrPhone);

        $registrationData = Cache::get($cacheKey);
        $otpInput = $request->input('verification_code') ?? $request->input('otp');

        if ($registrationData) {
            // Registration Verification Flow
            $tempCustomer = new Customer($registrationData);

            if (!$this->manageOtp->verify($tempCustomer, $otpInput)) {
                throw ValidationException::withMessages([
                    'verification_code' => 'The entered verification code is incorrect or has expired.',
                    'otp' => 'The entered OTP is incorrect or has expired.',
                ]);
            }

            // Create the customer account
            $customer = new Customer([
                'name' => $registrationData['name'],
                'password' => $registrationData['password'],
                'email' => $registrationData['email'],
                'phone_no' => $registrationData['phone_no'],
            ]);
            $customer->email_verified_at = $isEmail ? now() : null;
            $customer->phone_verified_at = $isEmail ? null : now();
            $customer->save();

            // Clear cache
            Cache::forget($cacheKey);

            // Log activity
            $this->logActivity->capture(
                description: "Customer registered via App API: {$emailOrPhone}",
                event: 'registration',
                subject: $customer,
                properties: ['email_or_phone' => $emailOrPhone],
                causer: $customer
            );

            $this->logActivity->capture(
                description: $isEmail
                    ? "Customer email verified successfully via App API: {$customer->email}"
                    : "Customer phone verified successfully via App API: {$customer->phone_no}",
                event: 'otp.verified',
                subject: $customer,
                properties: $isEmail ? ['email' => $customer->email] : ['phone_no' => $customer->phone_no],
                causer: $customer
            );

            // Send registration success email for email users
            if ($isEmail && $customer->email) {
                try {
                    Mail::to($customer->email)->send(new RegistrationSuccessMail($customer));
                } catch (Exception $e) {
                    Log::error('Failed to send registration success email via App API: ' . $e->getMessage());
                }
            }

            // Generate JWT Token
            $token = Auth::guard('customer-api')->login($customer);

            return $this->respondWithToken($token, $customer, 'Account created and verified successfully.');
        } else {
            // OTP verification for existing unverified login
            $customer = Customer::where($isEmail ? 'email' : 'phone_no', $emailOrPhone)->first();

            if (!$customer) {
                throw ValidationException::withMessages([
                    'email_or_phone' => 'Verification session expired or customer not found.',
                ]);
            }

            $isCustomerEmailUnverified = $customer->email && !$customer->email_verified_at;
            $isCustomerPhoneUnverified = $customer->phone_no && !$customer->phone_verified_at;

            if (!$isCustomerEmailUnverified && !$isCustomerPhoneUnverified) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account is already verified. Please login directly.',
                ], 422);
            }

            if (!$this->manageOtp->verify($customer, $otpInput)) {
                throw ValidationException::withMessages([
                    'verification_code' => 'The entered verification code is incorrect or has expired.',
                    'otp' => 'The entered OTP is incorrect or has expired.',
                ]);
            }

            // Update verification status
            if ($isCustomerEmailUnverified) {
                app(CustomerEmailVerifiedAction::class)->execute($customer);
            } else {
                $customer->phone_verified_at = now();
                $customer->save();

                $this->logActivity->capture(
                    description: "Customer phone verified successfully via App API: {$customer->phone_no}",
                    event: 'otp.verified',
                    subject: $customer,
                    properties: ['phone_no' => $customer->phone_no],
                    causer: $customer
                );
            }

            // Generate JWT Token
            $token = Auth::guard('customer-api')->login($customer);

            return $this->respondWithToken($token, $customer, 'Verification successful.');
        }
    }

    /**
     * Handle customer login API request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email_or_phone' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $loginInput = $request->input('email_or_phone');
        $isEmail = str_contains($loginInput ?? '', '@') || preg_match('/[a-zA-Z]/', $loginInput ?? '');

        $credentials = [
            $isEmail ? 'email' : 'phone_no' => $loginInput,
            'password' => $request->password,
        ];

        $loginAttemptsKey = 'login_attempts_api_' . md5('customer_' . $loginInput);

        // Check if customer exists
        $customer = Customer::where($isEmail ? 'email' : 'phone_no', $loginInput)->first();
        if (!$customer) {
            $attempts = Cache::increment($loginAttemptsKey);
            Cache::put($loginAttemptsKey, $attempts, 3600);

            $this->logActivity->capture(
                description: "Failed customer login attempt via App API (non-existent user): {$loginInput}",
                event: 'login.failed',
                subject: null,
                properties: ['email_or_phone' => $loginInput, 'attempt_count' => $attempts]
            );

            return response()->json([
                'success' => false,
                'code' => 'USER_NOT_FOUND',
                'message' => 'No account found. Please register to continue.',
                'email_or_phone' => $loginInput,
            ], 404);
        }

        // Attempt login using JWT guard
        if ($token = Auth::guard('customer-api')->attempt($credentials)) {
            // Check verification status
            $isEmailUnverified = $customer->email && !$customer->email_verified_at;
            $isPhoneUnverified = $customer->phone_no && !$customer->phone_verified_at;

            if ($isEmailUnverified || $isPhoneUnverified) {
                // Generate OTP
                $otp = $this->manageOtp->generate($customer, ManageOtp::REASON_AUTO_GENERATE, null, false);

                $this->logActivity->capture(
                    description: "Customer logged in via App API but needs verification: {$loginInput}",
                    event: 'login.verification_required',
                    subject: $customer,
                    properties: ['email_or_phone' => $loginInput],
                    causer: $customer
                );

                return response()->json([
                    'success' => false,
                    'code' => 'VERIFICATION_REQUIRED',
                    'message' => 'Your account is not verified. A verification code has been generated.',
                    'email_or_phone' => $loginInput,
                    'is_email' => (bool)$customer->email,
                    'verification_code' => $otp,
                    'otp' => $otp,
                ], 403);
            }

            Cache::forget($loginAttemptsKey);

            $this->logActivity->capture(
                description: 'Customer logged in via App API',
                event: 'login.success',
                subject: $customer,
                properties: ['email_or_phone' => $loginInput, 'attempt_count' => 0],
                causer: $customer
            );

            return $this->respondWithToken($token, $customer, 'Logged in successfully!');
        }

        $attempts = Cache::increment($loginAttemptsKey);
        Cache::put($loginAttemptsKey, $attempts, 3600);

        $this->logActivity->capture(
            description: "Failed customer login attempt via App API with identifier: {$loginInput}",
            event: 'login.failed',
            subject: $customer,
            properties: ['email_or_phone' => $loginInput, 'attempt_count' => $attempts]
        );

        throw ValidationException::withMessages([
            'email_or_phone' => __('auth.failed'),
        ]);
    }

    /**
     * Resend verification OTP code.
     */
    public function resendOtp(Request $request)
    {
        $request->validate([
            'email_or_phone' => ['required', 'string'],
        ]);

        $emailOrPhone = $request->input('email_or_phone');
        $isEmail = str_contains($emailOrPhone ?? '', '@') || preg_match('/[a-zA-Z]/', $emailOrPhone ?? '');
        $cacheKey = $this->getPendingRegKey($emailOrPhone);

        $registrationData = Cache::get($cacheKey);
        $customer = null;

        if ($registrationData) {
            $customer = new Customer($registrationData);
        } else {
            $customer = Customer::where($isEmail ? 'email' : 'phone_no', $emailOrPhone)->first();
            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'No pending verification or customer record found for this identifier.',
                ], 404);
            }

            $isEmailUnverified = $customer->email && !$customer->email_verified_at;
            $isPhoneUnverified = $customer->phone_no && !$customer->phone_verified_at;

            if (!$isEmailUnverified && !$isPhoneUnverified) {
                return response()->json([
                    'success' => false,
                    'message' => 'This account is already verified.',
                ], 422);
            }
        }

        $cooldownTimestamp = $this->manageOtp->getCooldownTimestamp($customer);

        if ($cooldownTimestamp && $cooldownTimestamp > now()->timestamp) {
            $remaining = $cooldownTimestamp - now()->timestamp;
            $minutes = ceil($remaining / 60);

            return response()->json([
                'success' => false,
                'message' => "Please wait {$minutes} minute(s) before requesting a new OTP.",
                'cooldown_remaining_seconds' => $remaining,
            ], 429);
        }

        $otp = $this->manageOtp->generate($customer, ManageOtp::REASON_RESEND, null, false);

        return response()->json([
            'success' => true,
            'message' => 'A new OTP has been generated.',
            'verification_code' => $otp,
            'otp' => $otp,
        ]);
    }

    /**
     * Get authenticated customer details.
     */
    public function me()
    {
        $customer = Auth::guard('customer-api')->user();

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $customer->id,
                'ulid' => $customer->ulid,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone_no' => $customer->phone_no,
                'profile_picture' => $customer->profile_picture,
                'email_verified_at' => $customer->email_verified_at,
                'phone_verified_at' => $customer->phone_verified_at,
            ],
        ]);
    }

    /**
     * Log out customer from App API.
     */
    public function logout()
    {
        $customer = Auth::guard('customer-api')->user();

        if ($customer) {
            $this->logActivity->capture(
                description: 'Customer logged out via App API',
                event: 'logout',
                subject: $customer,
                causer: $customer
            );
        }

        Auth::guard('customer-api')->logout();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully!',
        ]);
    }

    /**
     * Format response with token.
     */
    protected function respondWithToken($token, Customer $customer, string $message = '')
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('customer-api')->factory()->getTTL() * 60,
            'user' => [
                'id' => $customer->id,
                'ulid' => $customer->ulid,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone_no' => $customer->phone_no,
                'profile_picture' => $customer->profile_picture,
            ],
        ]);
    }
}
