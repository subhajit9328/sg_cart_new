<?php

namespace App\Http\Controllers;

use App\Actions\UpdateProfilePicture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProfilePictureController extends Controller
{
    public function update(Request $request, UpdateProfilePicture $updateProfilePicture)
    {
        $validator = Validator::make($request->all(), [
            'profile_picture' => [
                'required',
                'image',
                'max:2048', // 2 MB max size
                'mimes:jpeg,png,jpg,gif,webp',
            ],
        ], [
            'profile_picture.required' => 'Please select an image file to upload.',
            'profile_picture.image' => 'The file must be an image.',
            'profile_picture.max' => 'The profile picture size must not exceed 2 MB.',
            'profile_picture.mimes' => 'The profile picture must be a file of type: jpeg, png, jpg, gif, webp.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $customer = auth('customer')->user();
        if (! $customer) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated customer.',
            ], 401);
        }

        try {
            $file = $request->file('profile_picture');
            $updatedCustomer = $updateProfilePicture->execute($customer, $file);

            return response()->json([
                'success' => true,
                'message' => 'Profile picture updated successfully.',
                'url' => Storage::url($updatedCustomer->profile_picture),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload profile picture: '.$e->getMessage(),
            ], 500);
        }
    }
}
