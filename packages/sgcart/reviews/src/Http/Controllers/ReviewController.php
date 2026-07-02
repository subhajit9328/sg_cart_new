<?php

namespace SGCart\Reviews\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use SGCart\Reviews\Models\Review;
use SGCart\Reviews\Http\Requests\StoreReviewRequest;

class ReviewController extends Controller
{
    /**
     * Store or update a customer review.
     */
    public function store(StoreReviewRequest $request)
    {
        $customerId = auth('customer')->id();
        $productId = $request->product_id;

        // 1. Look for an existing review for editing (a customer can only review a product once)
        $review = Review::with('images')
            ->where('customer_id', $customerId)
            ->where('product_id', $productId)
            ->first();

        $isEdit = !is_null($review);

        $isAdminApprovalRequired = config('reviews.admin_approval', true);
        $defaultStatusName = $isAdminApprovalRequired 
            ? \SGCart\Reviews\Enums\ReviewStatus::PENDING->value 
            : \SGCart\Reviews\Enums\ReviewStatus::APPROVED->value;
        $statusId = \SGCart\Reviews\Models\ReviewStatus::where('name', $defaultStatusName)->value('id') ?? ($isAdminApprovalRequired ? 1 : 2);
        $approvedAt = !$isAdminApprovalRequired ? now() : null;

        if ($isEdit) {
            // Update existing review text details
            $review->update([
                'rating' => $request->rating,
                'comment' => $request->comment,
                'status_id' => $statusId,
                'approved_at' => $approvedAt,
            ]);
            $message = $isAdminApprovalRequired 
                ? 'Your review has been updated and is pending admin approval.' 
                : 'Your review has been updated successfully.';
        } else {
            // Create new review record
            $review = Review::create([
                'customer_id' => $customerId,
                'product_id' => $productId,
                'rating' => $request->rating,
                'comment' => $request->comment,
                'status_id' => $statusId,
                'approved_at' => $approvedAt,
            ]);
            $message = $isAdminApprovalRequired 
                ? 'Your review has been submitted and is pending admin approval.' 
                : 'Your review has been submitted successfully.';
        }

        // 2. Handle existing images reconciliation if editing
        if ($isEdit) {
            $keepImages = $request->input('keep_images', []);
            
            // Reload relation to get current DB state
            $review->load('images');
            foreach ($review->images as $oldImage) {
                // If this image is not explicitly listed in keep_images, delete it
                if (!in_array($oldImage->image_path, $keepImages)) {
                    if (Storage::disk('public')->exists($oldImage->image_path)) {
                        Storage::disk('public')->delete($oldImage->image_path);
                    }
                    $oldImage->delete();
                }
            }
        }

        // 3. Save new images if provided (up to configured max of total images)
        if ($request->hasFile('images')) {
            $maxImages = config('reviews.max_no_image', 5);
            $currentImagesCount = $isEdit ? $review->images()->count() : 0;
            $allowedNewCount = max(0, $maxImages - $currentImagesCount);

            $files = array_slice($request->file('images'), 0, $allowedNewCount);

            foreach ($files as $file) {
                $imagePath = $file->store('reviews', 'public');
                $review->images()->create([
                    'image_path' => $imagePath,
                ]);
            }
        }

        return redirect()->back()->with('success', $message);
    }
}
