<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StoreController extends Controller
{
    /**
     * Get static products database.
     */
    public static function getProducts()
    {
        return [
            ['id' => 2, 'name' => 'Slim Stretch Chinos', 'cat' => 'Men', 'price' => 59.99, 'old' => null, 'rating' => 4.5, 'badge' => '', 'sizes' => ['28', '30', '32', '34', '36'], 'colors' => ['#374151', '#92400e', '#1f2937'], 'desc' => 'Modern slim-fit chinos made from stretch-cotton blend. A wardrobe essential that transitions from desk to dinner.', 'img' => 'https://images.unsplash.com/photo-1490578474895-699cd4e2cf59?w=600&auto=format&fit=crop&q=80', 'images' => ['https://images.unsplash.com/photo-1490578474895-699cd4e2cf59?w=600&auto=format&fit=crop&q=80', 'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?w=600&auto=format&fit=crop&q=80', 'https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?w=600&auto=format&fit=crop&q=80']],
            ['id' => 3, 'name' => '18K Gold Hoops', 'cat' => 'Accessories', 'price' => 29.99, 'old' => 39.99, 'rating' => 4.9, 'badge' => 'Hot', 'sizes' => [], 'colors' => ['#fcd34d', '#d1d5db'], 'desc' => 'Elegant 18K gold-plated hoop earrings that elevate any outfit — from casual brunch to formal evening.', 'img' => 'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?w=600&auto=format&fit=crop&q=80', 'images' => ['https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?w=600&auto=format&fit=crop&q=80', 'https://images.unsplash.com/photo-1630019852942-f89202989a59?w=600&auto=format&fit=crop&q=80']],
            ['id' => 4, 'name' => 'Leather Runner Sneakers', 'cat' => 'Footwear', 'price' => 89.99, 'old' => 119.99, 'rating' => 4.7, 'badge' => 'New', 'sizes' => ['6', '7', '8', '9', '10', '11'], 'colors' => ['#f9fafb', '#374151', '#7c3aed'], 'desc' => 'Premium genuine leather sneakers built for daily wear. Clean silhouette, cushioned sole, lasting comfort.', 'img' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600&auto=format&fit=crop&q=80', 'images' => ['https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600&auto=format&fit=crop&q=80', 'https://images.unsplash.com/photo-1608231387042-66d1773070a5?w=600&auto=format&fit=crop&q=80']],
            ['id' => 5, 'name' => 'Floral Midi Dress', 'cat' => 'Women', 'price' => 74.99, 'old' => null, 'rating' => 4.6, 'badge' => 'New', 'sizes' => ['XS', 'S', 'M', 'L'], 'colors' => ['#fde68a', '#fecdd3', '#a7f3d0'], 'desc' => 'A garden-ready midi dress with an easy-to-wear silhouette. Perfect for brunches, weddings, and summer events.', 'img' => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=600&auto=format&fit=crop&q=80', 'images' => ['https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=600&auto=format&fit=crop&q=80', 'https://images.unsplash.com/photo-1572804013309-59a88b7e92f1?w=600&auto=format&fit=crop&q=80']],
            ['id' => 6, 'name' => 'Swiss Minimalist Watch', 'cat' => 'Accessories', 'price' => 129.99, 'old' => 159.99, 'rating' => 4.9, 'badge' => 'Hot', 'sizes' => [], 'colors' => ['#f9fafb', '#374151', '#92400e'], 'desc' => 'Swiss-inspired timepiece with sapphire crystal glass and genuine leather strap. The definition of understated luxury.', 'img' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&auto=format&fit=crop&q=80', 'images' => ['https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&auto=format&fit=crop&q=80', 'https://images.unsplash.com/photo-1547996160-81dfa63595aa?w=600&auto=format&fit=crop&q=80']],
            ['id' => 7, 'name' => 'Oxford Button-Down', 'cat' => 'Men', 'price' => 54.99, 'old' => null, 'rating' => 4.4, 'badge' => '', 'sizes' => ['S', 'M', 'L', 'XL', 'XXL'], 'colors' => ['#dbeafe', '#f9fafb', '#fef3c7'], 'desc' => 'A versatile Oxford button-down shirt crafted from 100% Egyptian cotton. The cornerstone of a smart wardrobe.', 'img' => 'https://images.unsplash.com/photo-1598032895397-b9472444bf93?w=600&auto=format&fit=crop&q=80', 'images' => ['https://images.unsplash.com/photo-1598032895397-b9472444bf93?w=600&auto=format&fit=crop&q=80', 'https://images.unsplash.com/photo-1621072156002-e2fcc10d9714?w=600&auto=format&fit=crop&q=80']],
            ['id' => 8, 'name' => 'Full-Grain Crossbody', 'cat' => 'Accessories', 'price' => 94.99, 'old' => 129.99, 'rating' => 4.8, 'badge' => 'Sale', 'sizes' => [], 'colors' => ['#92400e', '#374151', '#f9fafb'], 'desc' => 'Handcrafted full-grain leather crossbody bag with adjustable strap and structured interior pockets.', 'img' => 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=600&auto=format&fit=crop&q=80', 'images' => ['https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=600&auto=format&fit=crop&q=80', 'https://images.unsplash.com/photo-1584917865442-de89df76afd3?w=600&auto=format&fit=crop&q=80']],
            ['id' => 9, 'name' => 'Rose Facial Serum', 'cat' => 'Beauty', 'price' => 44.99, 'old' => 59.99, 'rating' => 4.7, 'badge' => 'New', 'sizes' => ['30ml', '50ml'], 'colors' => [], 'desc' => 'Luxurious rose-infused serum with hyaluronic acid for deep hydration, plumpness, and radiant glow.', 'img' => 'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?w=600&auto=format&fit=crop&q=80', 'images' => ['https://images.unsplash.com/photo-1620916566398-39f1143ab7be?w=600&auto=format&fit=crop&q=80', 'https://images.unsplash.com/photo-1608248597279-f99d160bfcbc?w=600&auto=format&fit=crop&q=80']],
            ['id' => 10, 'name' => 'High-Rise Yoga Leggings', 'cat' => 'Women', 'price' => 39.99, 'old' => null, 'rating' => 4.5, 'badge' => '', 'sizes' => ['XS', 'S', 'M', 'L', 'XL'], 'colors' => ['#1f2937', '#7c3aed', '#059669'], 'desc' => '4-way stretch, high-performance leggings with moisture-wicking fabric. Sculpting fit, squat-proof confidence.', 'img' => 'https://images.unsplash.com/photo-1552902865-b72c031ac5ea?w=600&auto=format&fit=crop&q=80', 'images' => ['https://images.unsplash.com/photo-1552902865-b72c031ac5ea?w=600&auto=format&fit=crop&q=80', 'https://images.unsplash.com/photo-1506152983158-b4a74a01c721?w=600&auto=format&fit=crop&q=80']],
            ['id' => 11, 'name' => 'Suede Chelsea Boots', 'cat' => 'Footwear', 'price' => 109.99, 'old' => 139.99, 'rating' => 4.6, 'badge' => 'Sale', 'sizes' => ['6', '7', '8', '9', '10'], 'colors' => ['#92400e', '#1f2937'], 'desc' => 'Premium suede Chelsea boots with elastic side panels for easy wear. A style investment that works every season.', 'img' => 'https://images.unsplash.com/photo-1595341888016-a392ef81b7de?w=600&auto=format&fit=crop&q=80', 'images' => ['https://images.unsplash.com/photo-1595341888016-a392ef81b7de?w=600&auto=format&fit=crop&q=80', 'https://images.unsplash.com/photo-1520639888713-7851133b1ed0?w=600&auto=format&fit=crop&q=80']],
            ['id' => 12, 'name' => 'Silk Pocket Squares', 'cat' => 'Men', 'price' => 24.99, 'old' => null, 'rating' => 4.3, 'badge' => '', 'sizes' => [], 'colors' => ['#7c3aed', '#ef4444', '#1f2937'], 'desc' => 'A curated set of 3 pure silk pocket squares to elevate any formal or semi-formal look instantly.', 'img' => 'https://images.unsplash.com/photo-1576566588028-4147f3842f27?w=600&auto=format&fit=crop&q=80', 'images' => ['https://images.unsplash.com/photo-1576566588028-4147f3842f27?w=600&auto=format&fit=crop&q=80']],
        ];
    }

    /**
     * Show storefront homepage.
     */
    public function home()
    {
        $products = collect(self::getProducts())->take(4);
        $categories = ['Women', 'Men', 'Accessories', 'Footwear', 'Beauty'];
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

        // Rating filter
        if ($request->filled('rating')) {
            $minRating = (float) $request->input('rating');
            $products = $products->filter(fn($p) => $p['rating'] >= $minRating);
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
        } elseif ($sort === 'rating') {
            $products = $products->sortByDesc('rating');
        }

        $allCategories = ['Women', 'Men', 'Accessories', 'Footwear', 'Beauty'];

        return view('store.shop', [
            'products' => $products,
            'allCategories' => $allCategories,
            'selectedCategories' => (array) $request->input('category', []),
            'selectedRating' => $request->input('rating'),
            'selectedPriceMax' => $request->input('price_max', 160),
            'selectedSort' => $sort,
            'searchQuery' => $request->input('search')
        ]);
    }

    /**
     * Product details page.
     */
    public function product($id)
    {
        $products = collect(self::getProducts());
        $product = $products->firstWhere('id', (int) $id);

        if (!$product) {
            abort(404);
        }

        // Fetch related products (same category)
        $related = $products->where('cat', $product['cat'])
            ->where('id', '!=', $product['id'])
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

        $discount = session()->get('coupon_discount', 0);
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

        return redirect()->route('store.cart')->with('success', 'Item removed from cart.');
    }

    /**
     * Apply coupon code.
     */
    public function applyCoupon(Request $request)
    {
        $request->validate(['code' => 'required|string']);
        $code = strtoupper($request->code);

        if ($code === 'SGCART20') {
            session()->put('coupon_discount', 10.00); // Flat $10 off
            return redirect()->route('store.cart')->with('success', 'Coupon code SGCART20 applied! $10.00 off.');
        }

        return redirect()->route('store.cart')->with('error', 'Invalid coupon code.');
    }

    /**
     * Checkout page.
     */
    public function checkout()
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('store.shop')->with('error', 'Your cart is empty.');
        }

        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        $discount = session()->get('coupon_discount', 0);
        $tax = $subtotal * 0.08;
        $total = max(0, $subtotal + $tax - $discount);

        return view('store.checkout', compact('cart', 'subtotal', 'tax', 'discount', 'total'));
    }

    /**
     * Place order.
     */
    public function placeOrder(Request $request)
    {
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
        $discount = session()->get('coupon_discount', 0);
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
    public function account()
    {
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

        return view('store.account', compact('orders', 'wishlist'));
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
}
