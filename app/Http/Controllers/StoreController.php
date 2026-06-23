<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StoreController extends Controller
{
    /**
     * Get products dynamically from database.
     */
    public static function getProducts()
    {
        $relations = ['category.parent', 'images'];
        if (class_exists(\SGCart\ProductVariants\Models\ProductVariant::class)) {
            $relations[] = 'variants.color';
            $relations[] = 'variants.size';
        }
        return \App\Models\Product::with($relations)->where('status', 'active')->get()->map(function ($p) {
            $catName = 'Fashion';
            if ($p->category) {
                $topParent = $p->category;
                while ($topParent->parent) {
                    $topParent = $topParent->parent;
                }
                $name = $topParent->name;
                if (str_contains($name, 'Men')) $catName = 'Men';
                elseif (str_contains($name, 'Women')) $catName = 'Women';
                elseif (str_contains($name, 'Kids')) $catName = 'Kids';
                elseif (str_contains($name, 'Footwear')) $catName = 'Footwear';
                elseif (str_contains($name, 'Accessory') || str_contains($name, 'Accessories')) $catName = 'Accessories';
            }

            // Determine sizes and colors dynamically from variants or database column values if present
            $sizes = [];
            $colors = [];
            if ($p->variants && $p->variants->isNotEmpty()) {
                $colors = $p->variants->where('is_active', true)->map(fn($v) => $v->color?->hex_code)->filter()->unique()->values()->toArray();
                $sizes = $p->variants->where('is_active', true)->map(fn($v) => $v->size?->code)->filter()->unique()->values()->toArray();
            } else {
                if (isset($p->sizes) && !empty($p->sizes)) {
                    $sizes = is_array($p->sizes) ? $p->sizes : array_filter(array_map('trim', explode(',', $p->sizes)));
                }
                if (isset($p->colors) && !empty($p->colors)) {
                    $colors = is_array($p->colors) ? $p->colors : array_filter(array_map('trim', explode(',', $p->colors)));
                }
            }

            return [
                'id' => $p->id,
                'slug' => $p->slug,
                'name' => $p->name,
                'cat' => $catName,
                'price' => $p->sale_price ?? $p->price,
                'old' => $p->sale_price ? $p->price : null,
                'rating' => 4.5,
                'badge' => $p->sale_price ? 'Sale' : '',
                'sizes' => $sizes,
                'colors' => $colors,
                'desc' => $p->short_description ?? $p->description ?? 'No description available.',
                'description' => $p->description ?? 'No detailed description available.',
                'weight' => $p->weight,
                'dimensions' => $p->dimensions,
                'sku' => $p->sku,
                'manufacturer' => $p->manufacturer ? $p->manufacturer->name : null,
                'img' => $p->image ? \Illuminate\Support\Facades\Storage::url($p->image) : asset('images/no-image.svg'),
                'images' => $p->images->isNotEmpty()
                    ? $p->images->sortByDesc('is_default')->map(fn($img) => \Illuminate\Support\Facades\Storage::url($img->image_path))->values()->toArray()
                    : [asset('images/no-image.svg')]
            ];
        })->toArray();
    }

    /**
     * Show storefront homepage.
     */
    public function home()
    {
        $products = collect(self::getProducts())->take(4);
        $categories = collect(self::getProducts())->pluck('cat')->unique()->values()->toArray();
        if (empty($categories)) {
            $categories = ['Women', 'Men', 'Kids', 'Accessories', 'Footwear', 'Beauty'];
        }
        return view('welcome', compact('products', 'categories'));
    }

    /**
     * Shop catalogue page.
     */
    public function shop(Request $request)
    {
        $products = collect(self::getProducts());

        // Category filter
        if ($request->filled('category')) {
            $categories = (array) $request->input('category');
            $products = $products->filter(fn($p) => in_array($p['cat'], $categories));
        }

        // Price filter
        if ($request->filled('price_max')) {
            $maxPrice = (float) $request->input('price_max');
            $products = $products->filter(fn($p) => $p['price'] <= $maxPrice);
        }

        // Search filter
        if ($request->filled('search')) {
            $search = strtolower($request->input('search'));
            $products = $products->filter(fn($p) => 
                str_contains(strtolower($p['name']), $search) || 
                str_contains(strtolower($p['desc']), $search)
            );
        }

        // Sorting
        $sort = $request->input('sort', 'default');
        if ($sort === 'price_asc') {
            $products = $products->sortBy('price');
        } elseif ($sort === 'price_desc') {
            $products = $products->sortByDesc('price');
        }

        $allCategories = collect(self::getProducts())->pluck('cat')->unique()->values()->toArray();
        if (empty($allCategories)) {
            $allCategories = ['Women', 'Men', 'Accessories', 'Footwear', 'Beauty'];
        }

        return view('store.shop', [
            'products' => $products,
            'allCategories' => $allCategories,
            'selectedCategories' => (array) $request->input('category', []),
            'selectedPriceMax' => $request->input('price_max', 10000),
            'selectedSort' => $sort,
            'searchQuery' => $request->input('search')
        ]);
    }

    /**
     * Product details page.
     */
    public function product($slug)
    {
        $products = collect(self::getProducts());
        $product = $products->firstWhere('slug', $slug);

        if (!$product) {
            abort(404);
        }

        // Fetch related products (same category)
        $related = $products->where('cat', $product['cat'])
            ->where('slug', '!=', $product['slug'])
            ->take(4);

        return view('store.product', compact('product', 'related'));
    }

    /**
     * View cart page.
     */
    public function cart()
    {
        $cart = session()->get('cart', []);
        $products = collect(self::getProducts());

        $subtotal = 0;
        foreach ($cart as $key => $item) {
            $subtotal += $item['price'] * $item['quantity'];
            
            // Resolve category dynamically
            $prod = $products->firstWhere('id', $item['id']);
            $cart[$key]['cat'] = $prod ? $prod['cat'] : 'Fashion';
        }

        $discount = 0;
        if (app()->bound('coupon.calculator')) {
            $discount = app('coupon.calculator')->calculate(session('coupon_code'), $subtotal);
        }
        $tax = $subtotal * 0.08;
        $total = max(0, $subtotal + $tax - $discount);

        return view('store.cart', compact('cart', 'subtotal', 'tax', 'discount', 'total'));
    }

    /**
     * Add product to cart.
     */
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
            'size' => 'nullable|string',
            'color' => 'nullable|string',
        ]);

        $productId = (int) $request->product_id;
        $quantity = (int) $request->quantity;
        $size = $request->size;
        $color = $request->color;

        $products = collect(self::getProducts());
        $product = $products->firstWhere('id', $productId);

        if (!$product) {
            return redirect()->back()->with('error', 'Product not found.');
        }

        $cart = session()->get('cart', []);
        
        // Generate unique key for cart item based on details (size & color)
        $cartKey = $productId . '_' . ($size ?? '') . '_' . ($color ?? '');

        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] += $quantity;
        } else {
            $cart[$cartKey] = [
                'id' => $product['id'],
                'slug' => $product['slug'],
                'name' => $product['name'],
                'price' => $product['price'],
                'img' => $product['img'],
                'quantity' => $quantity,
                'size' => $size,
                'color' => $color,
            ];
        }

        session()->put('cart', $cart);

        return redirect()->route('store.cart')->with('success', 'Product added to cart!');
    }

    /**
     * Update cart quantities.
     */
    public function updateCart(Request $request)
    {
        $cart = session()->get('cart', []);
        $quantities = $request->input('quantities', []);

        foreach ($quantities as $key => $qty) {
            if (isset($cart[$key])) {
                if ((int)$qty <= 0) {
                    unset($cart[$key]);
                } else {
                    $cart[$key]['quantity'] = (int)$qty;
                }
            }
        }

        session()->put('cart', $cart);

        if (empty($cart)) {
            session()->forget('coupon_code');
            session()->forget('coupon_discount');
        }

        return redirect()->route('store.cart')->with('success', 'Cart updated successfully.');
    }

    /**
     * Remove item from cart.
     */
    public function removeFromCart($key)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$key])) {
            unset($cart[$key]);
            session()->put('cart', $cart);
        }

        if (empty($cart)) {
            session()->forget('coupon_code');
            session()->forget('coupon_discount');
        }

        return redirect()->route('store.cart')->with('success', 'Item removed from cart.');
    }



    /**
     * Checkout page.
     */
    public function checkout()
    {
        if (!auth('customer')->check()) {
            return redirect()->route('store.login')->with('error', 'Please log in to proceed to checkout.');
        }

        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('store.shop')->with('error', 'Your cart is empty.');
        }

        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        $discount = 0;
        if (app()->bound('coupon.calculator')) {
            $discount = app('coupon.calculator')->calculate(session('coupon_code'), $subtotal);
        }
        $tax = $subtotal * 0.08;
        $total = max(0, $subtotal + $tax - $discount);

        return view('store.checkout', compact('cart', 'subtotal', 'tax', 'discount', 'total'));
    }

    /**
     * Place order.
     */
    public function placeOrder(Request $request)
    {
        if (!auth('customer')->check()) {
            return redirect()->route('store.login')->with('error', 'Please log in to proceed to checkout.');
        }

        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'zip' => 'required|string|max:20',
            'card_name' => 'required|string|max:255',
            'card_num' => 'required|string|max:19',
            'card_expiry' => 'required|string|max:5',
            'card_cvv' => 'required|string|max:4',
        ]);

        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('store.shop')->with('error', 'Your cart is empty.');
        }

        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        $discount = 0;
        if (app()->bound('coupon.calculator')) {
            $discount = app('coupon.calculator')->calculate(session('coupon_code'), $subtotal);
        }
        $tax = $subtotal * 0.08;
        $total = max(0, $subtotal + $tax - $discount);

        // Generate dynamic order data
        $orderId = 'SGCART-' . date('Ymd') . '-' . rand(1000, 9999);
        $orderDate = date('M d, Y');

        $order = [
            'id' => $orderId,
            'date' => $orderDate,
            'items_count' => count($cart),
            'amount' => $total,
            'status' => 'Processing',
        ];

        // Store order in session history
        $ordersHistory = session()->get('orders_history', []);
        array_unshift($ordersHistory, $order);
        session()->put('orders_history', $ordersHistory);

        // Clear Cart
        session()->forget('cart');
        session()->forget('coupon_code');
        session()->forget('coupon_discount');

        return redirect()->route('store.success', ['order_id' => $orderId]);
    }

    /**
     * Order success page.
     */
    public function success(Request $request)
    {
        $orderId = $request->input('order_id', 'SGCART-MOCK-ORDER');
        return view('store.success', compact('orderId'));
    }

    /**
     * Account / Profile page.
     */
    public function account($tab = 'orders')
    {
        if (!auth('customer')->check()) {
            return redirect()->route('store.login')->with('error', 'Please log in to access your account.');
        }

        $validTabs = ['orders', 'profile', 'address', 'wishlist'];
        $activeTab = in_array($tab, $validTabs) ? $tab : 'orders';

        $orders = session()->get('orders_history', [
            [
                'id' => 'SGCART-2026-8821',
                'date' => 'May 15, 2026',
                'items_count' => 3,
                'amount' => 127.50,
                'status' => 'Delivered'
            ],
            [
                'id' => 'SGCART-2026-8907',
                'date' => 'Jun 02, 2026',
                'items_count' => 1,
                'amount' => 59.99,
                'status' => 'In Transit'
            ]
        ]);

        $wishlistIds = session()->get('wishlist', [3, 5, 6]);
        $allProducts = self::getProducts();
        $wishlist = array_filter($allProducts, fn($p) => in_array($p['id'], $wishlistIds));

        return view('store.account', compact('orders', 'wishlist', 'activeTab'));
    }

    /**
     * Update user profile details.
     */
    public function updateProfile(Request $request)
    {
        if (!auth('customer')->check()) {
            return redirect()->route('store.login')->with('error', 'Please log in to update your profile.');
        }

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
        ]);

        $customer = auth('customer')->user();
        $customer->name = trim($request->first_name . ' ' . $request->last_name);
        $customer->save();

        return redirect()->route('store.account', 'profile')->with('success', 'Profile details updated successfully!');
    }

    /**
     * Toggle wishlist item.
     */
    public function toggleWishlist(Request $request)
    {
        $productId = (int) $request->input('product_id');
        $wishlist = session()->get('wishlist', [3, 5, 6]);

        if (in_array($productId, $wishlist)) {
            $wishlist = array_values(array_diff($wishlist, [$productId]));
            $msg = 'Removed from wishlist.';
        } else {
            $wishlist[] = $productId;
            $msg = 'Added to wishlist.';
        }

        session()->put('wishlist', $wishlist);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => $msg, 'wishlist' => $wishlist]);
        }

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Live search API.
     */
    public function searchLive(Request $request)
    {
        $query = strtolower($request->input('q', ''));
        if (empty($query)) {
            return response()->json([]);
        }

        $products = collect(self::getProducts());
        $results = $products->filter(fn($p) => 
            str_contains(strtolower($p['name']), $query) || 
            str_contains(strtolower($p['desc']), $query) ||
            str_contains(strtolower($p['cat']), $query)
        )->map(fn($p) => [
            'id' => $p['id'],
            'name' => $p['name'],
            'price' => (float) $p['price'],
            'cat' => $p['cat'],
            'img' => $p['img'],
            'url' => route('store.product', $p['slug'])
        ])->values()->take(5);

        return response()->json($results);
    }
}
