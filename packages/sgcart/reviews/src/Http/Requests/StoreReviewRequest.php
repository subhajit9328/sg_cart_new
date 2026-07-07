<?php

namespace SGCart\Reviews\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Order;

class StoreReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $customerId = auth('customer')->id();
        $productId = $this->input('product_id');

        if (!$customerId || !$productId) {
            return false;
        }

        // Verify that the customer has at least one delivered order containing this product
        return Order::where('customer_id', $customerId)
            ->where('status', 'Delivered')
            ->whereHas('items', function ($q) use ($productId) {
                $q->where('product_id', $productId);
            })
            ->exists();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $maxImages = config('reviews.max_no_image', 5);

        return [
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'images' => 'nullable|array|max:' . $maxImages,
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'keep_images' => 'nullable|array',
            'keep_images.*' => 'nullable|string',
        ];
    }
}
