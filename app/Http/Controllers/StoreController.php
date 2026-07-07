<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Actions\LogActivity;
use App\Actions\ManageOtp;
use App\Actions\ResolveRelatedProducts;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use SGCart\ProductVariants\Models\ProductVariant;

class StoreController extends Controller
{
    /**
     * Get products dynamically from database.
     */
    public static function getProducts()
    {
        $relations = ['category.parent', 'images', 'searchTerms'];
        if (class_exists(ProductVariant::class)) {
            $relations[] = 'variants.color';
            $relations[] = 'variants.size';
        }

        $query = Product::with($relations)->where('status', 'active');

        if (class_exists(\SGCart\Marketplace\Models\Seller::class)) {
            $query->where(function ($q) {
                $q->whereNull('seller_id')
                  ->orWhereHas('seller', function ($sub) {
                      $sub->where('status', 'approved');
                  });
            });
        }

        return $query->get()->map(function ($p) {
            $catName = 'Fashion';
            if ($p->category) {
                $topParent = $p->category;
                while ($topParent->parent) {
                    $topParent = $topParent->parent;
                }
                $catName = $topParent->name;
            }

            // Determine sizes and colors dynamically from variants or database column values if present
            $sizes = [];
            $colors = [];
            if ($p->variants && $p->variants->isNotEmpty()) {
                $colors = $p->variants->where('is_active', true)->map(fn ($v) => $v->color?->hex_code)->filter()->unique()->values()->toArray();
                $sizes = $p->variants->where('is_active', true)->map(fn ($v) => $v->size?->code)->filter()->unique()->values()->toArray();
            } else {
                if (isset($p->sizes) && ! empty($p->sizes)) {
                    $sizes = is_array($p->sizes) ? $p->sizes : array_filter(array_map('trim', explode(',', $p->sizes)));
                }
                if (isset($p->colors) && ! empty($p->colors)) {
                    $colors = is_array($p->colors) ? $p->colors : array_filter(array_map('trim', explode(',', $p->colors)));
                }
            }

            $rating = 4.5;
            if (class_exists(\SGCart\Reviews\Models\Review::class)) {
                $avgRating = \SGCart\Reviews\Models\Review::where('product_id', $p->id)
                    ->where('status_id', 2)
                    ->avg('rating');
                if (!is_null($avgRating)) {
                    $rating = (float) $avgRating;
                }
            }

            return [
                'id' => $p->id,
                'slug' => $p->slug,
                'name' => $p->name,
                'cat' => $catName,
                'price' => $p->sale_price ?? $p->price,
                'old' => $p->sale_price ? $p->price : null,
                'rating' => $rating,
                'badge' => $p->sale_price ? 'Sale' : '',
                'sizes' => $sizes,
                'colors' => $colors,
                'desc' => $p->short_description ?? $p->description ?? 'No description available.',
                'description' => $p->description ?? 'No detailed description available.',
                'weight' => $p->weight,
                'dimensions' => $p->dimensions,
                'sku' => $p->sku,
                'stock' => $p->stock,
                'manufacturer' => $p->manufacturer ? $p->manufacturer->name : null,
                'img' => $p->image ? Storage::url($p->image) : asset('images/no-image.svg'),
                'images' => $p->images->isNotEmpty()
                    ? $p->images->sortByDesc('is_default')->map(fn ($img) => Storage::url($img->image_path))->values()->toArray()
                    : [asset('images/no-image.svg')],
                'meta_title' => $p->meta_title,
                'meta_description' => $p->meta_description,
                'meta_keywords' => $p->meta_keywords,
                'search_tags' => $p->searchTerms ? $p->searchTerms->pluck('term')->toArray() : [],
            ];
        })->toArray();
    }

    /**
     * Show storefront homepage.
     */
    public function home()
    {
        $products = collect(self::getProducts())->take(4);
        $categories = Category::parents()->active()->orderBy('sort_order')->take(5)->get();
        if ($categories->isEmpty()) {
            $categories = collect(["Women's Clothing", "Men's Clothing", "Kids' Clothing", 'Accessories', 'Footwear'])->map(function($name, $index) {
                return new Category([
                    'name' => $name,
                    'slug' => \Illuminate\Support\Str::slug($name),
                    'is_active' => true,
                    'sort_order' => $index
                ]);
            });
        }

        return view('welcome', compact('products', 'categories'));
    }

    /**
     * Shop catalogue page.
     */
    public function shop(Request $request)
    {
        $baseProducts = collect(self::getProducts());

        // Apply Search filter first (since it is global)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $baseProducts = self::prioritizeSearch($baseProducts, $search);
        }

        // Apply Price filter next
        if ($request->filled('price_max')) {
            $maxPrice = (float) $request->input('price_max');
            $baseProducts = $baseProducts->filter(fn($p) => $p['price'] <= $maxPrice);
        }

        // Get all category names from all products
        $allCategories = collect(self::getProducts())->pluck('cat')->unique()->values()->toArray();
        if (empty($allCategories)) {
            $allCategories = ["Women's Clothing", "Men's Clothing", "Kids' Clothing", 'Accessories', 'Footwear', 'Sportswear', 'Winter Wear'];
        }

        // Calculate counts based on search and price filters (before category filter is applied)
        $categoryCounts = [];
        foreach ($allCategories as $cat) {
            $categoryCounts[$cat] = $baseProducts->where('cat', $cat)->count();
        }

        // Now apply Category filter for actual product listing
        $products = $baseProducts;
        if ($request->filled('category')) {
            $categories = (array) $request->input('category');
            $products = $products->filter(fn($p) => in_array($p['cat'], $categories));
        }

        // Sorting
        $sort = $request->input('sort', 'default');
        if ($sort === 'price_asc') {
            $products = $products->sortBy('price');
        } elseif ($sort === 'price_desc') {
            $products = $products->sortByDesc('price');
        }

