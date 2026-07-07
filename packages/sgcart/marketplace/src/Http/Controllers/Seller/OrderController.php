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
        $status = $request->input('status');
        $dateRange = $request->input('date_range');
        $startDate = null;
        $endDate = null;

        if ($dateRange) {
            $parts = explode(' - ', $dateRange);
            $startPart = isset($parts[0]) ? trim($parts[0]) : null;
            $endPart = isset($parts[1]) ? trim($parts[1]) : $startPart;

            try {
                $startDate = $startPart ? \Carbon\Carbon::createFromFormat('d-m-Y h:i A', $startPart)->format('Y-m-d H:i:s') : null;
                $endDate = $endPart ? \Carbon\Carbon::createFromFormat('d-m-Y h:i A', $endPart)->format('Y-m-d H:i:s') : $startDate;
            } catch (\Exception $e) {
                try {
                    $startDate = $startPart ? \Carbon\Carbon::createFromFormat('d-m-Y H:i', $startPart)->format('Y-m-d H:i:s') : null;
                    $endDate = $endPart ? \Carbon\Carbon::createFromFormat('d-m-Y H:i', $endPart)->format('Y-m-d H:i:s') : $startDate;
                } catch (\Exception $ex) {
                    try {
                        $startDate = $startPart ? \Carbon\Carbon::createFromFormat('d-m-Y', $startPart)->startOfDay()->format('Y-m-d H:i:s') : null;
                        $endDate = $endPart ? \Carbon\Carbon::createFromFormat('d-m-Y', $endPart)->endOfDay()->format('Y-m-d H:i:s') : $startDate;
                    } catch (\Exception $ex2) {
                        try {
                            $startDate = $startPart ? \Carbon\Carbon::parse($startPart)->format('Y-m-d H:i:s') : null;
                            $endDate = $endPart ? \Carbon\Carbon::parse($endPart)->format('Y-m-d H:i:s') : $startDate;
                        } catch (\Exception $ex3) {
                            $startDate = null;
                            $endDate = null;
                        }
                    }
                }
            }
        }

        // Scoped automatically by Eloquent global scope!
        $orders = Order::with(['items', 'customer'])
            ->when($search, fn($q) => $q->where('id', $search)->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$search}%")))
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->where('created_at', '<=', $endDate))
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
