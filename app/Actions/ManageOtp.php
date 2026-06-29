<?php

namespace App\Actions;

use App\Mail\CustomerOtpMail;
use App\Models\Customer;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ManageOtp
{
    // Public constants for OTP generation reasons
    public const string REASON_REGISTRATION = 'registration';

    public const string REASON_AUTO_GENERATE = 'auto_generate';

    public const string REASON_RESEND = 'resend';

    public const string REASON_PROFILE_UPDATE = 'profile_update';

    public const string REASON_FORGOT_PASSWORD = 'forgot_password';

    private const int OTP_LIFESPAN = 5; // 5 minutes

    public function __construct(protected LogActivity $logActivity) {}

    /**
     * Generate a new OTP cache key.
     */
    private function getOtpCacheKey(Customer $customer): string
    {
        return "customer_otp_{$customer->id}";
    }

    /**
     * Generate a new OTP cooldown cache key.
     */
    private function getCooldownCacheKey(Customer $customer): string
    {
        return "customer_otp_cooldown_{$customer->id}";
    }

    /**
     * Get the active OTP from cache for the customer.
     */
    public function getOtp(Customer $customer): ?string
    {
        return Cache::get($this->getOtpCacheKey($customer));
    }

    /**
     * Get the OTP resend cooldown timestamp from cache for the customer.
     */
    public function getCooldownTimestamp(Customer $customer): ?int
    {
        return Cache::get($this->getCooldownCacheKey($customer));
    }

    /**
     * Generate a new OTP code, cache it, send the email (if email exists), and log the event.
     *
     * @param  string  $reason  - logs the otp generation reason with default description
     * @param  string|null  $customDescription  - Pass any custom description you wants to log
     */
    public function generate(Customer $customer, string $reason = self::REASON_REGISTRATION, ?string $customDescription = null): string
    {
        $otp = sprintf('%06d', mt_rand(100000, 999999));
        $otpKey = $this->getOtpCacheKey($customer);
        $cooldownKey = $this->getCooldownCacheKey($customer);

        Cache::put($otpKey, $otp, self::OTP_LIFESPAN * 60); // 5 minutes valid
        Cache::put($cooldownKey, now()->addMinutes(self::OTP_LIFESPAN)->timestamp, self::OTP_LIFESPAN * 60); // 5 minutes cooldown

        // Format standard descriptions based on reason if customDescription is not provided
        if (! $customDescription) {
            $customDescription = match ($reason) {
                self::REASON_AUTO_GENERATE => $customer->email
                    ? "Auto-generated and sent OTP to email: {$customer->email}"
                    : "Auto-generated OTP for phone: {$customer->phone_no}",
                self::REASON_RESEND => $customer->email
                    ? "OTP resent to email: {$customer->email}"
                    : "OTP resent to phone: {$customer->phone_no}",
                self::REASON_PROFILE_UPDATE => $customer->email
                    ? "OTP sent to email: {$customer->email} on profile email update"
                    : "OTP generated for phone: {$customer->phone_no} on profile phone update",
                self::REASON_FORGOT_PASSWORD => $customer->email
                    ? "Forgot password OTP sent to email: {$customer->email}"
                    : "Forgot password OTP generated for phone: {$customer->phone_no}",
                default => $customer->email
                    ? "OTP sent to email: {$customer->email}"
                    : "OTP generated for phone: {$customer->phone_no}",
            };
        }

        try {
            $sendEmail = false;
            if ($customer->email) {
                if ($reason === self::REASON_FORGOT_PASSWORD) {
                    $sendEmail = session('forgot_password_method', 'email') === 'email';
                } else {
                    $sendEmail = ! $customer->email_verified_at;
                }
            }

            if ($sendEmail) {
                Mail::to($customer->email)->send(new CustomerOtpMail($customer, $otp, $reason));
            }

            $this->logActivity->capture(
                description: $customDescription,
                event: 'otp.sent',
                subject: $customer,
                properties: [
                    'email' => $customer->email,
                    'phone_no' => $customer->phone_no,
                    'reason' => $reason,
                    'otp_sent_time' => now()->toIso8601String(),
                ],
                causer: $customer
            );
        } catch (Exception $e) {
            Log::error("Failed to send OTP ({$reason}): ".$e->getMessage());
        }

        return $otp;
    }

    /**
     * Verify the entered OTP, update customer verified status, clear cache, and log events.
     */
    public function verify(Customer $customer, string $enteredOtp): bool
    {
        $otpKey = $this->getOtpCacheKey($customer);

        // Check if we are verifying a mobile number and test mode is enabled
        $isMobileOtp = ! $customer->email || (session('forgot_password_method') === 'phone') || ($customer->phone_no && ! $customer->phone_verified_at);

        if ($isMobileOtp && config('app.test_mode')) {
            // Clear cache keys
            Cache::forget($otpKey);
            Cache::forget($this->getCooldownCacheKey($customer));

            $this->logActivity->capture(
                description: "Bypassed OTP verification for mobile no: {$customer->phone_no} (Test Mode enabled)",
                event: 'otp.verified.bypass',
                subject: $customer,
                properties: ['phone_no' => $customer->phone_no, 'entered_otp' => $enteredOtp],
                causer: $customer
            );

            return true;
        }

        $cachedOtp = Cache::get($otpKey);

        if (! $cachedOtp || $cachedOtp !== $enteredOtp) {
            $identifier = $customer->email ?? $customer->phone_no;
            $this->logActivity->capture(
                description: "Failed OTP verification attempt for: {$identifier}",
                event: 'otp.failed',
                subject: $customer,
                properties: [
                    'email' => $customer->email,
                    'phone_no' => $customer->phone_no,
                    'entered_otp' => $enteredOtp,
                ],
                causer: $customer
            );

            return false;
        }

        // Clear cache keys
        Cache::forget($otpKey);
        Cache::forget($this->getCooldownCacheKey($customer));

        return true;
    }
}
