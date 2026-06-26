<?php

namespace App\Actions;

use App\Models\Customer;

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
                event: 'email.verified',
                subject: $customer,
                properties: ['email' => $customer->email],
                causer: $customer
            );

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
