<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('mail:test {email}', function ($email) {
    $this->info("Sending test registration email to {$email}...");
    
    // Create a dummy Customer
    $customer = new \App\Models\Customer();
    $customer->name = 'Test Customer';
    $customer->email = $email;
    
    try {
        Mail::to($email)->send(new \App\Mail\RegistrationSuccessMail($customer));
        $this->info("Success! Email sent to {$email}.");
    } catch (\Exception $e) {
        $this->error("Failed to send mail: " . $e->getMessage());
    }
})->purpose('Send a test customer registration success email');
