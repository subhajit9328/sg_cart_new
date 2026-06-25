<?php

namespace SGCart\Coupons\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use SGCart\Coupons\Models\Coupon;
use SGCart\Coupons\Enums\CouponType;
use Illuminate\Support\Carbon;

class CouponController extends Controller
{
    /**
     * Display a listing of coupons (Admin).
     */
    public function index()
    {
        $coupons = Coupon::orderBy('id', 'desc')->paginate(10);
        return view('coupons::index', compact('coupons'));
    }

    /**
     * Show the form for creating a new coupon (Admin).
     */
    public function create()
    {
        $types = CouponType::cases();
        return view('coupons::create', compact('types'));
    }

    /**
     * Store a newly created coupon in storage (Admin).
     */
    public function store(Request $request)
    {
        $rules = [
            'code' => 'required|string|max:50|unique:coupons,code',
            'type' => 'required|string',
            'value' => 'required|numeric|min:0.01',
            'is_active' => 'nullable|boolean',
        ];

        if (config('coupons.features.min_cart_total', true)) {
            $rules['min_cart_total'] = 'nullable|numeric|min:0';
        }
        if (config('coupons.features.expires_at', true)) {
            $rules['expires_at'] = 'nullable|date|after_or_equal:today';
        }

        $request->validate($rules);

        Coupon::create([
            'code' => strtoupper(trim($request->code)),
            'type' => $request->type,
            'value' => $request->value,
            'min_cart_total' => config('coupons.features.min_cart_total', true) ? ($request->min_cart_total ?? 0.00) : 0.00,
            'expires_at' => (config('coupons.features.expires_at', true) && $request->expires_at) ? Carbon::parse($request->expires_at)->endOfDay() : null,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon created successfully!');
    }

    /**
     * Show the form for editing the specified coupon (Admin).
     */
    public function edit($id)
    {
        $coupon = Coupon::findOrFail($id);
        $types = CouponType::cases();
        return view('coupons::edit', compact('coupon', 'types'));
    }

    /**
     * Update the specified coupon in storage (Admin).
     */
    public function update(Request $request, $id)
    {
        $coupon = Coupon::findOrFail($id);

        $rules = [
            'code' => 'required|string|max:50|unique:coupons,code,' . $coupon->id,
            'type' => 'required|string',
            'value' => 'required|numeric|min:0.01',
            'is_active' => 'nullable|boolean',
        ];

        if (config('coupons.features.min_cart_total', true)) {
            $rules['min_cart_total'] = 'nullable|numeric|min:0';
        }
        if (config('coupons.features.expires_at', true)) {
            $rules['expires_at'] = 'nullable|date';
        }

        $request->validate($rules);

        $coupon->update([
            'code' => strtoupper(trim($request->code)),
            'type' => $request->type,
            'value' => $request->value,
            'min_cart_total' => config('coupons.features.min_cart_total', true) ? ($request->min_cart_total ?? 0.00) : 0.00,
            'expires_at' => (config('coupons.features.expires_at', true) && $request->expires_at) ? Carbon::parse($request->expires_at)->endOfDay() : null,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon updated successfully!');
    }

    /**
     * Remove the specified coupon from storage (Admin).
     */
    public function destroy($id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->delete();

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon deleted successfully!');
    }

    /**
     * Apply coupon code (Storefront).
     */
    public function applyCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50',
        ]);

        $code = strtoupper(trim($request->code));
        $coupon = Coupon::where('code', $code)->first();

        // Validate coupon existence & validity status
        if (!$coupon || !$coupon->isValid()) {
            return redirect()->back()->with('error', 'Invalid or expired coupon code.');
        }

        // Validate subtotal against coupon requirement
        if (config('coupons.features.min_cart_total', true)) {
            $cart = session()->get('cart', []);
            $subtotal = 0;
            foreach ($cart as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }

            if ($subtotal < (float) $coupon->min_cart_total) {
                return redirect()->back()->with('error', 'Minimum cart total of $' . number_format($coupon->min_cart_total, 2) . ' required to use this coupon.');
            }
        }

        // Store active coupon code in session
        session()->put('coupon_code', $coupon->code);

        return redirect()->back()->with('success', 'Coupon code "' . $coupon->code . '" applied successfully!');
    }
}
