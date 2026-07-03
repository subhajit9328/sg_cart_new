<?php

namespace SGCart\Marketplace\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use SGCart\Marketplace\Models\Seller;
use SGCart\Marketplace\Models\SellerCommission;
use SGCart\Marketplace\Mail\SellerApprovedMail;
use SGCart\Marketplace\Mail\SellerRejectedMail;
use SGCart\Marketplace\Mail\SellerSuspendedMail;

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

        return view('marketplace::admin.sellers.show', compact(
            'seller',
            'products',
            'totalSales',
            'totalCommissionCollected'
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
}
