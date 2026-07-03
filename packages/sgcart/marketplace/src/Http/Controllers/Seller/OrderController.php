<?php

namespace SGCart\Marketplace\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of orders containing the seller's products.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        // Scoped automatically by Eloquent global scope!
        $orders = Order::with(['items', 'customer'])
            ->when($search, fn($q) => $q->where('id', $search)->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$search}%")))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('marketplace::seller.orders.index', compact('orders'));
    }

    /**
     * Display the specified order's details (restricted to seller's items).
     */
    public function show(Order $order)
    {
        // Eloquent global scope automatically filters the loaded items!
        $order->load(['items.product.images', 'customer', 'payments']);
        
        $commissions = \SGCart\Marketplace\Models\SellerCommission::where('order_id', $order->id)
            ->where('seller_id', auth('seller')->id())
            ->get()
            ->keyBy('order_item_id');
        
        return view('marketplace::seller.orders.show', compact('order', 'commissions'));
    }
}
