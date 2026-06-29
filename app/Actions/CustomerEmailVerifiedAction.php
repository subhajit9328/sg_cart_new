<?php

namespace App\Actions;

use App\Mail\RegistrationSuccessMail;
use App\Models\Customer;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CustomerEmailVerifiedAction
{
    public function __construct(protected LogActivity $logActivity) {}

    /**
     * Update the customer email verification status and send a welcome email if verified.
     */
    public function execute(Customer $customer, bool $verified = true): void
    {
        if ($verified) {
            $customer->email_verified_at = now();
            $customer->save();

            $this->logActivity->capture(
                description: "Customer email verified successfully: {$customer->email}",
                event: 'otp.verified',
                subject: $customer,
                properties: ['email' => $customer->email],
                causer: $customer
            );

            // Send registration success welcome email
            if ($customer->email) {
                try {
                    Mail::to($customer->email)->send(new RegistrationSuccessMail($customer));
                } catch (Exception $e) {
                    Log::error('Failed to send registration success email: '.$e->getMessage());
                }
            }

        } else {

            $customer->email_verified_at = null;
            $customer->save();

            $this->logActivity->capture(
                description: "Customer email verification removed: {$customer->email}",
                event: 'email.verified',
                subject: $customer,
                properties: ['email' => $customer->email],
                causer: $customer
            );
        }
    }
}
