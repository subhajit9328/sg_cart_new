<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
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
                'stock' => $p->stock,
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
        $cartModel = \App\Models\Cart::getActiveCart();
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

        $product = \App\Models\Product::find($productId);
        if (!$product || $product->status !== 'active') {
            return redirect()->back()->with('error', 'Product not found.');
        }

        $cartModel = \App\Models\Cart::getActiveCart();

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
        $cartModel = \App\Models\Cart::getActiveCart();
        $quantities = $request->input('quantities', []);
        $wasLimited = false;

        foreach ($quantities as $key => $qty) {
            $parts = explode('_', $key);
            if (count($parts) >= 1) {
                $productId = (int)$parts[0];
                $size = isset($parts[1]) && $parts[1] !== '' ? $parts[1] : null;
                $color = isset($parts[2]) && $parts[2] !== '' ? $parts[2] : null;

                $cartItem = $cartModel->items()
                    ->where('product_id', $productId)
                    ->where('size', $size)
                    ->where('color', $color)
                    ->first();

                if ($cartItem) {
                    if ((int)$qty <= 0) {
                        $cartItem->delete();
                    } else {
                        $product = \App\Models\Product::find($productId);
                        $currentStock = $product ? ($product->stock ?? 0) : 0;
                        $requestedQty = (int)$qty;

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
        $cartModel = \App\Models\Cart::getActiveCart();

        $parts = explode('_', $key);
        if (count($parts) >= 1) {
            $productId = (int)$parts[0];
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
        if (!auth('customer')->check()) {
            return redirect()->route('store.login')->with('error', 'Please log in to proceed to checkout.');
        }

        $cartModel = \App\Models\Cart::getActiveCart();
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

        return view('store.checkout', compact('cart', 'subtotal', 'tax', 'taxLabel', 'discount', 'total', 'addresses', 'shippingRates', 'shippingCost', 'selectionMode', 'hasShippingPackage'));
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
            'card_name' => 'required|string|max:255',
            'card_num' => 'required|string|max:19',
            'card_expiry' => 'required|string|max:5',
            'card_cvv' => 'required|string|max:4',
        ]);

        $cartModel = \App\Models\Cart::getActiveCart();
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
            if (!$savedAddress) {
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

        // Generate sequential order number starting from SG-100000
        $lastOrder = \App\Models\Order::orderBy('id', 'desc')->first();
        if ($lastOrder) {
            $lastNum = 100000;
            if (preg_match('/SG-(\d+)/', $lastOrder->order_number, $matches)) {
                $lastNum = (int)$matches[1];
            }
            $orderNumber = 'SG-' . ($lastNum + 1);
        } else {
            $orderNumber = 'SG-100000';
        }

        // Create Order in Database
        $order = \App\Models\Order::create([
            'order_number' => $orderNumber,
            'customer_id' => auth('customer')->id(),
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
            'status' => \App\Enums\OrderStatus::PROCESSING,
            'payment_status' => \App\Enums\PaymentStatus::PAID,
            'payment_method' => 'Card',
            'card_name' => $request->card_name,
            // Mask card number for PCI compliance standard
            'card_number_masked' => '**** **** **** ' . substr(str_replace(' ', '', $request->card_num), -4),
            'payment_transaction_id' => 'TXN-' . strtoupper(Str::random(12)),
            'payment_gateway' => 'SGCart Gateway (Stripe Sim)',
        ]);

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

        // Clear Database Cart
        $cartModel->items()->delete();

        // Clear coupon info from session
        session()->forget('coupon_code');
        session()->forget('coupon_discount');

        return redirect()->route('store.success', ['order_id' => $order->order_number]);
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

        // Load real orders from the database
        $orders = auth('customer')->user()->orders()
            ->with('items')
            ->latest()
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->order_number,
                    'ulid' => $order->ulid,
                    'items_count' => $order->items->sum('quantity'),
                    'date' => $order->created_at->format('M d, Y'),
                    'amount' => $order->total,
                    'status' => $order->status->value ?? $order->status,
                ];
            })
            ->toArray();

        $wishlistIds = session()->get('wishlist', [3, 5, 6]);
        $allProducts = self::getProducts();
        $wishlist = array_filter($allProducts, fn($p) => in_array($p['id'], $wishlistIds));

        $addresses = auth('customer')->user()->addresses;

        return view('store.account', compact('orders', 'wishlist', 'activeTab', 'addresses'));
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

    /**
     * Add new customer address.
     */
    public function addAddress(Request $request)
    {
        if (!auth('customer')->check()) {
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
                'address' => $address
            ]);
        }

        return redirect()->route('store.account', 'address')->with('success', 'Address added successfully!');
    }

    /**
     * Delete customer address.
     */
    public function deleteAddress(Request $request, $id)
    {
        if (!auth('customer')->check()) {
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
                'message' => 'Address deleted successfully!'
            ]);
        }

        return redirect()->route('store.account', 'address')->with('success', 'Address deleted successfully!');
    }

    /**
     * Update customer address.
     */
    public function updateAddress(Request $request, $id)
    {
        if (!auth('customer')->check()) {
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
        if (!$address) {
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
                'address' => $address
            ]);
        }

        return redirect()->route('store.account', 'address')->with('success', 'Address updated successfully!');
    }

    /**
     * Get order details for modal view.
     */
    public function getOrderDetail($ulid)
    {
        if (!auth('customer')->check()) {
            return response()->json(['success' => false, 'message' => 'Please log in to view order details.'], 401);
        }

        $order = auth('customer')->user()->orders()
            ->with(['items.product'])
            ->where('ulid', $ulid)
            ->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        // Format for JSON response
        $items = [];
        foreach ($order->items as $item) {
            $items[] = [
                'name' => $item->product_name,
                'sku' => $item->product_sku,
                'price' => (float)$item->price,
                'quantity' => (int)$item->quantity,
                'size' => $item->size,
                'color' => $item->color,
                'img' => $item->product && $item->product->image 
                    ? \Illuminate\Support\Facades\Storage::url($item->product->image) 
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
                'recipient_name' => $order->first_name . ' ' . $order->last_name,
                'address' => $order->address,
                'city' => $order->city,
                'state' => $order->state,
                'zip' => $order->zip,
                'country' => $order->country,
                'subtotal' => (float)$order->subtotal,
                'discount' => (float)$order->discount,
                'tax' => (float)$order->tax,
                'total' => (float)$order->total,
                'items' => $items,
            ]
        ]);
    }

    /**
     * View a single order in a dedicated storefront page.
     */
    public function viewOrder($ulid)
    {
        if (!auth('customer')->check()) {
            return redirect()->route('store.login')->with('error', 'Please log in to view order details.');
        }

        $order = auth('customer')->user()->orders()
            ->with(['items.product'])
            ->where('ulid', $ulid)
            ->first();

        if (!$order) {
            return redirect()->route('store.account', 'orders')->with('error', 'Order not found.');
        }

        return view('store.order-details', compact('order'));
    }

    /**
     * Generate and download PDF Invoice.
     */
    public function downloadInvoice($ulid)
    {
        if (!auth('customer')->check()) {
            return redirect()->route('store.login')->with('error', 'Please log in to view/download invoices.');
        }

        $order = auth('customer')->user()->orders()
            ->with(['items.product'])
            ->where('ulid', $ulid)
            ->first();

        if (!$order) {
            return redirect()->route('store.account', 'orders')->with('error', 'Order not found.');
        }

        $pdf = Pdf::loadView('store.invoice-pdf', compact('order'));
        
        return $pdf->download("invoice-{$order->order_number}.pdf");
    }
}
