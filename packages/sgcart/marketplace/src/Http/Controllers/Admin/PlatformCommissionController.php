<?php

namespace SGCart\Marketplace\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use SGCart\Marketplace\Models\SellerCommission;

class PlatformCommissionController extends Controller
{
    /**
     * Display a ledger of all seller commissions.
     */
    public function index()
    {
        $ledger = SellerCommission::with(['order', 'orderItem', 'seller'])
            ->latest()
            ->paginate(15);

        // Totals
        $allCommissions = SellerCommission::where('status', '!=', 'cancelled')->get();
        $totalSales = $allCommissions->sum('subtotal');
        $totalCommissions = $allCommissions->sum('commission_amount');
        $totalSellerEarnings = $allCommissions->sum('seller_earning');

        return view('marketplace::admin.commissions.index', compact(
            'ledger',
            'totalSales',
            'totalCommissions',
            'totalSellerEarnings'
        ));
    }
}
