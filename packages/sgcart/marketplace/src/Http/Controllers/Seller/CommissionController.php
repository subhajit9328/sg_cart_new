<?php

namespace SGCart\Marketplace\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use SGCart\Marketplace\Models\SellerCommission;

class CommissionController extends Controller
{
    /**
     * Display the seller's earnings and commissions ledger.
     */
    public function index()
    {
        $sellerId = auth()->id();

        // Fetch commissions/earnings ledger
        $ledger = SellerCommission::with(['order', 'orderItem'])
            ->where('seller_id', $sellerId)
            ->latest()
            ->paginate(15);

        // Calculate totals
        $allCommissions = SellerCommission::where('seller_id', $sellerId)
            ->where('status', '!=', 'cancelled')
            ->get();

        $totalEarnings = $allCommissions->sum('seller_earning');
        $totalCommissionPaid = $allCommissions->sum('commission_amount');
        $totalSales = $allCommissions->sum('subtotal');

        return view('marketplace::seller.commissions.index', compact(
            'ledger',
            'totalEarnings',
            'totalCommissionPaid',
            'totalSales'
        ));
    }
}
