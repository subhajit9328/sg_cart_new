<?php

namespace SGCart\SocialShare\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use SGCart\SocialShare\Models\SocialPost;
use SGCart\SocialShare\Models\SocialFollow;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SocialShareController extends Controller
{
    public function index(Request $request)
    {
        $postsQuery = SocialPost::with(['customer', 'product'])->approved()->latest();
        
        // Filter by influencer (optional, supports ID and ULID)
        if ($request->filled('influencer')) {
            $influencer = $request->influencer;
            $postsQuery->where(function ($q) use ($influencer) {
                if (is_numeric($influencer)) {
                    $q->where('customer_id', $influencer);
                } else {
                    $q->whereHas('customer', function ($sq) use ($influencer) {
                        $sq->where('ulid', $influencer);
                    });
                }
            });
        }
        
        $posts = $postsQuery->paginate(config('social-share.per_page', 12));
        
        return view('social-share::feed', compact('posts'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'media' => [
                'required',
                'file',
                'max:102400', // max 100MB for reels
                function ($attribute, $value, $fail) {
                    if (!$value->isValid()) {
                        $fail('The uploaded file is not valid.');
                        return;
                    }
                    
                    $extension = strtolower($value->getClientOriginalExtension());
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'mov', 'avi', 'm4v', 'webm', '3gp'];
                    
                    // Check MIME type
                    $mimeType = $value->getMimeType();
                    $allowedMimes = [
                        'image/jpeg', 'image/png', 'image/gif', 'image/webp',
                        'video/mp4', 'video/quicktime', 'video/x-m4v', 'video/webm', 'video/3gpp', 
                        'video/avi', 'video/msvideo', 'video/x-msvideo', 'application/octet-stream'
                    ];

                    if (!in_array($extension, $allowedExtensions) && !in_array($mimeType, $allowedMimes)) {
                        $fail('The media must be a valid image or video file (jpg, jpeg, png, gif, webp, mp4, mov, avi, webm, m4v).');
                    }
                }
            ],
            'caption' => 'nullable|string|max:1000',
            'order_number' => 'required_without:product_sku|nullable|string',
            'product_sku' => 'required_without:order_number|nullable|string',
            'shop_link' => [
                'nullable',
                'url',
                function ($attribute, $value, $fail) {
                    $appUrl = url('/');
                    $appHost = parse_url($appUrl, PHP_URL_HOST);
                    $linkHost = parse_url($value, PHP_URL_HOST);
                    
                    if (strcasecmp($appHost, $linkHost) !== 0) {
                        $fail('The shop link must be related to this website (base URL must match).');
                    }
                }
            ],
        ], [
            'order_number.required_without' => 'Please provide either an Order ID or a Product SKU.',
            'product_sku.required_without' => 'Please provide either an Order ID or a Product SKU.',
            'media.required' => 'Please upload an image or video.',
            'media.max' => 'The file size must not exceed 100MB.',
            'shop_link.url' => 'Please provide a valid URL for the shop link.',
        ]);

        if ($validator->fails()) {
            return redirect()->route('store.account', 'social-share')
                ->withErrors($validator)
                ->withInput();
        }

        $customerId = auth('customer')->id();
        $productId = null;
        $orderNumber = $request->input('order_number');
        $productSku = $request->input('product_sku');

        // Resolve product
        if ($productSku) {
            $product = Product::where('sku', $productSku)->first();
            if (!$product) {
                return redirect()->route('store.account', 'social-share')
                    ->withErrors(['product_sku' => 'The provided Product SKU was not found in our store.'])
                    ->withInput();
            }
            $productId = $product->id;
        } elseif ($orderNumber) {
            // Find order
            $order = Order::where('order_number', $orderNumber)
                ->where('customer_id', $customerId)
                ->first();

            if (!$order) {
                // Let's also check if they input the Order ULID
                $order = Order::where('ulid', $orderNumber)
                    ->where('customer_id', $customerId)
                    ->first();
            }

            if (!$order) {
                return redirect()->route('store.account', 'social-share')
                    ->withErrors(['order_number' => 'No order found with the provided ID. Please verify your order history.'])
                    ->withInput();
            }

            // Get first product from the order items
            $firstItem = $order->items()->first();
            if ($firstItem) {
                $productId = $firstItem->product_id;
            }
            $orderNumber = $order->order_number; // Normalise to order number
        }

        // Upload media file
        if ($request->hasFile('media')) {
            $file = $request->file('media');
            $mime = $file->getClientMimeType();
            $extension = strtolower($file->getClientOriginalExtension());
            $videoExtensions = ['mp4', 'mov', 'avi', 'm4v', 'webm', '3gp'];
            
            $mediaType = (str_contains($mime, 'video') || in_array($extension, $videoExtensions)) ? 'video' : 'image';
            
            $path = $file->store('social_shares', 'public');
        } else {
            return redirect()->route('store.account', 'social-share')
                ->withErrors(['media' => 'Failed to upload media.'])
                ->withInput();
        }

        // Create post
        SocialPost::create([
            'customer_id' => $customerId,
            'media_path' => $path,
            'media_type' => $mediaType,
            'order_number' => $orderNumber,
            'product_sku' => $productSku,
            'product_id' => $productId,
            'caption' => $request->input('caption'),
            'shop_link' => $request->input('shop_link'),
            'status' => 'pending',
        ]);

        return redirect()->route('store.account', 'social-share')
            ->with('success', 'Your post has been submitted successfully and is awaiting admin approval!');
    }

    public function toggleFollow(Request $request, $influencerId)
    {
        if (!auth('customer')->check()) {
            return response()->json(['success' => false, 'message' => 'Please log in to follow influencers.'], 401);
        }

        $followerId = auth('customer')->id();

        if ($followerId == $influencerId) {
            return response()->json(['success' => false, 'message' => 'You cannot follow yourself.'], 400);
        }

        $existing = SocialFollow::where('follower_id', $followerId)
            ->where('influencer_id', $influencerId)
            ->first();

        if ($existing) {
            $existing->delete();
            $isFollowing = false;
            $msg = 'Unfollowed successfully.';
        } else {
            SocialFollow::create([
                'follower_id' => $followerId,
                'influencer_id' => $influencerId,
            ]);
            $isFollowing = true;
            $msg = 'Followed successfully.';
        }

        return response()->json([
            'success' => true,
            'is_following' => $isFollowing,
            'message' => $msg,
            'followers_count' => SocialFollow::where('influencer_id', $influencerId)->count()
        ]);
    }
}
