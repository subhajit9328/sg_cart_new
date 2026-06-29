<?php

namespace SGCart\LogisticTracking\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use SGCart\LogisticTracking\Models\ShippingCourier;

class ShippingCourierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $couriers = ShippingCourier::latest()->get();
        return view('logistic-tracking::shipping-couriers.index', compact('couriers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('logistic-tracking::shipping-couriers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'nullable|url|max:255',
            'support_email' => 'nullable|email|max:255',
        ]);

        ShippingCourier::create($data);

        return redirect()->route('admin.couriers.index')
            ->with('success', 'Shipping courier added successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ShippingCourier $courier)
    {
        $courier->delete();

        return redirect()->route('admin.couriers.index')
            ->with('success', 'Shipping courier deleted successfully.');
    }
}