        // Pagination: 12 products per page
        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 12;
        $currentPageResults = $products->slice(($page - 1) * $perPage, $perPage)->values();
        $paginatedProducts = new LengthAwarePaginator(
            $currentPageResults,
            $products->count(),
            $perPage,
            $page,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => $request->query()
            ]
        );

        return view('store.shop', [
            'products' => $paginatedProducts,
            'allCategories' => $allCategories,
            'categoryCounts' => $categoryCounts,
            'selectedCategories' => (array) $request->input('category', []),
            'selectedPriceMax' => $request->input('price_max', 10000),
            'selectedSort' => $sort,
            'searchQuery' => $request->input('search'),
        ]);
    }

    /**
     * Product details page.
     */
    public function product(Request $request, $slug)
    {
        $products = collect(self::getProducts());
        $product = $products->firstWhere('slug', $slug);

        if (! $product) {
            abort(404);
        }

        // Fetch related products (manually assigned via package or fallback logic: Name -> Category -> Search Tag)
        $productModel = Product::find($product['id']);
        $relatedIds = [];

        if (class_exists(\SGCart\RelatedProducts\Models\RelatedProduct::class) && $productModel) {
            // Package is installed: Fetch products related via the relationship
            $relatedIds = $productModel->relatedProducts()->where('status', 'active')->pluck('products.id')->toArray();
        }

        if (empty($relatedIds) && $productModel) {
            // Fallback: Name -> Category -> Search Tag (via ResolveRelatedProducts action)
            $relatedIds = app(ResolveRelatedProducts::class)->handle($productModel);
        }

        $related = collect($relatedIds)
            ->map(fn($id) => $products->firstWhere('id', $id))
            ->filter()
            ->take(config('store.max_no_related_products', 4))
            ->values();

        if ($request->ajax() || $request->has('ajax')) {
            $reviewsData = $this->getProductReviews($product['id'], $request);
            return view('store.partials.reviews', compact('reviewsData'))->render();
        }

        $reviewsData = $this->getProductReviews($product['id'], $request);

        $approvedReviews = collect();
        $avgProductRating = 0;
        $totalReviewsCount = 0;
        $count5 = 0; $count4 = 0; $count3 = 0; $count2 = 0; $count1 = 0;
        $pct5 = 0; $pct4 = 0; $pct3 = 0; $pct2 = 0; $pct1 = 0;

        if (class_exists(\SGCart\Reviews\Models\Review::class)) {
            $approvedReviews = \SGCart\Reviews\Models\Review::where('product_id', $product['id'])
                ->where('status_id', 2)
                ->latest()
                ->get();
            if ($approvedReviews->isNotEmpty()) {
                $avgProductRating = $approvedReviews->avg('rating');
                $totalReviewsCount = $approvedReviews->count();

                $count5 = $approvedReviews->where('rating', 5)->count();
                $count4 = $approvedReviews->where('rating', 4)->count();
                $count3 = $approvedReviews->where('rating', 3)->count();
                $count2 = $approvedReviews->where('rating', 2)->count();
                $count1 = $approvedReviews->where('rating', 1)->count();

                $pct5 = ($count5 / $totalReviewsCount) * 100;
                $pct4 = ($count4 / $totalReviewsCount) * 100;
                $pct3 = ($count3 / $totalReviewsCount) * 100;
                $pct2 = ($count2 / $totalReviewsCount) * 100;
                $pct1 = ($count1 / $totalReviewsCount) * 100;
            }
        }

        return view('store.product', compact(
            'product', 'related', 'reviewsData', 'approvedReviews', 'avgProductRating',
            'totalReviewsCount', 'count5', 'count4', 'count3', 'count2', 'count1',
            'pct5', 'pct4', 'pct3', 'pct2', 'pct1'
        ));
    }

    /**
     * Get filtered and paginated reviews for a product.
     */
    public function getProductReviews($productId, Request $request)
    {
        if (!class_exists(\SGCart\Reviews\Models\Review::class)) {
            return [];
        }

        $filter = $request->input('review_filter', 'helpful');
        $page = (int) $request->input('review_page', 1);

        $query = \SGCart\Reviews\Models\Review::with(['customer', 'images'])
            ->where('product_id', $productId)
            ->where('status_id', 2);

        // Apply filtration based on the captured URL param
        if ($filter === 'positive') {
            $query->where('rating', '>=', 4);
        } elseif ($filter === 'negative') {
            $query->where('rating', '<=', 3);
        }

        // Sorting
        if ($filter === 'latest') {
            $query->latest();
        } else {
            // default/helpful: rating descending, then latest
            $query->orderBy('rating', 'desc')->latest();
        }

        return $query->paginate(5, ['*'], 'review_page', $page);
    }


    /**
     * View cart page.
     */
    public function cart()
    {
        $cartModel = Cart::getActiveCart();
        $cart = $cartModel->getFormattedItems();

        $subtotal = 0;
        foreach ($cart as $key => $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        $discount = 0;
        if (app()->bound('coupon.calculator')) {
            $discount = app('coupon.calculator')->calculate(session('coupon_code'), $subtotal);
        }
        $tax = 0.0;
        $taxLabel = null;
        if (app()->bound('tax.calculator')) {
            $taxResult = app('tax.calculator')->calculate($subtotal);
            $tax = (float) $taxResult['amount'];
            $taxLabel = $taxResult['label'];
        }

        $shippingCost = 0.0;
        $hasShippingPackage = class_exists(\SGCart\Shipping\Models\ShippingRate::class);
        if ($hasShippingPackage) {
            $shippingRates = \SGCart\Shipping\Models\ShippingRate::where('is_active', true)
                ->where('min_order_amount', '<=', $subtotal)
                ->get();

            $cheapestRate = $shippingRates->map(function ($rate) use ($subtotal) {
                $rate->calculated_cost = $rate->calculateCost($subtotal);
                return $rate;
            })->sortBy('calculated_cost')->first();

            $shippingCost = $cheapestRate ? (float) $cheapestRate->calculated_cost : 0.0;
        }

        $selectionMode = 'user_choice';
        if (class_exists(\SGCart\Shipping\Models\ShippingSetting::class)) {
            $selectionMode = \SGCart\Shipping\Models\ShippingSetting::getVal('shipping_selection_mode', 'user_choice');
        }

        // Only add shipping cost to cart total if not in user choice mode
        $effectiveShippingCost = ($selectionMode === 'user_choice') ? 0.0 : $shippingCost;
        $total = max(0, $subtotal + $tax - $discount + $effectiveShippingCost);

        return view('store.cart', compact('cart', 'subtotal', 'tax', 'taxLabel', 'discount', 'total', 'shippingCost', 'selectionMode', 'hasShippingPackage'));
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

        $product = Product::find($productId);
        if (! $product || $product->status !== \App\Enums\ProductStatus::ACTIVE) {
            return redirect()->back()->with('error', 'Product not found.');
        }

        if (class_exists(\SGCart\Marketplace\Models\Seller::class)) {
            $seller = $product->seller;
            if ($seller && $seller->status->value !== 'approved') {
                return redirect()->back()->with('error', 'Product not found.');
            }
        }

        $cartModel = Cart::getActiveCart();

        // Stock check
        $currentStock = $product->stock ?? 0;
        if ($currentStock <= 0) {
            return redirect()->back()->with('error', 'Sorry, this product is currently out of stock.');
        }

        $cartItem = $cartModel->items()
            ->where('product_id', $productId)
            ->where('size', $size)
            ->where('color', $color)
            ->first();

        $existingQty = $cartItem ? $cartItem->quantity : 0;
        $allowableAdd = $currentStock - $existingQty;

        if ($allowableAdd <= 0) {
            return redirect()->route('store.cart')->with('warning', "Your cart already contains the maximum available stock ({$currentStock} items) for this product.");
        }

        if ($quantity > $allowableAdd) {
            $qtyToAdd = $allowableAdd;
            $wasCapped = true;
        } else {
            $qtyToAdd = $quantity;
            $wasCapped = false;
        }

        if ($cartItem) {
            $cartItem->quantity += $qtyToAdd;
            $cartItem->save();
        } else {
            $cartModel->items()->create([
                'product_id' => $productId,
                'quantity' => $qtyToAdd,
                'size' => $size,
                'color' => $color,
            ]);
        }

        if ($wasCapped) {
            return redirect()->route('store.cart')->with('warning', "Only {$currentStock} items are available in stock. We added {$qtyToAdd} items to your cart (bringing it to maximum available stock).");
        }

        return redirect()->route('store.cart')->with('success', 'Product added to cart!');
    }

    /**
     * Update cart quantities.
     */
    public function updateCart(Request $request)
    {
        $cartModel = Cart::getActiveCart();
        $quantities = $request->input('quantities', []);
        $wasLimited = false;

        foreach ($quantities as $key => $qty) {
            $parts = explode('_', $key);
            if (count($parts) >= 1) {
                $productId = (int) $parts[0];
                $size = isset($parts[1]) && $parts[1] !== '' ? $parts[1] : null;
                $color = isset($parts[2]) && $parts[2] !== '' ? $parts[2] : null;

                $cartItem = $cartModel->items()
                    ->where('product_id', $productId)
                    ->where('size', $size)
                    ->where('color', $color)
                    ->first();

                if ($cartItem) {
                    if ((int) $qty <= 0) {
                        $cartItem->delete();
                    } else {
                        $product = Product::find($productId);
                        $currentStock = $product ? ($product->stock ?? 0) : 0;
                        $requestedQty = (int) $qty;

                        if ($requestedQty > $currentStock) {
                            $cartItem->quantity = $currentStock;
                            $cartItem->save();
                            $wasLimited = true;
                        } else {
                            $cartItem->quantity = $requestedQty;
                            $cartItem->save();
                        }
                    }
                }
            }
        }

        if ($cartModel->items()->count() === 0) {
            session()->forget('coupon_code');
            session()->forget('coupon_discount');
        }

        if ($wasLimited) {
            return redirect()->route('store.cart')->with('warning', 'Some items were limited to the maximum available stock.');
        }

        return redirect()->route('store.cart')->with('success', 'Cart updated successfully.');
    }

    /**
     * Remove item from cart.
     */
    public function removeFromCart($key)
    {
        $cartModel = Cart::getActiveCart();

        $parts = explode('_', $key);
        if (count($parts) >= 1) {
            $productId = (int) $parts[0];
            $size = isset($parts[1]) && $parts[1] !== '' ? $parts[1] : null;
            $color = isset($parts[2]) && $parts[2] !== '' ? $parts[2] : null;

            $cartModel->items()
                ->where('product_id', $productId)
                ->where('size', $size)
                ->where('color', $color)
                ->delete();
        }

        if ($cartModel->items()->count() === 0) {
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
        if (! auth('customer')->check()) {
            return redirect()->route('store.login')->with('error', 'Please log in to proceed to checkout.');
        }

        $cartModel = Cart::getActiveCart();
        $cart = $cartModel->getFormattedItems();
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

        $addresses = auth('customer')->user()->addresses;
        $defaultAddress = $addresses->where('is_default', true)->first() ?? $addresses->first();
        $addressArray = null;
        if ($defaultAddress) {
            $addressArray = [
                'country' => $defaultAddress->country,
                'state' => $defaultAddress->state,
                'zip' => $defaultAddress->zip,
            ];
        }

        $tax = 0.0;
        $taxLabel = null;
        if (app()->bound('tax.calculator')) {
            $taxResult = app('tax.calculator')->calculate($subtotal, $addressArray);
            $tax = (float) $taxResult['amount'];
            $taxLabel = $taxResult['label'];
        }

        $shippingRates = collect();
        $selectionMode = 'user_choice';
        $hasShippingPackage = class_exists(\SGCart\Shipping\Models\ShippingRate::class);
        if ($hasShippingPackage) {
            $shippingRates = \SGCart\Shipping\Models\ShippingRate::where('is_active', true)
                ->where('min_order_amount', '<=', $subtotal)
                ->get();
        }
        if (class_exists(\SGCart\Shipping\Models\ShippingSetting::class)) {
            $selectionMode = \SGCart\Shipping\Models\ShippingSetting::getVal('shipping_selection_mode', 'user_choice');
        }

        // Map and compute dynamically based on rate type
        $shippingRates = $shippingRates->map(function ($rate) use ($subtotal) {
            $rate->calculated_cost = (float) $rate->calculateCost($subtotal);
            return $rate;
        })->sortBy('calculated_cost');

        $shippingCost = $shippingRates->first() ? (float) $shippingRates->first()->calculated_cost : 0.0;
        $total = max(0, $subtotal + $tax - $discount + $shippingCost);

        $activeGateways = [];
        if (app()->bound('payment.manager')) {
            $activeGateways = app('payment.manager')->getActiveGateways();
        }

        return view('store.checkout', compact('cart', 'subtotal', 'tax', 'taxLabel', 'discount', 'total', 'addresses', 'shippingRates', 'shippingCost', 'selectionMode', 'hasShippingPackage', 'activeGateways'));
    }

    /**
     * Place order.
     */
    public function placeOrder(Request $request)
    {
        if (! auth('customer')->check()) {
            return redirect()->route('store.login')->with('error', 'Please log in to proceed to checkout.');
        }

        $request->validate([
            'address_id' => 'nullable|integer',
            'shipping_rate_id' => 'nullable|integer',
            'first_name' => 'required_without:address_id|nullable|string|max:100',
            'last_name' => 'required_without:address_id|nullable|string|max:100',
            'email' => 'required_without:address_id|nullable|email|max:150',
            'address' => 'required_without:address_id|nullable|string|max:255',
            'city' => 'required_without:address_id|nullable|string|max:100',
            'state' => 'required_without:address_id|nullable|string|max:100',
            'zip' => 'required_without:address_id|nullable|string|max:20',
            'country' => 'required_without:address_id|nullable|string|max:100',
            'payment_method' => 'required|string',
        ]);

        $cartModel = Cart::getActiveCart();
        $cart = $cartModel->getFormattedItems();
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

        // Resolve address details first
        $firstName = null;
        $lastName = null;
        $email = null;
        $addressStr = null;
        $city = null;
        $state = null;
        $zip = null;
        $country = null;
        $phone = null;
        $alternatePhone = null;
        $addressType = null;
        $landmark = null;
        $shippingAndBillingSame = true;
        $billingFirstName = null;
        $billingLastName = null;
        $billingAddress = null;
        $billingCity = null;
        $billingState = null;
        $billingZip = null;
        $billingCountry = null;
        $billingPhone = null;

        if ($request->filled('address_id')) {
            $savedAddress = auth('customer')->user()->addresses()->find($request->address_id);
            if (! $savedAddress) {
                return redirect()->back()->withErrors(['address_id' => 'Selected address is invalid.']);
            }
            $firstName = $savedAddress->first_name;
            $lastName = $savedAddress->last_name;
            $email = auth('customer')->user()->email;
            $addressStr = $savedAddress->address;
            $city = $savedAddress->city;
            $state = $savedAddress->state;
            $zip = $savedAddress->zip;
            $country = $savedAddress->country;
            $phone = $savedAddress->phone;
            $alternatePhone = $savedAddress->alternate_phone;
            $addressType = $savedAddress->address_type;
            $landmark = $savedAddress->landmark;
            $shippingAndBillingSame = $savedAddress->shipping_and_billing_same;
            $billingFirstName = $savedAddress->billing_first_name;
            $billingLastName = $savedAddress->billing_last_name;
            $billingAddress = $savedAddress->billing_address;
            $billingCity = $savedAddress->billing_city;
            $billingState = $savedAddress->billing_state;
            $billingZip = $savedAddress->billing_zip;
            $billingCountry = $savedAddress->billing_country;
            $billingPhone = $savedAddress->billing_phone;
        } else {
            $firstName = $request->first_name;
            $lastName = $request->last_name;
            $email = $request->email;
            $addressStr = $request->address;
            $city = $request->city;
            $state = $request->state;
            $zip = $request->zip;
            $country = $request->country;
            $phone = $request->phone;
            $alternatePhone = $request->alternate_phone;
            $addressType = $request->address_type ?? 'work';
            $landmark = $request->landmark;
            $shippingAndBillingSame = $request->has('shipping_and_billing_same') ? $request->boolean('shipping_and_billing_same') : true;
            $billingFirstName = $shippingAndBillingSame ? $firstName : $request->billing_first_name;
            $billingLastName = $shippingAndBillingSame ? $lastName : $request->billing_last_name;
            $billingAddress = $shippingAndBillingSame ? $addressStr : $request->billing_address;
            $billingCity = $shippingAndBillingSame ? $city : $request->billing_city;
            $billingState = $shippingAndBillingSame ? $state : $request->billing_state;
            $billingZip = $shippingAndBillingSame ? $zip : $request->billing_zip;
            $billingCountry = $shippingAndBillingSame ? $country : $request->billing_country;
            $billingPhone = $shippingAndBillingSame ? $phone : $request->billing_phone;

            // Optionally save the new address
            if ($request->boolean('save_address')) {
                auth('customer')->user()->addresses()->create([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'address' => $addressStr,
                    'city' => $city,
                    'state' => $state,
                    'zip' => $zip,
                    'country' => $country,
                    'phone' => $phone,
                    'alternate_phone' => $alternatePhone,
                    'address_type' => $addressType,
                    'landmark' => $landmark,
                    'shipping_and_billing_same' => $shippingAndBillingSame,
                    'billing_first_name' => $billingFirstName,
                    'billing_last_name' => $billingLastName,
                    'billing_address' => $billingAddress,
                    'billing_city' => $billingCity,
                    'billing_state' => $billingState,
                    'billing_zip' => $billingZip,
                    'billing_country' => $billingCountry,
                    'billing_phone' => $billingPhone,
                    'is_default' => auth('customer')->user()->addresses()->count() === 0,
                ]);
            }
        }

        // Calculate dynamic tax based on resolved address
        $addressArray = [
            'country' => $country,
            'state' => $state,
            'zip' => $zip,
        ];
        $tax = 0.0;
        $taxMethod = null;
        if (app()->bound('tax.calculator')) {
            $taxResult = app('tax.calculator')->calculate($subtotal, $addressArray);
            $tax = (float) $taxResult['amount'];
            $taxMethod = $taxResult['label'] ?? $taxResult['name'];
        }

        // Calculate shipping cost
        $shippingCost = 0.0;
        $shippingMethodName = null;
        if (class_exists(\SGCart\Shipping\Models\ShippingRate::class)) {
            $selectionMode = 'user_choice';
            if (class_exists(\SGCart\Shipping\Models\ShippingSetting::class)) {
                $selectionMode = \SGCart\Shipping\Models\ShippingSetting::getVal('shipping_selection_mode', 'user_choice');
            }

            if ($selectionMode === 'user_choice' && $request->filled('shipping_rate_id')) {
                $shippingRate = \SGCart\Shipping\Models\ShippingRate::where('is_active', true)
                    ->where('min_order_amount', '<=', $subtotal)
                    ->find($request->shipping_rate_id);
                if ($shippingRate) {
                    $shippingCost = (float) $shippingRate->calculateCost($subtotal);
                    $shippingMethodName = $shippingRate->name;
                }
            } else {
                // Auto-select cheapest eligible rate
                $eligibleRates = \SGCart\Shipping\Models\ShippingRate::where('is_active', true)
                    ->where('min_order_amount', '<=', $subtotal)
                    ->get();
                $cheapestRate = $eligibleRates->map(function ($rate) use ($subtotal) {
                    $rate->calculated_cost = (float) $rate->calculateCost($subtotal);
                    return $rate;
                })->sortBy('calculated_cost')->first();

                if ($cheapestRate) {
                    $shippingCost = (float) $cheapestRate->calculated_cost;
                    $shippingMethodName = $cheapestRate->name;
                }
            }
        }

        $total = max(0, $subtotal + $tax - $discount + $shippingCost);
        // Generate dynamic unique order number
        $orderNumber = 'SG' . date('ymd') . strtoupper(Str::random(4));

        $paymentMethodName = 'Card';
        $gateway = null;

        if (app()->bound('payment.manager')) {
            $gateway = app('payment.manager')->getGateway($request->payment_method);
            if (!$gateway || !app('payment.manager')->isEnabled($request->payment_method)) {
                return redirect()->back()->withErrors(['payment_method' => 'Selected payment method is invalid or disabled.']);
            }
            $paymentMethodName = $gateway->getName();
        }

        $pendingOrderUlid = session('pending_checkout_order_id');
        $order = null;

        if ($pendingOrderUlid) {
            $order = \App\Models\Order::with('items')->where('ulid', $pendingOrderUlid)->first();
            if ($order) {
                if ($order->payment_status === \App\Enums\PaymentStatus::PAID) {
                    $cartModel->items()->delete();
                    session()->forget('pending_checkout_order_id');
                    session()->forget('coupon_code');
                    session()->forget('coupon_discount');
                    return redirect()->route('store.success', ['order_id' => $order->order_number]);
                }

                // Check if cart matches
                $cartMatches = false;
                if ((float)$order->total === (float)$total && $order->items->count() === $cartModel->items->count()) {
                    $cartMatches = true;
                    foreach ($cartModel->items as $cartItem) {
                        $orderItem = $order->items->where('product_id', $cartItem->product_id)
                            ->where('quantity', $cartItem->quantity)
                            ->where('size', $cartItem->size)
                            ->where('color', $cartItem->color)
                            ->first();
                        if (!$orderItem) {
                            $cartMatches = false;
                            break;
                        }
                    }
                }

                if ($cartMatches) {
                    $order->update([
                        'order_number' => $orderNumber,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'email' => $email,
                        'phone' => $phone,
                        'alternate_phone' => $alternatePhone,
                        'address_type' => $addressType,
                        'landmark' => $landmark,
                        'shipping_and_billing_same' => $shippingAndBillingSame,
                        'billing_first_name' => $billingFirstName,
                        'billing_last_name' => $billingLastName,
                        'billing_address' => $billingAddress,
                        'billing_city' => $billingCity,
                        'billing_state' => $billingState,
                        'billing_zip' => $billingZip,
                        'billing_country' => $billingCountry,
                        'billing_phone' => $billingPhone,
                        'address' => $addressStr,
                        'city' => $city,
                        'state' => $state,
                        'zip' => $zip,
                        'country' => $country,
                        'subtotal' => $subtotal,
                        'tax' => $tax,
                        'tax_method' => $taxMethod,
                        'shipping_charge' => $shippingCost,
                        'shipping_method' => $shippingMethodName,
                        'discount' => $discount,
                        'total' => $total,
                    ]);
                } else {
                    // Restock old order
                    foreach ($order->items as $oldItem) {
                        $prod = \App\Models\Product::find($oldItem->product_id);
                        if ($prod) {
                            $prod->increment('stock', $oldItem->quantity);
                        }
                    }
                    $order->items()->delete();
                    $order->delete();
                    $order = null;
                }
            }
        }

        $isNewOrder = false;
        if (!$order) {
            $isNewOrder = true;
            // Create Order in Database
            $order = \App\Models\Order::create([
                'order_number' => $orderNumber,
                'customer_id' => auth('customer')->id(),
                'session_id' => request()->session()->getId(),
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'phone' => $phone,
                'alternate_phone' => $alternatePhone,
                'address_type' => $addressType,
                'landmark' => $landmark,
                'shipping_and_billing_same' => $shippingAndBillingSame,
                'billing_first_name' => $billingFirstName,
                'billing_last_name' => $billingLastName,
                'billing_address' => $billingAddress,
                'billing_city' => $billingCity,
                'billing_state' => $billingState,
                'billing_zip' => $billingZip,
                'billing_country' => $billingCountry,
                'billing_phone' => $billingPhone,
                'address' => $addressStr,
                'city' => $city,
                'state' => $state,
                'zip' => $zip,
                'country' => $country,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'tax_method' => $taxMethod,
                'shipping_charge' => $shippingCost,
                'shipping_method' => $shippingMethodName,
                'discount' => $discount,
                'total' => $total,
                'status' => \App\Enums\OrderStatus::NEW_ORDER,
            ]);
        }

        if (!$gateway) {
            $order->payments()->create([
                'payment_method' => $paymentMethodName,
                'amount' => $total,
                'status' => \App\Enums\PaymentStatus::PAID,
                'card_name' => $request->card_name,
                'card_number_masked' => '**** **** **** ' . substr(str_replace(' ', '', $request->card_num), -4),
            ]);
        } else {
            try {
                $paymentResult = $gateway->processPayment($request, $order);
            } catch (\Exception $e) {
                // Restock items and delete the order
                foreach ($order->items as $item) {
                    $product = \App\Models\Product::find($item->product_id);
                    if ($product) {
                        $product->increment('stock', $item->quantity);
                    }
                }
                $order->items()->delete();
                $order->delete();
                session()->forget('pending_checkout_order_id');
                return redirect()->back()->withInput()->with('error', 'Payment failed: ' . $e->getMessage());
            }

            if (!$paymentResult['success']) {
                // Restock items and delete the order
                foreach ($order->items as $item) {
                    $product = \App\Models\Product::find($item->product_id);
                    if ($product) {
                        $product->increment('stock', $item->quantity);
                    }
                }
                $order->items()->delete();
                $order->delete();
                session()->forget('pending_checkout_order_id');
                return redirect()->back()->withInput()->with('error', $paymentResult['message'] ?? 'Payment transaction failed.');
            }
        }

        if ($isNewOrder) {
            // Create OrderItems in Database
            foreach ($cartModel->items as $cartItem) {
                $product = \App\Models\Product::find($cartItem->product_id);

                \App\Models\OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'product_name' => $product ? $product->name : 'Unknown Product',
                    'product_sku' => $product ? $product->sku : null,
                    'price' => $product ? ($product->sale_price ?? $product->price) : 0,
                    'quantity' => $cartItem->quantity,
                    'size' => $cartItem->size,
                    'color' => $cartItem->color,
                ]);

                // Optional: decrement product stock
                if ($product) {
                    $product->decrement('stock', $cartItem->quantity);
                }
            }
        }

        $redirectUrl = isset($paymentResult['redirect_url']) ? $paymentResult['redirect_url'] : null;

        if ($redirectUrl) {
            // Save pending order ID in session so we do not clear the cart yet
            session(['pending_checkout_order_id' => $order->ulid]);
        } else {
            // Clear Database Cart immediately for synchronous checkouts
            $cartModel->items()->delete();

            // Clear coupon and session info
            session()->forget('coupon_code');
            session()->forget('coupon_discount');
            session()->forget('pending_checkout_order_id');
        }

        $redirectUrl = $redirectUrl ?? route('store.success', ['order_id' => $order->order_number]);
        return redirect($redirectUrl);
    }

    /**
     * Order success page.
     */
    public function success(Request $request)
    {
        $orderId = $request->input('order_id', 'SGMOCKORDER');

        $order = \App\Models\Order::where('order_number', $orderId)->first();
        if ($order && $order->customer_id === auth('customer')->id()) {
            if ($order->payment_status === \App\Enums\PaymentStatus::PAID) {
                // Clear active cart since payment succeeded
                $cartModel = \App\Models\Cart::getActiveCart();
                if ($cartModel) {
                    $cartModel->items()->delete();
                }
                session()->forget('pending_checkout_order_id');
                session()->forget('coupon_code');
                session()->forget('coupon_discount');
            }
        }

        return view('store.success', compact('orderId'));
    }

    /**
     * Account / Profile page.
     */
    public function account(Request $request, $tab = 'orders')
    {
        if (! auth('customer')->check()) {
            return redirect()->route('store.login')->with('error', 'Please log in to access your account.');
        }

        $validTabs = ['orders', 'profile', 'address', 'wishlist'];
        $activeTab = in_array($tab, $validTabs) ? $tab : 'orders';

        // Load real orders from the database
        $ordersQuery = auth('customer')->user()->orders()
            ->with('items')
            ->latest();

        if ($request->filled('order_search')) {
            $search = $request->input('order_search');
            $ordersQuery->where('order_number', 'like', "%{$search}%");
        }

        $orders = $ordersQuery->paginate(4)
            ->withQueryString()
            ->through(function ($order) {
                return [
                    'id' => $order->order_number,
                    'ulid' => $order->ulid,
                    'items_count' => $order->items->sum('quantity'),
                    'date' => $order->created_at->format('M d, Y'),
                    'amount' => $order->total,
                    'status' => $order->status->value ?? $order->status,
                ];
            });

        $wishlistIds = session()->get('wishlist', []);
        $allProducts = self::getProducts();
        $wishlist = array_filter($allProducts, fn ($p) => in_array($p['id'], $wishlistIds));

        $addresses = auth('customer')->user()->addresses;

        return view('store.account', compact('orders', 'wishlist', 'activeTab', 'addresses'));
    }

    /**
     * Update user profile details.
     */
    public function updateProfile(Request $request)
    {
        if (! auth('customer')->check()) {
            return redirect()->route('store.login')->with('error', 'Please log in to update your profile.');
        }

        $customer = auth('customer')->user();

        // If email is already present, they cannot submit email
        if ($customer->email && $request->has('email')) {
            throw ValidationException::withMessages([
                'email' => 'You are not allowed to update your registered email address.',
            ]);
        }

        // If phone_no is already present, they cannot submit phone_no
        if ($customer->phone_no && $request->has('phone_no')) {
            throw ValidationException::withMessages([
                'phone_no' => 'You are not allowed to update your registered phone number.',
            ]);
        }

        // Both fields cannot be updated at the same time
        if ($request->has('email') && $request->has('phone_no')) {
            throw ValidationException::withMessages([
                'email' => 'You cannot update both email and phone number at the same time.',
            ]);
        }

        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
        ];
        $messages = [];

        $addingEmail = ! $customer->email;
        $addingPhone = ! $customer->phone_no;

        if ($addingEmail) {
            $rules['email'] = ['nullable', 'string', 'email', 'max:255', 'unique:customers,email'];
            $messages['email.unique'] = 'The email address has already been taken.';
            $messages['email.email'] = 'The email address must be a valid email address.';
        }

        if ($addingPhone) {
            $rules['phone_no'] = [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if (is_null($value) || $value === '') {
                        return;
                    }
                    if (!str_starts_with($value, '+')) {
                        $fail('The phone number must include a country code starting with +.');
                        return;
                    }
                    $digits = substr($value, 1);
                    if (!ctype_digit($digits)) {
                        $fail('The phone number must contain only digits after the + country code.');
                        return;
                    }
                    if (strlen($digits) < 7 || strlen($digits) > 15) {
                        if (strlen($digits) > 15) {
                            $fail('The phone number must not be more than 15 digits.');
                        } else {
                            $fail('The phone number must be at least 7 digits.');
                        }
                        return;
                    }
                },
                'unique:customers,phone_no',
            ];
            $messages['phone_no.unique'] = 'The phone number has already been taken.';
        }

        $request->validate($rules, $messages);

        $oldName = $customer->name;
        $oldEmail = $customer->email;
        $oldPhone = $customer->phone_no;

        $customer->name = trim($request->first_name.' '.$request->last_name);

        $emailAdded = false;

        if ($addingEmail && $request->filled('email')) {
            $customer->email = $request->email;
            $customer->email_verified_at = null; // Mark as unverified
            $emailAdded = true;
        }

        $phoneAdded = false;

        if ($addingPhone && $request->filled('phone_no')) {
            $customer->phone_no = $request->phone_no;
            $customer->phone_verified_at = null; // Mark as unverified
            $phoneAdded = true;
        }

        $customer->save();

        // Detect and log changes
        $changes = [];
        $original = [];
        if ($oldName !== $customer->name) {
            $original['name'] = $oldName;
            $changes['name'] = $customer->name;
        }
        if ($oldEmail !== $customer->email) {
            $original['email'] = $oldEmail;
            $changes['email'] = $customer->email;
        }
        if ($oldPhone !== $customer->phone_no) {
            $original['phone_no'] = $oldPhone;
            $changes['phone_no'] = $customer->phone_no;
        }

        if (! empty($changes)) {
            app(LogActivity::class)->capture(
                description: 'Customer profile updated',
                event: 'profile.update',
                subject: $customer,
                attributeChanges: ['old' => $original, 'new' => $changes]
            );
        }

        app(ManageOtp::class)->generate($customer, ManageOtp::REASON_PROFILE_UPDATE);

        if ($emailAdded) {
            return redirect()->route('store.otp.verify')->with('success', 'Profile updated. Please verify your new email address.');
        }

        if ($phoneAdded) {
            return redirect()->route('store.otp.verify')->with('success', 'Profile updated. Please verify your new phone number.');
        }

        return redirect()->route('store.account', 'profile')->with('success', 'Profile details updated successfully!');
    }

    /**
     * Toggle wishlist item.
     */
    public function toggleWishlist(Request $request)
    {
        $productId = (int) $request->input('product_id');
        $wishlist = session()->get('wishlist', []);

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
     * Show guest wishlist page.
     */
    public function guestWishlist()
    {
        if (\Illuminate\Support\Facades\Auth::guard('customer')->check()) {
            return redirect()->route('store.account', 'wishlist');
        }

        $wishlistIds = session()->get('wishlist', []);
        $allProducts = self::getProducts();
        $wishlist = array_filter($allProducts, fn($p) => in_array($p['id'], $wishlistIds));

        return view('store.wishlist', compact('wishlist'));
    }

    /**
     * Prioritize and filter products based on name, sku, category and search tags.
     */
    public static function prioritizeSearch($products, $query)
    {
        $query = strtolower(trim($query));
        if (empty($query)) {
            return collect();
        }

        $queryWords = preg_split('/\s+/', $query, -1, PREG_SPLIT_NO_EMPTY);

        return $products->map(function ($p) use ($query, $queryWords) {
            $score = 0;
            $name = strtolower($p['name'] ?? '');
            $sku = strtolower($p['sku'] ?? '');
            $cat = strtolower($p['cat'] ?? '');
            $tags = array_map('strtolower', $p['search_tags'] ?? []);

            // Helper to check if all query words are present
            $allWordsInName = true;
            foreach ($queryWords as $qw) {
                if (!str_contains($name, $qw)) {
                    $allWordsInName = false;
                    break;
                }
            }

            $allWordsInSku = true;
            foreach ($queryWords as $qw) {
                if (!str_contains($sku, $qw)) {
                    $allWordsInSku = false;
                    break;
                }
            }

            $allWordsInCat = true;
            foreach ($queryWords as $qw) {
                if (!str_contains($cat, $qw)) {
                    $allWordsInCat = false;
                    break;
                }
            }

            // A tag matches if the full query matches it or is inside it
            $tagMatch = false;
            foreach ($tags as $tag) {
                if ($tag === $query || str_contains($tag, $query)) {
                    $tagMatch = true;
                    break;
                }
            }

            // Or if all query words are matched across one or more tags
            $allWordsInTags = false;
            if (!$tagMatch) {
                $matchedWords = 0;
                foreach ($queryWords as $qw) {
                    foreach ($tags as $tag) {
                        if (str_contains($tag, $qw)) {
                            $matchedWords++;
                            break;
                        }
                    }
                }
                if ($matchedWords === count($queryWords)) {
                    $allWordsInTags = true;
                }
            }

            // 1. Check Product Name
            if (str_contains($name, $query)) {
                if ($name === $query) {
                    $score = 100;
                } elseif (str_starts_with($name, $query)) {
                    $score = 90;
                } else {
                    $score = 80;
                }
            } elseif ($allWordsInName) {
                $score = 75;
            }
            // 2. Check SKU
            elseif (str_contains($sku, $query) || $allWordsInSku) {
                if ($sku === $query) {
                    $score = 60;
                } else {
                    $score = 50;
                }
            }
            // 3. Check Category
            elseif (str_contains($cat, $query) || $allWordsInCat) {
                if ($cat === $query) {
                    $score = 40;
                } else {
                    $score = 30;
                }
            }
            // 4. Check Search Terms / Tags
            elseif ($tagMatch) {
                $score = 20;
            } elseif ($allWordsInTags) {
                $score = 10;
            }

            $p['_search_score'] = $score;
            return $p;
        })
        ->filter(fn($p) => $p['_search_score'] > 0)
        ->sortByDesc('_search_score')
        ->values();
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
        $results = self::prioritizeSearch($products, $query)
            ->map(fn ($p) => [
                'id' => $p['id'],
                'name' => $p['name'],
                'price' => (float) $p['price'],
                'cat' => $p['cat'],
                'img' => $p['img'],
                'url' => route('store.product', $p['slug']),
            ])->values()->take(5);

        return response()->json($results);
    }

    /**
     * Add new customer address.
     */
    public function addAddress(Request $request)
    {
        if (! auth('customer')->check()) {
            return redirect()->route('store.login')->with('error', 'Please log in to manage your addresses.');
        }

        $rules = [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'zip' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'alternate_phone' => 'nullable|string|max:20',
            'address_type' => 'required|in:office,work,other',
            'landmark' => 'nullable|string|max:255',
            'shipping_and_billing_same' => 'nullable',
        ];

        $isSame = $request->has('shipping_and_billing_same') ? $request->boolean('shipping_and_billing_same') : false;

        if (!$isSame) {
            $rules['billing_first_name'] = 'required|string|max:100';
            $rules['billing_last_name'] = 'required|string|max:100';
            $rules['billing_address'] = 'required|string|max:255';
            $rules['billing_city'] = 'required|string|max:100';
            $rules['billing_state'] = 'required|string|max:100';
            $rules['billing_zip'] = 'required|string|max:20';
            $rules['billing_country'] = 'required|string|max:100';
            $rules['billing_phone'] = 'required|string|max:20';
        }

        $request->validate($rules);

        $customer = auth('customer')->user();

        $address = $customer->addresses()->create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'zip' => $request->zip,
            'country' => $request->country,
            'phone' => $request->phone,
            'alternate_phone' => $request->alternate_phone,
            'address_type' => $request->address_type,
            'landmark' => $request->landmark,
            'shipping_and_billing_same' => $isSame,
            'billing_first_name' => $isSame ? $request->first_name : $request->billing_first_name,
            'billing_last_name' => $isSame ? $request->last_name : $request->billing_last_name,
            'billing_address' => $isSame ? $request->address : $request->billing_address,
            'billing_city' => $isSame ? $request->city : $request->billing_city,
            'billing_state' => $isSame ? $request->state : $request->billing_state,
            'billing_zip' => $isSame ? $request->zip : $request->billing_zip,
            'billing_country' => $isSame ? $request->country : $request->billing_country,
            'billing_phone' => $isSame ? $request->phone : $request->billing_phone,
            'is_default' => $customer->addresses()->count() === 0,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Address added successfully!',
                'address' => $address,
            ]);
        }

        return redirect()->route('store.account', 'address')->with('success', 'Address added successfully!');
    }

    /**
     * Delete customer address.
     */
    public function deleteAddress(Request $request, $id)
    {
        if (! auth('customer')->check()) {
            return redirect()->route('store.login')->with('error', 'Please log in to manage your addresses.');
        }

        $address = auth('customer')->user()->addresses()->find($id);
        if ($address) {
            $wasDefault = $address->is_default;
            $address->delete();

            // If we deleted the default address, make another one default
            if ($wasDefault) {
                $nextAddress = auth('customer')->user()->addresses()->first();
                if ($nextAddress) {
                    $nextAddress->update(['is_default' => true]);
                }
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Address deleted successfully!',
            ]);
        }

        return redirect()->route('store.account', 'address')->with('success', 'Address deleted successfully!');
    }

    /**
     * Update customer address.
     */
    public function updateAddress(Request $request, $id)
    {
        if (! auth('customer')->check()) {
            return redirect()->route('store.login')->with('error', 'Please log in to manage your addresses.');
        }

        $rules = [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'zip' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'alternate_phone' => 'nullable|string|max:20',
            'address_type' => 'required|in:office,work,other',
            'landmark' => 'nullable|string|max:255',
            'shipping_and_billing_same' => 'nullable',
        ];

        $isSame = $request->has('shipping_and_billing_same') ? $request->boolean('shipping_and_billing_same') : false;

        if (!$isSame) {
            $rules['billing_first_name'] = 'required|string|max:100';
            $rules['billing_last_name'] = 'required|string|max:100';
            $rules['billing_address'] = 'required|string|max:255';
            $rules['billing_city'] = 'required|string|max:100';
            $rules['billing_state'] = 'required|string|max:100';
            $rules['billing_zip'] = 'required|string|max:20';
            $rules['billing_country'] = 'required|string|max:100';
            $rules['billing_phone'] = 'required|string|max:20';
        }

        $request->validate($rules);

        $address = auth('customer')->user()->addresses()->find($id);
        if (! $address) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Address not found.'], 404);
            }

            return redirect()->back()->with('error', 'Address not found.');
        }

        $address->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'zip' => $request->zip,
            'country' => $request->country,
            'phone' => $request->phone,
            'alternate_phone' => $request->alternate_phone,
            'address_type' => $request->address_type,
            'landmark' => $request->landmark,
            'shipping_and_billing_same' => $isSame,
            'billing_first_name' => $isSame ? $request->first_name : $request->billing_first_name,
            'billing_last_name' => $isSame ? $request->last_name : $request->billing_last_name,
            'billing_address' => $isSame ? $request->address : $request->billing_address,
            'billing_city' => $isSame ? $request->city : $request->billing_city,
            'billing_state' => $isSame ? $request->state : $request->billing_state,
            'billing_zip' => $isSame ? $request->zip : $request->billing_zip,
            'billing_country' => $isSame ? $request->country : $request->billing_country,
            'billing_phone' => $isSame ? $request->phone : $request->billing_phone,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Address updated successfully!',
                'address' => $address,
            ]);
        }

        return redirect()->route('store.account', 'address')->with('success', 'Address updated successfully!');
    }

    /**
     * Get order details for modal view.
     */
    public function getOrderDetail($ulid)
    {
        if (! auth('customer')->check()) {
            return response()->json(['success' => false, 'message' => 'Please log in to view order details.'], 401);
        }

        $order = auth('customer')->user()->orders()
            ->with(['items.product'])
            ->where('ulid', $ulid)
            ->first();

        if (! $order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        // Format for JSON response
        $items = [];
        foreach ($order->items as $item) {
            $items[] = [
                'name' => $item->product_name,
                'sku' => $item->product_sku,
                'price' => (float) $item->price,
                'quantity' => (int) $item->quantity,
                'size' => $item->size,
                'color' => $item->color,
                'img' => $item->product && $item->product->image
                    ? Storage::url($item->product->image)
                    : asset('images/no-image.svg'),
            ];
        }

        return response()->json([
            'success' => true,
            'order' => [
                'order_number' => $order->order_number,
                'date' => $order->created_at->format('M d, Y h:i A'),
                'status' => $order->status->value ?? $order->status,
                'payment_status' => $order->payment_status->value ?? $order->payment_status,
                'payment_method' => $order->payment_method,
                'card_number_masked' => $order->card_number_masked,
                'recipient_name' => $order->first_name.' '.$order->last_name,
                'address' => $order->address,
                'city' => $order->city,
                'state' => $order->state,
                'zip' => $order->zip,
                'country' => $order->country,
                'subtotal' => (float) $order->subtotal,
                'discount' => (float) $order->discount,
                'tax' => (float) $order->tax,
                'total' => (float) $order->total,
                'items' => $items,
            ],
        ]);
    }

    /**
     * View a single order in a dedicated storefront page.
     */
    public function viewOrder($ulid)
    {
        if (! auth('customer')->check()) {
            return redirect()->route('store.login')->with('error', 'Please log in to view order details.');
        }

        $relations = ['items.product'];
        if (class_exists(\SGCart\CrmTickets\Models\Ticket::class)) {
            $relations[] = 'tickets.status';
        }

        $order = auth('customer')->user()->orders()
            ->with($relations)
            ->where('ulid', $ulid)
            ->first();

        if (! $order) {
            return redirect()->route('store.account', 'orders')->with('error', 'Order not found.');
        }

        return view('store.order-details', compact('order'));
    }

    /**
     * Generate and download PDF Invoice.
     */
    public function downloadInvoice($ulid)
    {
        if (! auth('customer')->check()) {
            return redirect()->route('store.login')->with('error', 'Please log in to view/download invoices.');
        }

        $order = auth('customer')->user()->orders()
            ->with(['items.product'])
            ->where('ulid', $ulid)
            ->first();

        if (! $order) {
            return redirect()->route('store.account', 'orders')->with('error', 'Order not found.');
        }

        $pdf = Pdf::loadView('store.invoice-pdf', compact('order'));

        return $pdf->download("invoice-{$order->order_number}.pdf");
    }
}
