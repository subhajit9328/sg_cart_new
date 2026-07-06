<?php

namespace SGCart\Marketplace\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use SGCart\Marketplace\Models\SellerCommission;

class CommissionController extends Controller
{
    /**
     * Display the seller's earnings and commissions ledger.
     */
    public function index(\Illuminate\Http\Request $request)
    {
        $sellerId = auth()->id();
        $dateRange = $request->input('date_range');
        $startDate = null;
        $endDate = null;

        if ($dateRange) {
            $parts = explode(' - ', $dateRange);
            $startPart = isset($parts[0]) ? trim($parts[0]) : null;
            $endPart = isset($parts[1]) ? trim($parts[1]) : $startPart;

            try {
                $startDate = $startPart ? \Carbon\Carbon::createFromFormat('d-m-Y', $startPart)->startOfDay() : null;
                $endDate = $endPart ? \Carbon\Carbon::createFromFormat('d-m-Y', $endPart)->endOfDay() : null;
            } catch (\Exception $e) {
                try {
                    $startDate = $startPart ? \Carbon\Carbon::parse($startPart)->startOfDay() : null;
                    $endDate = $endPart ? \Carbon\Carbon::parse($endPart)->endOfDay() : null;
                } catch (\Exception $ex) {
                    $startDate = null;
                    $endDate = null;
                }
            }
        }

        $search = $request->input('search');

        // Fetch commissions/earnings ledger
        $ledger = SellerCommission::with(['order', 'orderItem'])
            ->where('seller_id', $sellerId)
            ->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->where('created_at', '<=', $endDate))
            ->when($search, function($q) use ($search) {
                $q->where(function($sq) use ($search) {
                    $sq->whereHas('order', fn($oq) => $oq->where('order_number', 'like', "%{$search}%")->orWhere('id', 'like', "%{$search}%"))
                       ->orWhereHas('orderItem', fn($oiq) => $oiq->where('product_name', 'like', "%{$search}%")->orWhere('product_sku', 'like', "%{$search}%"))
                       ->orWhere('status', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        // Calculate totals
        $allCommissions = SellerCommission::where('seller_id', $sellerId)
            ->where('status', '!=', 'cancelled')
            ->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->where('created_at', '<=', $endDate))
            ->when($search, function($q) use ($search) {
                $q->where(function($sq) use ($search) {
                    $sq->whereHas('order', fn($oq) => $oq->where('order_number', 'like', "%{$search}%")->orWhere('id', 'like', "%{$search}%"))
                       ->orWhereHas('orderItem', fn($oiq) => $oiq->where('product_name', 'like', "%{$search}%")->orWhere('product_sku', 'like', "%{$search}%"))
                       ->orWhere('status', 'like', "%{$search}%");
                });
            })
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
