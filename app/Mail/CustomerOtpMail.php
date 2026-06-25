<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Customer;

class CustomerOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The customer instance.
     *
     * @var Customer
     */
    public $customer;

    /**
     * The OTP code.
     *
     * @var string
     */
    public $otp;

    /**
     * Create a new message instance.
     */
    public function __construct(Customer $customer, string $otp)
    {
        $this->customer = $customer;
        $this->otp = $otp;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Verify Your Email Address - ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.otp',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
