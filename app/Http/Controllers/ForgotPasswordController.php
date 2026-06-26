<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\LogActivity;
use App\Actions\ManageOtp;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function __construct(
        protected ManageOtp $manageOtp,
        protected LogActivity $logActivity
    ) {}

    /**
     * Show the forgot password form.
     */
    public function showForgotPassword(): View
    {
        return view('store.auth.forgot-password');
    }

    /**
     * Handle the submission of the forgot password request.
     */
    public function sendResetOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'email_or_phone' => ['required', 'string'],
        ]);

        $input = $request->input('email_or_phone');
        $isEmail = str_contains($input, '@');

        // Check if customer exists
        $customer = Customer::where($isEmail ? 'email' : 'phone_no', $input)->first();

        if (! $customer) {
            throw ValidationException::withMessages([
                'email_or_phone' => 'No account found with this email or phone number.',
            ]);
        }

        if ($isEmail) {
            // Check if cooldown is active
            $cooldownTimestamp = $this->manageOtp->getCooldownTimestamp($customer);
            if ($cooldownTimestamp && $cooldownTimestamp > now()->timestamp) {
                // Save customer ID in session so they can verify the existing OTP
                session(['forgot_password_customer_id' => $customer->id]);

                return redirect()->route('store.forgot-password.verify')
                    ->with('info', 'A verification code was already sent recently. You can verify it here.');
            }

            // Log the request method
            $this->logActivity->capture(
                description: "Forgot password requested via email for: {$customer->email}",
                event: 'forgot_password.request',
                subject: $customer,
                properties: ['email' => $customer->email, 'method' => 'email'],
                causer: $customer
            );

            // Generate and send OTP using the ManageOtp action
            $this->manageOtp->generate($customer, ManageOtp::REASON_FORGOT_PASSWORD);

            // Save customer ID in session
            session(['forgot_password_customer_id' => $customer->id]);

            return redirect()->route('store.forgot-password.verify')
                ->with('success', 'A verification code has been sent to your email.');
        } else {
            // Log the request method for phone numbers
            $this->logActivity->capture(
                description: "Forgot password requested via phone for: {$customer->phone_no}",
                event: 'forgot_password.request',
                subject: $customer,
                properties: ['phone_no' => $customer->phone_no, 'method' => 'phone'],
                causer: $customer
            );

            // For phone numbers, nothing to do for now (SMS not implemented)
            return back()->with('info', 'Password reset via SMS is currently under development. Please use your email address.');
        }
    }

    /**
     * Show the OTP verification form.
     */
    public function showVerifyOtp(): View|RedirectResponse
    {
        $customerId = session('forgot_password_customer_id');
        if (! $customerId) {
            return redirect()->route('store.forgot-password')
                ->with('error', 'Please enter your email or phone first.');
        }

        $customer = Customer::findOrFail($customerId);
        $cooldownTimestamp = $this->manageOtp->getCooldownTimestamp($customer);
        $remainingSeconds = $cooldownTimestamp ? max(0, $cooldownTimestamp - now()->timestamp) : 0;

        return view('store.auth.forgot-password-verify', [
            'email' => $customer->email,
            'remainingSeconds' => $remainingSeconds,
        ]);
    }

    /**
     * Handle OTP verification.
     */
    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $customerId = session('forgot_password_customer_id');
        if (! $customerId) {
            return redirect()->route('store.forgot-password')
                ->with('error', 'Please enter your email or phone first.');
        }

        $customer = Customer::findOrFail($customerId);

        // Verify OTP using the ManageOtp action (handles cache check, cleanup, and logging)
        if (! $this->manageOtp->verify($customer, $request->otp)) {
            throw ValidationException::withMessages([
                'otp' => 'The entered OTP is incorrect or has expired.',
            ]);
        }

        // Authorize password reset step in the session
        session(['password_reset_authorized_customer_id' => $customer->id]);
        session()->forget('forgot_password_customer_id');

        return redirect()->route('store.forgot-password.reset')
            ->with('success', 'Email verified successfully! You can now choose a new password.');
    }

    /**
     * Resend/regenerate the forgot password OTP.
     */
    public function resendOtp(): RedirectResponse
    {
        $customerId = session('forgot_password_customer_id');
        if (! $customerId) {
            return redirect()->route('store.forgot-password')
                ->with('error', 'Please enter your email or phone first.');
        }

        $customer = Customer::findOrFail($customerId);
        $cooldownTimestamp = $this->manageOtp->getCooldownTimestamp($customer);

        if ($cooldownTimestamp && $cooldownTimestamp > now()->timestamp) {
            $remaining = $cooldownTimestamp - now()->timestamp;
            $minutes = ceil($remaining / 60);

            return back()->with('error', "Please wait {$minutes} minute(s) before requesting a new OTP.");
        }

        $this->manageOtp->generate($customer, ManageOtp::REASON_FORGOT_PASSWORD);

        return back()->with('success', 'A new OTP has been sent to your email.');
    }

    /**
     * Show the reset password form.
     */
    public function showResetPassword(): View|RedirectResponse
    {
        $customerId = session('password_reset_authorized_customer_id');
        if (! $customerId) {
            return redirect()->route('store.forgot-password')
                ->with('error', 'Please verify your OTP code first.');
        }

        return view('store.auth.forgot-password-reset');
    }

    /**
     * Process password reset.
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $customerId = session('password_reset_authorized_customer_id');
        if (! $customerId) {
            return redirect()->route('store.forgot-password')
                ->with('error', 'Please verify your OTP code first.');
        }

        $customer = Customer::findOrFail($customerId);
        $customer->password = Hash::make($request->password);
        $customer->save();

        // Log successful password reset
        $this->logActivity->capture(
            description: "Customer password reset successfully for: {$customer->email}",
            event: 'password_reset.success',
            subject: $customer,
            causer: $customer
        );

        session()->forget('password_reset_authorized_customer_id');

        return redirect()->route('store.login')
            ->with('success', 'Your password has been reset successfully! Please login with your new password.');
    }
}
