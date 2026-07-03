<?php

namespace SGCart\Marketplace\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Order;
use SGCart\Marketplace\Models\SellerCommission;

class DashboardController extends Controller
{
    /**
     * Display the seller dashboard.
     */
    public function index()
    {
        $sellerId = auth('seller')->id();

        // Standard metrics (automatically scoped by our Eloquent global scopes!)
        $totalProductsCount = Product::count();
        $totalOrdersCount = Order::count();

        // Calculate commissions
        $commissions = SellerCommission::where('seller_id', $sellerId)
            ->where('status', '!=', 'cancelled')
            ->get();

        $totalEarnings = $commissions->sum('seller_earning');
        $totalSales = $commissions->sum('subtotal');

        // Recent orders (scoped)
        $recentOrders = Order::latest()->take(5)->get();

        return view('marketplace::seller.dashboard.index', compact(
            'totalProductsCount',
            'totalOrdersCount',
            'totalEarnings',
            'totalSales',
            'recentOrders'
        ));
    }
}
