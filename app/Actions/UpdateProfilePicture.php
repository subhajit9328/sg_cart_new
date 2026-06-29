<?php

namespace App\Actions;

use App\Models\Customer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UpdateProfilePicture
{
    public function __construct(protected LogActivity $logActivity) {}

    /**
     * Store profile picture, clean up the old file, and log update.
     */
    public function execute(Customer $customer, UploadedFile $file): Customer
    {
        $oldPicture = $customer->profile_picture;

        // Store new image under public disk
        $path = $file->store('profile_pictures', 'public');

        $customer->update([
            'profile_picture' => $path,
        ]);

        if ($oldPicture) {
            Storage::disk('public')->delete($oldPicture);
        }

        // Log security activity
        $this->logActivity->capture(
            description: 'Customer updated profile picture: '.basename($path),
            event: 'customer.profile_picture_updated',
            subject: $customer,
            causer: $customer
        );

        return $customer;
    }
}
