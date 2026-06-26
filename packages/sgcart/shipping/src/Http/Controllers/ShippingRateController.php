<?php

namespace SGCart\Shipping\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use SGCart\Shipping\Models\ShippingRate;
use SGCart\Shipping\Models\ShippingSetting;

class ShippingRateController extends Controller
{
    /**
     * Display a listing of shipping rates (Admin).
     */
    public function index()
    {
        $rates = ShippingRate::orderBy('id', 'desc')->paginate(10);
        $selectionMode = ShippingSetting::getVal('shipping_selection_mode', 'user_choice');
        return view('shipping::index', compact('rates', 'selectionMode'));
    }

    /**
     * Show the form for creating a new shipping rate (Admin).
     */
    public function create()
    {
        return view('shipping::create');
    }

    /**
     * Store a newly created shipping rate in storage (Admin).
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:flat,percent',
            'cost' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        ShippingRate::create([
            'name' => trim($request->name),
            'type' => $request->type,
            'cost' => $request->cost,
            'min_order_amount' => $request->min_order_amount ?? 0.00,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.shipping.index')->with('success', 'Shipping rate created successfully!');
    }

    /**
     * Show the form for editing the specified shipping rate (Admin).
     */
    public function edit($id)
    {
        $rate = ShippingRate::findOrFail($id);
        return view('shipping::edit', compact('rate'));
    }

    /**
     * Update the specified shipping rate in storage (Admin).
     */
    public function update(Request $request, $id)
    {
        $rate = ShippingRate::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:flat,percent',
            'cost' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $rate->update([
            'name' => trim($request->name),
            'type' => $request->type,
            'cost' => $request->cost,
            'min_order_amount' => $request->min_order_amount ?? 0.00,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.shipping.index')->with('success', 'Shipping rate updated successfully!');
    }

    /**
     * Remove the specified shipping rate from storage (Admin).
     */
    public function destroy($id)
    {
        $rate = ShippingRate::findOrFail($id);
        $rate->delete();

        return redirect()->route('admin.shipping.index')->with('success', 'Shipping rate deleted successfully!');
    }

    /**
     * Update shipping global settings (Admin).
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'shipping_selection_mode' => 'required|string|in:user_choice,auto_cheapest',
        ]);

        ShippingSetting::setVal('shipping_selection_mode', $request->shipping_selection_mode);

        return redirect()->route('admin.shipping.index')->with('success', 'Shipping configuration settings updated successfully!');
    }
}
