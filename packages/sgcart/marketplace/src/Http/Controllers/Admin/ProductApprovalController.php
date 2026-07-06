<?php

namespace SGCart\Marketplace\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Helpers\NotificationHelper;

class ProductApprovalController extends Controller
{
    /**
     * Display a listing of seller products awaiting approval.
     */
    public function index()
    {
        $products = Product::with(['category', 'manufacturer', 'images'])
            ->whereNotNull('seller_id')
            ->where('status', \App\Enums\ProductStatus::PENDING_APPROVAL)
            ->latest()
            ->paginate(15);

        return view('marketplace::admin.products.approvals', compact('products'));
    }

    /**
     * Approve a seller's product (make it active/purchasable).
     */
    public function approve(Product $product)
    {
        $product->update([
            'status' => \App\Enums\ProductStatus::ACTIVE,
            'seller_note' => null,
        ]);

        if ($product->seller_id) {
            try {
                NotificationHelper::sendToSeller(
                    $product->seller_id,
                    'Product Approved',
                    "Your product '{$product->name}' has been approved and is now live on the store.",
                    route('seller.products.edit', $product->ulid),
                    'success',
                    'fa-circle-check'
                );
            } catch (\Exception $e) {
                \Log::error('Failed to notify seller of product approval: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', "Product '{$product->name}' approved and is now active.");
    }

    /**
     * Reject a seller's product (set to inactive/draft).
     */
    public function reject(Request $request, Product $product)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $product->update([
            'status' => \App\Enums\ProductStatus::REJECTED,
            'rejection_reason' => $request->rejection_reason,
        ]);

        if ($product->seller_id) {
            try {
                NotificationHelper::sendToSeller(
                    $product->seller_id,
                    'Product Rejected',
                    "Your product '{$product->name}' has been rejected. Reason: " . $request->rejection_reason,
                    route('seller.products.edit', $product->ulid),
                    'danger',
                    'fa-circle-xmark'
                );
            } catch (\Exception $e) {
                \Log::error('Failed to notify seller of product rejection: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', "Product '{$product->name}' has been rejected with a reason.");
    }
}
