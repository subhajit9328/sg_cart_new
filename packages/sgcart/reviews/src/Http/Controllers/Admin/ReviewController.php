<?php

namespace SGCart\Reviews\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use SGCart\Reviews\Models\Review;
use SGCart\Reviews\Models\ReviewStatus;

class ReviewController extends Controller
{
    /**
     * Display a listing of reviews.
     */
    public function index(Request $request)
    {
        $query = Review::with(['customer', 'product', 'status:id,name', 'images']);

        // Filter by search term
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('customer', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('product', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%");
                })->orWhere('comment', 'like', "%{$search}%");
            });
        }

        // Filter by status ID
        if ($request->filled('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        // Paginate reviews
        $reviews = $query->latest()->paginate(15);

        // Fetch review statistics
        $totalReviews = Review::count();
        $pendingReviewsCount = Review::where('status_id', 1)->count();
        $approvedReviewsCount = Review::where('status_id', 2)->count();
        $rejectedReviewsCount = Review::where('status_id', 3)->count();
        $averageRating = Review::where('status_id', 2)->avg('rating') ?: 0;

        $statuses = ReviewStatus::all();

        return view('reviews::admin.index', compact(
            'reviews',
            'totalReviews',
            'pendingReviewsCount',
            'approvedReviewsCount',
            'rejectedReviewsCount',
            'averageRating',
            'statuses'
        ));
    }

    /**
     * Approve a review.
     */
    public function approve(Review $review)
    {
        $review->update([
            'status_id' => 2, // Approved
            'approved_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Review has been approved and published.');
    }

    /**
     * Reject a review.
     */
    public function reject(Review $review)
    {
        $review->update([
            'status_id' => 3, // Rejected
            'approved_at' => null,
        ]);

        return redirect()->back()->with('success', 'Review has been rejected.');
    }

    /**
     * Delete a review.
     */
    public function destroy(Review $review)
    {
        // Delete all attached images from disk
        foreach ($review->images as $img) {
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($img->image_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($img->image_path);
            }
            $img->delete();
        }

        // Delete the review
        $review->delete();

        return redirect()->back()->with('success', 'Review has been deleted successfully.');
    }

    /**
     * Perform bulk action on multiple reviews.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'bulk_ids' => 'required|array',
            'bulk_ids.*' => 'exists:reviews,id',
            'action' => 'required|in:approve,reject,delete',
        ]);

        $ids = $request->bulk_ids;
        $action = $request->action;

        if ($action === 'approve') {
            Review::whereIn('id', $ids)->update([
                'status_id' => 2, // Approved
                'approved_at' => now(),
            ]);
            $message = 'Selected reviews have been approved and published.';
        } elseif ($action === 'reject') {
            Review::whereIn('id', $ids)->update([
                'status_id' => 3, // Rejected
                'approved_at' => null,
            ]);
            $message = 'Selected reviews have been rejected.';
        } elseif ($action === 'delete') {
            $reviews = Review::with('images')->whereIn('id', $ids)->get();
            foreach ($reviews as $review) {
                // Delete all attached images from disk
                foreach ($review->images as $img) {
                    if (\Illuminate\Support\Facades\Storage::disk('public')->exists($img->image_path)) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($img->image_path);
                    }
                    $img->delete();
                }
                // Delete the review
                $review->delete();
            }
            $message = 'Selected reviews have been deleted successfully.';
        }

        return redirect()->back()->with('success', $message);
    }
}
