<?php

namespace SGCart\Marketplace\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use SGCart\Marketplace\Models\SellerCommission;
use SGCart\Marketplace\Models\SellerPayout;
use Illuminate\Http\Request;
use App\Helpers\NotificationHelper;

class CommissionController extends Controller
{
    /**
     * Display the seller's earnings and commissions ledger.
     */
    public function index(Request $request)
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

        // Calculate totals globally (unfiltered)
        $allCommissions = SellerCommission::where('seller_id', $sellerId)
            ->where('status', '!=', 'cancelled')
            ->get();

        $totalEarnings = $allCommissions->sum('seller_earning');
        $totalCommissionPaid = $allCommissions->sum('commission_amount');
        $totalSales = $allCommissions->sum('subtotal');

        // Accounting breakdown
        $pendingBalance = $allCommissions->where('status', 'pending')->sum('seller_earning');
        $allocatedBalance = $allCommissions->where('status', 'allocated')->sum('seller_earning');
        $settledBalance = $allCommissions->where('status', 'paid')->sum('seller_earning');
        $lifetimeEarnings = $allCommissions->sum('seller_earning');

        return view('marketplace::seller.commissions.index', compact(
            'ledger',
            'totalEarnings',
            'totalCommissionPaid',
            'totalSales',
            'pendingBalance',
            'allocatedBalance',
            'settledBalance',
            'lifetimeEarnings'
        ));
    }

    /**
     * Show form to edit seller account details.
     */
    public function showAccountDetails()
    {
        $seller = auth('seller')->user();
        return view('marketplace::seller.account_details', compact('seller'));
    }

    /**
     * Update seller account details.
     */
    public function updateAccountDetails(Request $request)
    {
        $data = $request->validate([
            'account_holder_name' => 'required|string|max:255',
            'bank_name'           => 'required|string|max:255',
            'account_number'      => 'required|string|regex:/^[0-9]{9,18}$/',
            'ifsc_code'           => 'required|string|regex:/^[A-Z]{4}0[A-Z0-9]{6}$/i',
            'branch_name'         => 'required|string|max:255',
            'upi_id'              => 'nullable|string|regex:/^[\w.\-_]{2,256}@[a-zA-Z]{2,64}$/',
        ], [
            'account_number.regex' => 'The account number must be between 9 and 18 digits and contain only numbers.',
            'ifsc_code.regex'      => 'Please enter a valid 11-digit Indian Financial System Code (IFSC) (e.g., SBIN0001234).',
            'upi_id.regex'         => 'Please enter a valid UPI ID (e.g., name@upi).',
        ]);

        $seller = auth('seller')->user();
        $oldDetails = $seller->account_details ?? [];
        $changed = false;
        
        foreach ($data as $key => $val) {
            if (($oldDetails[$key] ?? '') !== $val) {
                $changed = true;
                break;
            }
        }

        if ($changed || $seller->account_verification_status === 'unsubmitted' || $seller->account_verification_status === 'rejected') {
            $seller->update([
                'account_details'             => $data,
                'account_verification_status' => 'pending',
                'account_rejection_reason'    => null,
            ]);

            NotificationHelper::sendToAdmin(
                'Payment Account Submitted',
                "The seller '{$seller->shop_name}' has submitted their payout bank account details for verification.",
                route('admin.sellers.show', $seller->ulid),
                'info',
                'fa-building-columns'
            );

            return redirect()->back()->with('success', 'Account details submitted successfully and are pending admin verification.');
        }

        return redirect()->back()->with('info', 'No changes detected in account details.');
    }

    /**
     * Display list of payouts sent to the seller.
     */
    public function payouts(Request $request)
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

        $payouts = SellerPayout::where('seller_id', $sellerId)
            ->when($startDate, fn($q) => $q->where('payout_date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->where('payout_date', '<=', $endDate))
            ->when($search, function($q) use ($search) {
                $q->where(function($sq) use ($search) {
                    $sq->where('transaction_reference', 'like', "%{$search}%")
                       ->orWhere('payment_method', 'like', "%{$search}%")
                       ->orWhere('admin_notes', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('marketplace::seller.payouts.index', compact('payouts'));
    }
}
