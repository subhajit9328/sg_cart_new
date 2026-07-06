<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\LogActivity;
use App\Actions\ManageOtp;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected ManageOtp $manageOtp,
        protected LogActivity $logActivity
    ) {}

    /**
     * Handle the submission of the forgot password request.
     */
    public function sendResetOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email_or_phone' => ['required', 'string'],
        ]);

        $input = $request->input('email_or_phone');
        $isEmail = str_contains($input, '@') || preg_match('/[a-zA-Z]/', $input);

        // Check if customer exists
        $customer = Customer::where($isEmail ? 'email' : 'phone_no', $input)->first();

        if (! $customer) {
            return $this->errorResponse(
                message: 'No account found with this email or phone number.',
                code: 422,
                errors: [
                    'email_or_phone' => ['No account found with this email or phone number.']
                ]
            );
        }

        // Check if cooldown is active
        $cooldownTimestamp = $this->manageOtp->getCooldownTimestamp($customer);
        if ($cooldownTimestamp && $cooldownTimestamp > now()->timestamp) {
            $remaining = $cooldownTimestamp - now()->timestamp;
            return $this->successResponse(
                data: [
                    'email_or_phone' => $input,
                    'is_email' => $isEmail,
                    'cooldown_remaining_seconds' => $remaining,
                ],
                message: 'A verification code was already sent recently. You can verify it here.'
            );
        }

        // Log the request method
        $this->logActivity->capture(
            description: $isEmail
                ? "Forgot password requested via email for: {$customer->email}"
                : "Forgot password requested via phone for: {$customer->phone_no}",
            event: 'forgot_password.request',
            subject: $customer,
            properties: [
                'email' => $customer->email,
                'phone_no' => $customer->phone_no,
                'method' => $isEmail ? 'email' : 'phone',
            ],
            causer: $customer
        );

        // Save method in session temporarily for ManageOtp logic
        session(['forgot_password_method' => $isEmail ? 'email' : 'phone']);

        // Generate and send OTP using the ManageOtp action
        $otp = $this->manageOtp->generate($customer, ManageOtp::REASON_FORGOT_PASSWORD);

        return $this->successResponse(
            data: [
                'email_or_phone' => $input,
                'is_email' => $isEmail,
                'verification_code' => $otp,
                'otp' => $otp,
            ],
            message: $isEmail
                ? 'A verification code has been sent to your email.'
                : 'A verification code has been generated for your phone number.'
        );
    }

    /**
     * Handle OTP verification.
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email_or_phone' => ['required', 'string'],
            'otp' => ['required_without:verification_code', 'nullable', 'string', 'size:6'],
            'verification_code' => ['required_without:otp', 'nullable', 'string', 'size:6'],
        ]);

        $input = $request->input('email_or_phone');
        $otpInput = $request->input('verification_code') ?? $request->input('otp');
        $isEmail = str_contains($input, '@') || preg_match('/[a-zA-Z]/', $input);

        $customer = Customer::where($isEmail ? 'email' : 'phone_no', $input)->first();

        if (! $customer) {
            return $this->errorResponse(
                message: 'No account found with this email or phone number.',
                code: 422,
                errors: [
                    'email_or_phone' => ['No account found with this email or phone number.']
                ]
            );
        }

        // Set session helper momentarily so ManageOtp handles it correctly if checking phone verification
        session(['forgot_password_method' => $isEmail ? 'email' : 'phone']);

        // Verify OTP using the ManageOtp action
        if (! $this->manageOtp->verify($customer, $otpInput)) {
            return $this->errorResponse(
                message: 'The entered OTP is incorrect or has expired.',
                code: 422,
                errors: [
                    'otp' => ['The entered OTP is incorrect or has expired.'],
                    'verification_code' => ['The entered verification code is incorrect or has expired.']
                ]
            );
        }

        // Authorize password reset step in cache for 15 minutes (stateless verification confirmation)
        Cache::put("api_password_reset_authorized_{$customer->id}", true, 900);

        return $this->successResponse(
            data: [
                'email_or_phone' => $input,
            ],
            message: $isEmail
                ? 'Email verified successfully! You can now choose a new password.'
                : 'Phone number verified successfully! You can now choose a new password.'
        );
    }

    /**
     * Resend/regenerate the forgot password OTP.
     */
    public function resendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email_or_phone' => ['required', 'string'],
        ]);

        $input = $request->input('email_or_phone');
        $isEmail = str_contains($input, '@') || preg_match('/[a-zA-Z]/', $input);

        $customer = Customer::where($isEmail ? 'email' : 'phone_no', $input)->first();

        if (! $customer) {
            return $this->errorResponse(
                message: 'No account found with this email or phone number.',
                code: 422,
                errors: [
                    'email_or_phone' => ['No account found with this email or phone number.']
                ]
            );
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

        session(['forgot_password_method' => $isEmail ? 'email' : 'phone']);
        
        $otp = $this->manageOtp->generate($customer, ManageOtp::REASON_FORGOT_PASSWORD);

        return $this->successResponse(
            data: [
                'email_or_phone' => $input,
                'is_email' => $isEmail,
                'verification_code' => $otp,
                'otp' => $otp,
            ],
            message: $isEmail
                ? 'A new OTP has been sent to your email.'
                : 'A new OTP has been generated for your phone number.'
        );
    }

    /**
     * Process password reset.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email_or_phone' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $input = $request->input('email_or_phone');
        $isEmail = str_contains($input, '@') || preg_match('/[a-zA-Z]/', $input);

        $customer = Customer::where($isEmail ? 'email' : 'phone_no', $input)->first();

        if (! $customer) {
            return $this->errorResponse(
                message: 'No account found with this email or phone number.',
                code: 422,
                errors: [
                    'email_or_phone' => ['No account found with this email or phone number.']
                ]
            );
        }

        if (! Cache::has("api_password_reset_authorized_{$customer->id}")) {
            return $this->errorResponse(
                message: 'Please verify your OTP code first.',
                code: 403
            );
        }

        $customer->password = Hash::make($request->password);
        $customer->save();

        // Log successful password reset
        $this->logActivity->capture(
            description: "Customer password reset successfully for: {$customer->email}",
            event: 'password_reset.success',
            subject: $customer,
            causer: $customer
        );

        Cache::forget("api_password_reset_authorized_{$customer->id}");

        return $this->successResponse(
            message: 'Your password has been reset successfully!'
        );
    }
}
