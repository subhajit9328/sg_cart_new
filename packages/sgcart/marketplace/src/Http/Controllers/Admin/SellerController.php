<?php

namespace SGCart\Marketplace\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use SGCart\Marketplace\Models\Seller;
use SGCart\Marketplace\Models\SellerCommission;
use SGCart\Marketplace\Models\SellerPayout;
use SGCart\Marketplace\Mail\SellerApprovedMail;
use SGCart\Marketplace\Mail\SellerRejectedMail;
use SGCart\Marketplace\Mail\SellerSuspendedMail;
use SGCart\Marketplace\Mail\PayoutDisbursedMail;
use App\Helpers\NotificationHelper;

class SellerController extends Controller
{
    /**
     * Display a listing of registered sellers.
     */
    public function index(Request $request)
    {
        $status = $request->input('status');

        $sellers = Seller::where('status', '!=', 'pending_onboarding')
            ->when($status, fn($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('marketplace::admin.sellers.index', compact('sellers'));
    }

    /**
     * Show detailed profile of a specific seller.
     */
    public function show(Seller $seller)
    {
        // Resolve products scoped under this seller
        $products = $seller->products()->latest()->paginate(10);
        
        $commissions = SellerCommission::where('seller_id', $seller->id)
            ->where('status', '!=', 'cancelled')
            ->get();

        $totalSales = $commissions->sum('subtotal');
        $totalCommissionCollected = $commissions->sum('commission_amount');
        $totalEarnings = $commissions->sum('seller_earning');

        $pendingBalance = $commissions->where('status', 'pending')->sum('seller_earning');
        $allocatedBalance = $commissions->where('status', 'allocated')->sum('seller_earning');
        $settledBalance = $commissions->where('status', 'paid')->sum('seller_earning');

        // Fetch seller payouts
        $payouts = SellerPayout::where('seller_id', $seller->id)
            ->latest()
            ->get();

        return view('marketplace::admin.sellers.show', compact(
            'seller',
            'products',
            'totalSales',
            'totalCommissionCollected',
            'totalEarnings',
            'pendingBalance',
            'allocatedBalance',
            'settledBalance',
            'payouts'
        ));
    }

    /**
     * Approve a seller registration.
     */
    public function approve(Seller $seller)
    {
        $seller->update([
            'status' => \App\Enums\SellerStatus::APPROVED,
            'approved_at' => now(),
            'approved_by' => Auth::id(),
            'suspension_reason' => null,
        ]);

        NotificationHelper::sendToSeller(
            $seller,
            'Shop Approved!',
            'Congratulations! Your shop profile has been approved by administrators. You can now list and sell products.',
            route('seller.dashboard'),
            'success',
            'fa-circle-check'
        );

        if ($seller->email && !str_starts_with($seller->email, 'temp_')) {
            try {
                Mail::to($seller->email)->send(new SellerApprovedMail($seller));
            } catch (\Exception $e) {
                Log::error('Failed to send seller approved email: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', "Seller '{$seller->shop_name}' approved successfully.");
    }

    /**
     * Suspend a seller account.
     */
    public function suspend(Request $request, Seller $seller)
    {
        $request->validate([
            'suspension_reason' => 'required|string|max:1000',
        ]);

        $seller->update([
            'status' => \App\Enums\SellerStatus::SUSPENDED,
            'suspension_reason' => $request->suspension_reason,
        ]);

        NotificationHelper::sendToSeller(
            $seller,
            'Shop Suspended',
            'Your shop account has been suspended by an administrator. Reason: ' . $request->suspension_reason,
            '#',
            'danger',
            'fa-triangle-exclamation'
        );

        if ($seller->email && !str_starts_with($seller->email, 'temp_')) {
            try {
                Mail::to($seller->email)->send(new SellerSuspendedMail($seller));
            } catch (\Exception $e) {
                Log::error('Failed to send seller suspended email: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', "Seller '{$seller->shop_name}' suspended successfully.");
    }

    /**
     * Reject a seller registration.
     */
    public function reject(Request $request, Seller $seller)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $seller->update([
            'status' => \App\Enums\SellerStatus::REJECTED,
            'suspension_reason' => $request->rejection_reason,
        ]);

        NotificationHelper::sendToSeller(
            $seller,
            'Shop Registration Rejected',
            'Your shop registration application was rejected. Reason: ' . $request->rejection_reason,
            '#',
            'danger',
            'fa-circle-xmark'
        );

        if ($seller->email && !str_starts_with($seller->email, 'temp_')) {
            try {
                Mail::to($seller->email)->send(new SellerRejectedMail($seller));
            } catch (\Exception $e) {
                Log::error('Failed to send seller rejected email: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', "Seller '{$seller->shop_name}' application rejected.");
    }

    /**
     * Update the commission rate override for a seller.
     */
    public function updateCommission(Request $request, Seller $seller)
    {
        $request->validate([
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $seller->update([
            'commission_rate' => $request->commission_rate,
        ]);

        return redirect()->back()->with('success', 'Seller commission rate updated successfully.');
    }

    /**
     * Approve seller account details.
     */
    public function approveAccount(Seller $seller)
    {
        $seller->update([
            'account_verification_status' => 'verified',
            'account_rejection_reason'    => null,
        ]);

        NotificationHelper::sendToSeller(
            $seller,
            'Payment Account Verified',
            'Your bank/payout account details have been successfully verified. You are now eligible to receive payouts.',
            route('seller.account-details'),
            'success',
            'fa-building-columns'
        );

        return redirect()->back()->with('success', 'Seller account details approved successfully.');
    }

    /**
     * Reject seller account details.
     */
    public function rejectAccount(Request $request, Seller $seller)
    {
        $request->validate([
            'account_rejection_reason' => 'required|string|max:1000',
        ]);

        $seller->update([
            'account_verification_status' => 'rejected',
            'account_rejection_reason'    => $request->account_rejection_reason,
        ]);

        NotificationHelper::sendToSeller(
            $seller,
            'Payment Account Rejected',
            'Your bank/payout account details were rejected. Reason: ' . $request->account_rejection_reason,
            route('seller.account-details'),
            'danger',
            'fa-circle-xmark'
        );

        return redirect()->back()->with('success', 'Seller account details rejected.');
    }

    /**
     * Allow seller to edit their account details (unlock edit lock).
     */
    public function allowAccountEdit(Seller $seller)
    {
        $seller->update([
            'account_verification_status' => 'unsubmitted',
            'account_rejection_reason'    => null,
        ]);

        NotificationHelper::sendToSeller(
            $seller,
            'Payment Details Unlocked',
            'Your bank/payout account details edit lock has been released. You can now edit and re-submit your details.',
            route('seller.account-details'),
            'info',
            'fa-unlock'
        );

        return redirect()->back()->with('success', 'Seller account details edit lock released successfully.');
    }

    /**
     * Record a payout to the seller.
     */
    public function recordPayout(Request $request, Seller $seller)
    {
        if ($seller->account_verification_status !== 'verified') {
            return redirect()->back()->withErrors(['payout_error' => 'Payouts can only be processed for sellers with verified payment accounts.']);
        }

        // Calculate allocated withdrawable balance
        $allocatedBalance = SellerCommission::where('seller_id', $seller->id)
            ->where('status', 'allocated')
            ->sum('seller_earning');

        $request->validate([
            'amount'                => ['required', 'numeric', 'min:0.01', 'max:' . $allocatedBalance],
            'payment_method'        => ['required', 'string', 'in:bank_transfer,upi,cash,other'],
            'transaction_reference' => ['nullable', 'string', 'max:255'],
            'admin_notes'           => ['nullable', 'string', 'max:1000'],
        ]);

        $amount = (float) $request->amount;

        $payout = DB::transaction(function () use ($seller, $amount, $request) {
            // Create payout
            $payout = SellerPayout::create([
                'seller_id'             => $seller->id,
                'amount'                => $amount,
                'payment_method'        => $request->payment_method,
                'transaction_reference' => $request->transaction_reference,
                'admin_notes'           => $request->admin_notes,
                'payout_date'           => now(),
            ]);

            // Retrieve allocated commissions to mark as paid
            $commissions = SellerCommission::where('seller_id', $seller->id)
                ->where('status', 'allocated')
                ->orderBy('created_at', 'asc')
                ->get();

            $remaining = $amount;
            foreach ($commissions as $comm) {
                if ($remaining <= 0) {
                    break;
                }

                $commEarning = (float) $comm->seller_earning;

                $comm->update([
                    'status'           => 'paid',
                    'seller_payout_id' => $payout->id,
                ]);

                $remaining -= $commEarning;
            }

            return $payout;
        });

        NotificationHelper::sendToSeller(
            $seller,
            'Payout Disbursed',
            'A payout of ₹' . number_format($amount, 2) . ' has been recorded and disbursed to your account.',
            route('seller.payouts'),
            'payout',
            'fa-money-bill-transfer'
        );

        if ($seller->email && !str_starts_with($seller->email, 'temp_')) {
            try {
                Mail::to($seller->email)->send(new PayoutDisbursedMail($seller, $payout));
            } catch (\Exception $e) {
                Log::error('Failed to send payout disbursed email: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', 'Payout of ₹' . number_format($amount, 2) . ' recorded successfully.');
    }

    /**
     * Display a listing of all platform payouts.
     */
    public function payoutsList(Request $request)
    {
        $payouts = SellerPayout::with('seller')
            ->latest()
            ->paginate(15, ['*'], 'payouts_page');

        $pendingCommissions = SellerCommission::with(['order', 'orderItem', 'seller'])
            ->where('status', 'allocated')
            ->latest()
            ->get();

        $totalPendingAmount = $pendingCommissions->sum('seller_earning');
        $totalDisbursedAmount = SellerPayout::sum('amount');
        $totalDisbursedCount = SellerPayout::count();

        return view('marketplace::admin.payouts.index', compact(
            'payouts',
            'pendingCommissions',
            'totalPendingAmount',
            'totalDisbursedAmount',
            'totalDisbursedCount'
        ));
    }

    /**
     * Process bulk payouts for selected commissions.
     */
    public function bulkPayout(Request $request)
    {
        $request->validate([
            'commission_ids'   => ['required', 'array'],
            'commission_ids.*' => ['required', 'exists:seller_commissions,id'],
            'payment_method'   => ['required', 'string', 'in:bank_transfer,upi,cash,other'],
            'admin_notes'      => ['nullable', 'string', 'max:1000'],
        ]);

        $commissionIds = array_unique($request->input('commission_ids'));
        $paymentMethod = $request->input('payment_method');
        $adminNotes = $request->input('admin_notes');

        $commissions = SellerCommission::with('seller')
            ->whereIn('id', $commissionIds)
            ->where('status', 'allocated')
            ->get();

        if ($commissions->isEmpty()) {
            return redirect()->back()->withErrors(['payout_error' => 'No valid allocated commissions were selected.']);
        }

        $unverifiedSellers = [];
        foreach ($commissions as $comm) {
            $seller = $comm->seller;
            if (!$seller || $seller->account_verification_status !== 'verified') {
                $unverifiedSellers[$seller ? $seller->shop_name : 'Unknown'] = true;
            }
        }

        if (!empty($unverifiedSellers)) {
            $names = implode(', ', array_keys($unverifiedSellers));
            return redirect()->back()->withErrors([
                'payout_error' => "Payouts cannot be processed because the following sellers do not have verified payment accounts: {$names}. Please verify their accounts first."
            ]);
        }

        $grouped = $commissions->groupBy('seller_id');

        DB::transaction(function () use ($grouped, $paymentMethod, $adminNotes) {
            foreach ($grouped as $sellerId => $sellerComms) {
                $seller = $sellerComms->first()->seller;
                $totalAmount = $sellerComms->sum('seller_earning');

                $payout = SellerPayout::create([
                    'seller_id'             => $sellerId,
                    'amount'                => $totalAmount,
                    'payment_method'        => $paymentMethod,
                    'transaction_reference' => null,
                    'admin_notes'           => $adminNotes,
                    'payout_date'           => now(),
                ]);

                foreach ($sellerComms as $comm) {
                    $comm->update([
                        'status'           => 'paid',
                        'seller_payout_id' => $payout->id,
                    ]);
                }

                NotificationHelper::sendToSeller(
                    $seller,
                    'Payout Disbursed',
                    'A payout of ₹' . number_format($totalAmount, 2) . ' has been recorded and disbursed to your account for multiple orders.',
                    route('seller.payouts'),
                    'payout',
                    'fa-money-bill-transfer'
                );

                if ($seller && $seller->email && !str_starts_with($seller->email, 'temp_')) {
                    try {
                        Mail::to($seller->email)->send(new PayoutDisbursedMail($seller, $payout));
                    } catch (\Exception $e) {
                        Log::error('Failed to send payout disbursed email in bulk: ' . $e->getMessage());
                    }
                }
            }
        });

        return redirect()->back()->with('success', 'Bulk payout processed successfully for ' . $commissions->count() . ' order commissions.');
    }
}
