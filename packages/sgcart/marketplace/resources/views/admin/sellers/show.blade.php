@extends('layouts.admin')

@section('title', 'Seller Profile — SGCart Admin')

@section('content')
<style>
    /* Ensure no black browser outlines, and disable active focus ring shadows on buttons */
    input:focus, select:focus, textarea:focus {
        outline: none !important;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.15) !important;
        border-color: #3b82f6 !important;
    }
    button:focus {
        outline: none !important;
        box-shadow: none !important;
    }
</style>
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-wrap items-end justify-between gap-3 mb-6">
        <div>
            <h1 class="font-display text-2xl font-bold">{{ $seller->shop_name }}</h1>
            <x-breadcrumbs :items="[
                ['label' => 'Admin', 'url' => route('admin.dashboard')],
                ['label' => 'Marketplace', 'url' => route('admin.sellers.index')],
                ['label' => 'Sellers', 'url' => route('admin.sellers.index')],
                ['label' => 'Seller Profile']
            ]" />
        </div>
        <a href="{{ route('admin.sellers.index') }}" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 hover:text-slate-900 dark:text-slate-200 dark:hover:text-white font-semibold rounded-lg text-xs transition-colors flex items-center gap-1.5 shadow-sm">
            <i class="fa-solid fa-arrow-left"></i> Back to Sellers
        </a>
    </div>



    <!-- Shop Identity & Action Control Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex flex-col md:flex-row items-center gap-5">
                <!-- Avatar Initials -->
                <div class="w-16 h-16 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-2xl font-bold font-display text-slate-500 dark:text-slate-300 select-none border border-slate-200/50 dark:border-slate-700/50">
                    {{ strtoupper(substr($seller->shop_name, 0, 2)) }}
                </div>
                <!-- Details -->
                <div class="text-center md:text-left space-y-1">
                    <h2 class="text-xl font-bold text-slate-800 dark:text-slate-100 font-display">{{ $seller->shop_name }}</h2>
                    <p class="text-slate-400 dark:text-slate-500 text-xs">
                        Owned by <span class="font-medium text-slate-700 dark:text-slate-300">{{ $seller->name }}</span> | Registered: {{ $seller->created_at->format('M d, Y') }}
                    </p>
                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-2 pt-1">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $seller->status->value === 'approved' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/20 dark:text-emerald-400' : ($seller->status->value === 'pending' ? 'bg-amber-50 text-amber-700 dark:bg-amber-955/20 dark:text-amber-400' : 'bg-rose-50 text-rose-700 dark:bg-rose-955/20 dark:text-rose-450') }}">
                            Status: {{ $seller->status->value }}
                        </span>
                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200/50 dark:border-slate-700/50">
                            Commission: {{ number_format($seller->commission_rate ?? config('marketplace.default_commission', 10), 1) }}%
                        </span>
                    </div>
                </div>
            </div>

            <!-- Verification Action Controls -->
            <div class="flex flex-wrap gap-2 items-center justify-center">
                @if($seller->status->value === 'pending')
                    <form id="approve-detail-form" action="{{ route('admin.sellers.approve', $seller) }}" method="POST" class="inline">
                        @csrf
                        <button type="button" onclick="confirmApproveDetail('pending')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-xs uppercase tracking-wider transition-colors cursor-pointer border-none shadow-sm">
                            Approve Seller
                        </button>
                    </form>
                    <form id="reject-detail-form" action="{{ route('admin.sellers.reject', $seller) }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="rejection_reason" id="rejection-reason-detail">
                        <button type="button" onclick="confirmRejectDetail()" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-lg text-xs uppercase tracking-wider transition-colors cursor-pointer border-none shadow-sm">
                            Reject Seller
                        </button>
                    </form>
                @elseif($seller->status->value === 'approved')
                    <form id="suspend-detail-form" action="{{ route('admin.sellers.suspend', $seller) }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="suspension_reason" id="suspension-reason-detail">
                        <button type="button" onclick="confirmSuspendDetail()" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-lg text-xs uppercase tracking-wider transition-colors cursor-pointer border-none shadow-sm">
                            Suspend Seller
                        </button>
                    </form>
                @elseif($seller->status->value === 'suspended' || $seller->status->value === 'rejected')
                    <form id="approve-detail-form" action="{{ route('admin.sellers.approve', $seller) }}" method="POST" class="inline">
                        @csrf
                        <button type="button" onclick="confirmApproveDetail('{{ $seller->status->value }}')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-xs uppercase tracking-wider transition-colors cursor-pointer border-none shadow-sm">
                            Reactivate Seller
                        </button>
                    </form>
                @endif
            </div>
        </div>

        @if(($seller->status->value === 'suspended' || $seller->status->value === 'rejected') && $seller->suspension_reason)
            <div class="mt-4 p-4 bg-slate-50 dark:bg-slate-950/20 border border-slate-200 dark:border-slate-800 rounded-xl text-xs flex gap-3">
                <i class="fa-solid fa-triangle-exclamation text-rose-500 mt-0.5"></i>
                <div>
                    <span class="font-bold text-rose-700 dark:text-rose-455 block mb-1 text-[10px] uppercase tracking-wider">
                        {{ $seller->status->value === 'rejected' ? 'Rejection Reason:' : 'Suspension Reason:' }}
                    </span>
                    <p class="text-slate-655 dark:text-slate-400 leading-relaxed">{{ $seller->suspension_reason }}</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Sales & Commission Performance metrics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Gross Sales -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm flex items-center justify-between">
            <div class="space-y-1">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Gross Sales Volume</p>
                <h3 class="text-2xl font-bold text-slate-800 dark:text-white mt-1">₹{{ number_format($totalSales, 2) }}</h3>
                <p class="text-[10px] text-slate-400">Total processed item checkout volume.</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-950/30 flex items-center justify-center text-blue-600 dark:text-blue-400">
                <i class="fa-solid fa-chart-line text-xl"></i>
            </div>
        </div>

        <!-- Platform Fee Collected -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm flex items-center justify-between">
            <div class="space-y-1">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Platform Fee Collected</p>
                <h3 class="text-2xl font-bold text-slate-800 dark:text-white mt-1">₹{{ number_format($totalCommissionCollected, 2) }}</h3>
                <p class="text-[10px] text-slate-400">Total retained platform commission fee.</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-50 dark:bg-rose-950/30 flex items-center justify-center text-rose-600 dark:text-rose-400">
                <i class="fa-solid fa-percent text-xl"></i>
            </div>
        </div>

        <!-- Lifetime Net Earnings -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm flex items-center justify-between">
            <div class="space-y-1">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Lifetime Net Earnings</p>
                <h3 class="text-2xl font-bold text-slate-800 dark:text-white mt-1">₹{{ number_format($totalEarnings, 2) }}</h3>
                <p class="text-[10px] text-slate-400">Net revenue earned by the seller shop.</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-slate-50 dark:bg-slate-950/30 flex items-center justify-center text-slate-500 dark:text-slate-400">
                <i class="fa-solid fa-coins text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Ledger Balances -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Pending Balance -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex items-center gap-4">
            <div class="w-10 h-10 rounded-lg bg-amber-50 dark:bg-amber-955/20 text-amber-600 dark:text-amber-400 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <div>
                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider leading-none">Pending Balance</p>
                <h4 class="text-lg font-bold text-amber-650 dark:text-amber-400 mt-1">₹{{ number_format($pendingBalance, 2) }}</h4>
                <p class="text-[9px] text-slate-450 mt-0.5">Commissions from undelivered orders.</p>
            </div>
        </div>

        <!-- Allocated / Due -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex items-center gap-4">
            <div class="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-955/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider leading-none">Allocated Balance (Due)</p>
                <h4 class="text-lg font-bold text-emerald-600 dark:text-emerald-400 mt-1">₹{{ number_format($allocatedBalance, 2) }}</h4>
                <p class="text-[9px] text-slate-450 mt-0.5">Delivered orders, withdrawable due.</p>
            </div>
        </div>

        <!-- Paid / Settled -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex items-center gap-4">
            <div class="w-10 h-10 rounded-lg bg-indigo-50 dark:bg-indigo-955/20 text-indigo-650 dark:text-indigo-400 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-money-bill-transfer"></i>
            </div>
            <div>
                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider leading-none">Settled Balance (Paid)</p>
                <h4 class="text-lg font-bold text-indigo-600 dark:text-indigo-450 mt-1">₹{{ number_format($settledBalance, 2) }}</h4>
                <p class="text-[9px] text-slate-450 mt-0.5">Fund payouts disbursed to seller.</p>
            </div>
        </div>
    </div>

    <!-- Layout Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Side Column -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Payment Credentials & Payouts Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                        <i class="fa-solid fa-credit-card text-slate-400"></i> Payment Details & Payouts
                    </h3>
                    @php
                        $accountStatus = $seller->account_verification_status ?? 'unsubmitted';
                        $statusClasses = [
                            'unsubmitted' => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400',
                            'pending'     => 'bg-amber-50 text-amber-700 dark:bg-amber-955/20 dark:text-amber-400',
                            'verified'    => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-955/20 dark:text-emerald-450',
                            'rejected'    => 'bg-rose-50 text-rose-700 dark:bg-rose-955/20 dark:text-rose-450',
                        ];
                    @endphp
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $statusClasses[$accountStatus] ?? $statusClasses['unsubmitted'] }}">
                        Account: {{ $accountStatus }}
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Registered Payment Details -->
                    <div class="space-y-4">
                        <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Registered Account</h4>
                        @if($accountStatus === 'unsubmitted')
                            <div class="p-4 bg-slate-50 dark:bg-slate-950/20 border border-slate-200/50 dark:border-slate-800 rounded-xl text-xs text-slate-400 text-center italic">
                                No payment account details submitted yet.
                            </div>
                        @else
                            <div class="text-xs space-y-2 bg-slate-50 dark:bg-slate-950/20 border border-slate-200/50 dark:border-slate-800 p-4 rounded-xl relative">
                                <p class="flex justify-between border-b border-slate-200/40 pb-1.5"><span class="text-slate-400">Account Holder:</span> <span class="font-bold text-slate-800 dark:text-slate-200">{{ $seller->account_details['account_holder_name'] ?? 'N/A' }}</span></p>
                                <p class="flex justify-between border-b border-slate-200/40 pb-1.5"><span class="text-slate-400">Bank Name:</span> <span class="font-bold text-slate-800 dark:text-slate-200">{{ $seller->account_details['bank_name'] ?? 'N/A' }}</span></p>
                                <p class="flex justify-between border-b border-slate-200/40 pb-1.5"><span class="text-slate-400">Account Number:</span> <span class="font-mono font-bold text-slate-800 dark:text-slate-200">{{ $seller->account_details['account_number'] ?? 'N/A' }}</span></p>
                                <p class="flex justify-between border-b border-slate-200/40 pb-1.5"><span class="text-slate-400">IFSC / Route:</span> <span class="font-mono font-bold text-slate-850 dark:text-slate-200">{{ $seller->account_details['ifsc_code'] ?? 'N/A' }}</span></p>
                                <p class="flex justify-between border-b border-slate-200/40 pb-1.5"><span class="text-slate-400">Branch Name:</span> <span class="font-bold text-slate-850 dark:text-slate-200">{{ $seller->account_details['branch_name'] ?? 'N/A' }}</span></p>
                                @if(!empty($seller->account_details['upi_id']))
                                    <p class="flex justify-between"><span class="text-slate-400">UPI ID:</span> <span class="font-mono font-bold text-slate-850 dark:text-slate-200">{{ $seller->account_details['upi_id'] }}</span></p>
                                @endif
                            </div>
                        @endif

                        <!-- Approve / Reject Verification Actions -->
                        @if($accountStatus === 'pending')
                            <div class="flex gap-2">
                                <form action="{{ route('admin.sellers.approve-account', $seller) }}" method="POST" class="flex-1">
                                    @csrf
                                    <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-xs transition-colors cursor-pointer border-none shadow-sm">
                                        Approve
                                    </button>
                                </form>
                                <button type="button" onclick="openRejectAccountModal()" class="flex-1 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-lg text-xs transition-colors cursor-pointer border-none shadow-sm">
                                    Reject
                                </button>
                            </div>
                        @elseif($accountStatus === 'rejected' && $seller->account_rejection_reason)
                            <div class="p-3 bg-rose-50 dark:bg-rose-955/10 border border-rose-100 dark:border-rose-900/30 rounded-xl text-xs">
                                <span class="font-bold text-rose-600 dark:text-rose-400 block mb-1 text-[10px] uppercase">Rejection Reason:</span>
                                <p class="text-slate-655 dark:text-slate-400 leading-relaxed">{{ $seller->account_rejection_reason }}</p>
                            </div>
                        @elseif($accountStatus === 'verified')
                            <div>
                                <form action="{{ route('admin.sellers.allow-account-edit', $seller) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full py-2 bg-amber-500/10 hover:bg-amber-500/15 border border-amber-500/20 text-amber-600 dark:text-amber-400 font-bold rounded-lg text-xs transition-colors cursor-pointer">
                                        <i class="fa-solid fa-lock-open mr-1"></i> Unlock for Editing
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>

                    <!-- Payouts & Settlements Summary -->
                    <div class="space-y-4">
                        <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Payout & Settlements</h4>
                        
                        <div class="p-4 bg-slate-50 dark:bg-slate-950/20 border border-slate-200/60 dark:border-slate-800 rounded-xl space-y-4 flex flex-col justify-between h-[180px] box-sizing-border">
                            <!-- Balance info -->
                            <div class="space-y-1">
                                <span class="text-[10px] font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wide">Withdrawable Balance</span>
                                <div class="flex items-baseline gap-1">
                                    <h3 class="text-2xl font-black text-slate-800 dark:text-white font-mono">₹{{ number_format($allocatedBalance, 2) }}</h3>
                                    <span class="text-xs font-bold text-slate-400">allocated</span>
                                </div>
                            </div>
                            
                            <!-- Account Status and info -->
                            <div class="text-xs space-y-1.5">
                                @if($accountStatus !== 'verified')
                                    <p class="text-amber-600 dark:text-amber-450 font-bold flex items-center gap-1.5">
                                        <i class="fa-solid fa-lock text-[10px]"></i> Payouts Locked
                                    </p>
                                    <p class="text-[10px] text-slate-400 dark:text-slate-500 leading-normal">
                                        You must approve the seller's payment details before disbursing payouts.
                                    </p>
                                @elseif($allocatedBalance <= 0)
                                    <p class="text-slate-500 dark:text-slate-400 font-bold flex items-center gap-1.5">
                                        <i class="fa-solid fa-circle-check text-[10px] text-emerald-500"></i> No Payouts Due
                                    </p>
                                    <p class="text-[10px] text-slate-400 dark:text-slate-500 leading-normal">
                                        This seller has no new delivered order earnings due for settlement.
                                    </p>
                                @else
                                    <p class="text-emerald-600 dark:text-emerald-450 font-bold flex items-center gap-1.5">
                                        <i class="fa-solid fa-circle-check text-[10px]"></i> Ready for Settlement
                                    </p>
                                    <p class="text-[10px] text-slate-450 dark:text-slate-500 leading-normal">
                                        Delivered order commissions are awaiting disbursal.
                                    </p>
                                @endif
                            </div>

                            <!-- Primary Dashboard Link Button -->
                            <div>
                                @if($accountStatus === 'verified' && $allocatedBalance > 0)
                                    <a href="{{ route('admin.payouts.index') }}?search={{ urlencode($seller->shop_name) }}" class="w-full py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg text-xs transition-colors flex items-center justify-center gap-1.5 shadow-sm border-none cursor-pointer text-center no-underline box-border">
                                        <i class="fa-solid fa-paper-plane"></i> Go to Payout Dashboard
                                    </a>
                                @else
                                    <a href="{{ route('admin.payouts.index') }}" class="w-full py-2 bg-slate-100 hover:bg-slate-200/80 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-205 font-bold rounded-lg text-xs transition-colors flex items-center justify-center gap-1.5 border border-slate-200/50 dark:border-slate-700/50 cursor-pointer text-center no-underline box-border">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i> Open Payout Panel
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Seller Payout History Log -->
                <div class="space-y-3 pt-3 border-t border-slate-100 dark:border-slate-800/80">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Payout Disbursal Log</h4>
                    <div class="overflow-x-auto border border-slate-200 dark:border-slate-800/80 rounded-xl">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800/80 text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                    <th class="py-3 px-5">Date</th>
                                    <th class="py-3 px-5">Amount</th>
                                    <th class="py-3 px-5">Method</th>
                                    <th class="py-3 px-5">Transaction UTR</th>
                                    <th class="py-3 px-5">Settled Orders</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-350">
                                @forelse($payouts as $p)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/15 transition-colors">
                                        <td class="py-3.5 px-5 whitespace-nowrap text-slate-500">{{ $p->payout_date->format('M d, Y, h:i A') }}</td>
                                        <td class="py-3.5 px-5 font-bold text-emerald-600 dark:text-emerald-450 whitespace-nowrap">₹{{ number_format($p->amount, 2) }}</td>
                                        <td class="py-3.5 px-5 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium {{ $p->payment_method === 'bank_transfer' ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/30 dark:text-blue-400' : ($p->payment_method === 'upi' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-955/30 dark:text-emerald-400' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300') }}">
                                                {{ str_replace('_', ' ', $p->payment_method) }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-5 whitespace-nowrap">
                                            @if($p->transaction_reference)
                                                <span class="font-mono text-[10px] bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded border border-slate-200/50 dark:border-slate-700/50 text-slate-600 dark:text-slate-300">
                                                    {{ $p->transaction_reference }}
                                                </span>
                                            @else
                                                <span class="text-slate-400 italic">N/A</span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-5 max-w-[200px]">
                                            @php
                                                $orderRefs = $p->commissions->map(fn($c) => $c->order->order_number ?? $c->order_id)->unique();
                                            @endphp
                                            <div class="flex flex-wrap gap-1">
                                                @forelse($orderRefs as $ref)
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 dark:bg-slate-850 text-slate-655 dark:text-slate-400 border border-slate-200/40 dark:border-slate-700/40">
                                                        #{{ $ref }}
                                                    </span>
                                                @empty
                                                    <span class="text-slate-400 italic">None</span>
                                                @endforelse
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-5 text-center text-slate-450 dark:text-slate-600 italic">No payout records logged yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Products Catalog Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                        <i class="fa-solid fa-box-open text-slate-400"></i> Seller Catalog (Products)
                    </h3>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200/50 dark:border-slate-700/50">
                        {{ $products->total() }} Listings
                    </span>
                </div>
                
                <div class="overflow-x-auto border border-slate-200 dark:border-slate-800/80 rounded-xl">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800/80 text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                <th class="py-3 px-5">Product</th>
                                <th class="py-3 px-5">SKU</th>
                                <th class="py-3 px-5">Price</th>
                                <th class="py-3 px-5">Stock</th>
                                <th class="py-3 px-5">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-355">
                            @forelse($products as $p)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/15 transition-colors">
                                    <td class="py-3.5 px-5 font-semibold text-slate-850 dark:text-white">
                                        {{ $p->name }}
                                    </td>
                                    <td class="py-3.5 px-5">
                                        <span class="font-mono text-[10px] bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded border border-slate-200/50 dark:border-slate-700/50 text-slate-500 dark:text-slate-400">
                                            {{ $p->sku }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-5 font-bold">₹{{ number_format($p->price, 2) }}</td>
                                    <td class="py-3.5 px-5">
                                        @if($p->stock == 0)
                                            <span class="inline-flex items-center gap-1.5 font-bold text-rose-600 dark:text-rose-450">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Out of Stock
                                            </span>
                                        @elseif($p->stock <= 5)
                                            <span class="inline-flex items-center gap-1.5 font-semibold text-amber-600 dark:text-amber-400">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> {{ $p->stock }} units (Low)
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-400 font-medium">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> {{ $p->stock }} units
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-5 whitespace-nowrap">
                                        @php
                                            $badgeClass = 'bg-slate-50 text-slate-655 dark:bg-slate-800 dark:text-slate-400 border border-slate-200/50 dark:border-slate-700/50';
                                            if ($p->status->value === 'active') {
                                                $badgeClass = 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/20 dark:text-emerald-450 border border-emerald-500/20';
                                            } elseif ($p->status->value === 'pending_approval') {
                                                $badgeClass = 'bg-amber-50 text-amber-700 dark:bg-amber-950/20 dark:text-amber-450 border border-amber-500/20';
                                            } elseif ($p->status->value === 'rejected') {
                                                $badgeClass = 'bg-rose-50 text-rose-700 dark:bg-rose-955/20 dark:text-rose-455 border border-rose-500/20';
                                            }
                                        @endphp
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider {{ $badgeClass }}">
                                            {{ str_replace('_', ' ', $p->status->value) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-slate-450 dark:text-slate-600 italic">No products uploaded under this shop yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($products->hasPages())
                    <div class="pt-2">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Sidebar actions -->
        <div class="space-y-6">
            <!-- Commission Rate override card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-4">
                <h4 class="text-sm font-bold text-slate-800 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-3 flex items-center gap-2">
                    <i class="fa-solid fa-sliders text-slate-450"></i> Commission Config
                </h4>
                
                <form action="{{ route('admin.sellers.update-commission', $seller) }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Custom Rate Override (%)</label>
                        <div class="relative">
                            <input type="number" name="commission_rate" step="0.01" min="0" max="100"
                                   value="{{ old('commission_rate', $seller->commission_rate) }}"
                                   placeholder="Default System Rate (10.00%)"
                                   class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl py-2 px-3 text-xs font-semibold focus:outline-none focus:border-blue-500 transition-colors">
                            <span class="absolute right-4 top-2 text-xs font-semibold text-slate-400">%</span>
                        </div>
                        <p class="text-[9px] text-slate-400 leading-relaxed mt-0.5">Overrides the default global commission rate for all orders checked out from this seller shop.</p>
                        @error('commission_rate') <p class="text-rose-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    <button type="submit" class="w-full py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg text-xs transition-colors cursor-pointer border-none shadow-sm">
                        Save Rate Override
                    </button>
                </form>
            </div>

            <!-- Contact Information Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-4">
                <h4 class="text-sm font-bold text-slate-800 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-3 flex items-center gap-2">
                    <i class="fa-solid fa-address-book text-slate-450"></i> Contact Information
                </h4>
                <div class="text-xs space-y-3">
                    <div class="flex items-start justify-between">
                        <span class="text-slate-400">Owner Name:</span> 
                        <span class="text-slate-800 dark:text-slate-200 font-semibold">{{ $seller->name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex items-start justify-between">
                        <span class="text-slate-400">Email:</span> 
                        <a href="mailto:{{ $seller->email }}" class="text-blue-600 dark:text-blue-450 hover:underline font-semibold">{{ $seller->email }}</a>
                    </div>
                    <div class="flex items-start justify-between">
                        <span class="text-slate-400">Phone:</span> 
                        <span class="text-slate-800 dark:text-slate-200 font-semibold">{{ $seller->phone_no ?? 'N/A' }}</span>
                    </div>
                    @if($seller->address)
                        <div class="pt-2 border-t border-slate-100 dark:border-slate-800/80">
                            <span class="text-slate-400 block mb-1">Store Address:</span> 
                            <p class="text-slate-700 dark:text-slate-350 leading-relaxed font-medium bg-slate-50 dark:bg-slate-950/20 p-3 rounded-xl border border-slate-100 dark:border-slate-800/50">{{ $seller->address }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Description Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-3">
                <h4 class="text-sm font-bold text-slate-800 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-3 flex items-center gap-2">
                    <i class="fa-solid fa-store text-slate-450"></i> Shop Description
                </h4>
                <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed font-medium bg-slate-50 dark:bg-slate-950/20 p-3.5 rounded-xl border border-slate-100 dark:border-slate-800/50 italic">
                    "{{ $seller->shop_description ?? 'No shop description provided.' }}"
                </p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function confirmApproveDetail(status = 'pending') {
        const text = status === 'pending'
            ? 'Are you sure you want to approve this seller account? They will be allowed to log in and list products.'
            : 'Are you sure you want to reactivate this seller account? They will be allowed to log in and active status will be restored.';
        const title = status === 'pending' ? 'Approve Seller' : 'Reactivate Seller';
        showConfirm(
            text,
            () => document.getElementById('approve-detail-form').submit(),
            title
        );
    }
    
    function confirmSuspendDetail() {
        showSuspendModal((reason) => {
            document.getElementById('suspension-reason-detail').value = reason;
            document.getElementById('suspend-detail-form').submit();
        });
    }

    function confirmRejectDetail() {
        showRejectModal((reason) => {
            document.getElementById('rejection-reason-detail').value = reason;
            document.getElementById('reject-detail-form').submit();
        });
    }

    function showRejectModal(callback) {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm animate-fadeIn';
        modal.innerHTML = `
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 dark:border-slate-800 transform scale-95 opacity-0 transition-all duration-300 popup-content text-left">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white font-display">Reject Seller Application</h3>
                    <button class="modal-close text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer border-none bg-transparent outline-none focus:outline-none">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-4 leading-relaxed">
                    Please provide a reason for rejecting this seller application. They will see this reason on their dashboard.
                </p>
                <div class="mb-5">
                    <textarea id="rejectionReasonInput" class="w-full min-h-[100px] p-3 rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent text-slate-800 dark:text-slate-100 text-sm focus:outline-none focus:border-blue-500" placeholder="e.g. Incomplete warehouse address, invalid company documents..."></textarea>
                    <p id="rejectionErrorMsg" class="text-xs text-rose-500 mt-1.5 hidden">Rejection reason is required.</p>
                </div>
                <div class="flex justify-end gap-3">
                    <button class="modal-cancel border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 px-5 py-2.5 rounded-lg text-sm font-semibold cursor-pointer transition-colors bg-transparent" style="text-transform: none;">Cancel</button>
                    <button class="modal-confirm bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white shadow-lg shadow-rose-600/10 border-none px-5 py-2.5 rounded-lg text-sm font-semibold cursor-pointer" style="text-transform: none;">Reject</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);

        setTimeout(() => {
            const content = modal.querySelector('.popup-content');
            if (content) {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }
            modal.querySelector('#rejectionReasonInput').focus();
        }, 10);

        function closeModal(confirmed = false) {
            const reasonVal = modal.querySelector('#rejectionReasonInput').value.trim();
            if (confirmed) {
                if (!reasonVal) {
                    modal.querySelector('#rejectionErrorMsg').classList.remove('hidden');
                    return;
                }
                
                const confirmBtn = modal.querySelector('.modal-confirm');
                const cancelBtn = modal.querySelector('.modal-cancel');
                const closeBtn = modal.querySelector('.modal-close');
                const textarea = modal.querySelector('#rejectionReasonInput');
                
                confirmBtn.disabled = true;
                confirmBtn.style.opacity = '0.7';
                confirmBtn.style.cursor = 'not-allowed';
                confirmBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Rejecting...';
                
                cancelBtn.disabled = true;
                cancelBtn.style.opacity = '0.5';
                cancelBtn.style.cursor = 'not-allowed';
                
                closeBtn.disabled = true;
                textarea.disabled = true;
                
                if (typeof callback === 'function') {
                    callback(reasonVal);
                }
                return;
            }

            const content = modal.querySelector('.popup-content');
            if (content) {
                content.classList.remove('scale-100', 'opacity-100');
                content.classList.add('scale-95', 'opacity-0');
            }
            setTimeout(() => {
                modal.remove();
            }, 200);
        }

        modal.querySelector('.modal-close').addEventListener('click', () => closeModal(false));
        modal.querySelector('.modal-cancel').addEventListener('click', () => closeModal(false));
        modal.querySelector('.modal-confirm').addEventListener('click', () => closeModal(true));
    }

    function showSuspendModal(callback) {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm animate-fadeIn';
        modal.innerHTML = `
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 dark:border-slate-800 transform scale-95 opacity-0 transition-all duration-300 popup-content text-left">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white font-display">Suspend Seller</h3>
                    <button class="modal-close text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer border-none bg-transparent outline-none focus:outline-none">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-4 leading-relaxed">
                    Please provide a reason for suspending this seller account. Their products will be hidden from the storefront.
                </p>
                <div class="mb-5">
                    <textarea id="suspensionReasonInput" class="w-full min-h-[100px] p-3 rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent text-slate-800 dark:text-slate-100 text-sm focus:outline-none focus:border-blue-500" placeholder="e.g. Violation of marketplace policy, repeated negative reviews..."></textarea>
                    <p id="suspensionErrorMsg" class="text-xs text-rose-500 mt-1.5 hidden">Suspension reason is required.</p>
                </div>
                <div class="flex justify-end gap-3">
                    <button class="modal-cancel border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 px-5 py-2.5 rounded-lg text-sm font-semibold cursor-pointer transition-colors bg-transparent" style="text-transform: none;">Cancel</button>
                    <button class="modal-confirm bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white shadow-lg shadow-rose-600/10 border-none px-5 py-2.5 rounded-lg text-sm font-semibold cursor-pointer" style="text-transform: none;">Suspend</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);

        setTimeout(() => {
            const content = modal.querySelector('.popup-content');
            if (content) {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }
            modal.querySelector('#suspensionReasonInput').focus();
        }, 10);

        function closeModal(confirmed = false) {
            const reasonVal = modal.querySelector('#suspensionReasonInput').value.trim();
            if (confirmed) {
                if (!reasonVal) {
                    modal.querySelector('#suspensionErrorMsg').classList.remove('hidden');
                    return;
                }
                
                const confirmBtn = modal.querySelector('.modal-confirm');
                const cancelBtn = modal.querySelector('.modal-cancel');
                const closeBtn = modal.querySelector('.modal-close');
                const textarea = modal.querySelector('#suspensionReasonInput');
                
                confirmBtn.disabled = true;
                confirmBtn.style.opacity = '0.7';
                confirmBtn.style.cursor = 'not-allowed';
                confirmBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Suspending...';
                
                cancelBtn.disabled = true;
                cancelBtn.style.opacity = '0.5';
                cancelBtn.style.cursor = 'not-allowed';
                
                closeBtn.disabled = true;
                textarea.disabled = true;
                
                if (typeof callback === 'function') {
                    callback(reasonVal);
                }
                return;
            }

            const content = modal.querySelector('.popup-content');
            if (content) {
                content.classList.remove('scale-100', 'opacity-100');
                content.classList.add('scale-95', 'opacity-0');
            }
            setTimeout(() => {
                modal.remove();
            }, 200);
        }

        modal.querySelector('.modal-close').addEventListener('click', () => closeModal(false));
        modal.querySelector('.modal-cancel').addEventListener('click', () => closeModal(false));
        modal.querySelector('.modal-confirm').addEventListener('click', () => closeModal(true));
    }

    function openRejectAccountModal() {
        showRejectAccountDialog((reason) => {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `{{ route('admin.sellers.reject-account', $seller) }}`;
            form.innerHTML = `
                @csrf
                <input type="hidden" name="account_rejection_reason" value="${reason}">
            `;
            document.body.appendChild(form);
            form.submit();
        });
    }

    function showRejectAccountDialog(callback) {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm animate-fadeIn';
        modal.innerHTML = `
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 dark:border-slate-800 transform scale-95 opacity-0 transition-all duration-300 popup-content text-left">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white font-display">Reject Payment Account Details</h3>
                    <button class="modal-close text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer border-none bg-transparent outline-none focus:outline-none">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-4 leading-relaxed">
                    Please provide a reason for rejecting the seller's payment account details. The seller will see this on their account settings page.
                </p>
                <div class="mb-5">
                    <textarea id="accountRejectionReasonInput" class="w-full min-h-[100px] p-3 rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent text-slate-800 dark:text-slate-100 text-sm focus:outline-none focus:border-blue-500" placeholder="e.g. Account holder name mismatch, incorrect IFSC code..."></textarea>
                    <p id="accountRejectionErrorMsg" class="text-xs text-rose-500 mt-1.5 hidden">Rejection reason is required.</p>
                </div>
                <div class="flex justify-end gap-3">
                    <button class="modal-cancel border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 px-5 py-2.5 rounded-lg text-sm font-semibold cursor-pointer transition-colors bg-transparent" style="text-transform: none;">Cancel</button>
                    <button class="modal-confirm bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white shadow-lg shadow-rose-600/10 border-none px-5 py-2.5 rounded-lg text-sm font-semibold cursor-pointer" style="text-transform: none;">Reject</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);

        setTimeout(() => {
            const content = modal.querySelector('.popup-content');
            if (content) {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }
            modal.querySelector('#accountRejectionReasonInput').focus();
        }, 10);

        function closeModal(confirmed = false) {
            const reasonVal = modal.querySelector('#accountRejectionReasonInput').value.trim();
            if (confirmed) {
                if (!reasonVal) {
                    modal.querySelector('#accountRejectionErrorMsg').classList.remove('hidden');
                    return;
                }
                
                const confirmBtn = modal.querySelector('.modal-confirm');
                const cancelBtn = modal.querySelector('.modal-cancel');
                const closeBtn = modal.querySelector('.modal-close');
                const textarea = modal.querySelector('#accountRejectionReasonInput');
                
                confirmBtn.disabled = true;
                confirmBtn.style.opacity = '0.7';
                confirmBtn.style.cursor = 'not-allowed';
                confirmBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Rejecting...';
                
                cancelBtn.disabled = true;
                cancelBtn.style.opacity = '0.5';
                cancelBtn.style.cursor = 'not-allowed';
                
                closeBtn.disabled = true;
                textarea.disabled = true;
                
                if (typeof callback === 'function') {
                    callback(reasonVal);
                }
                return;
            }

            const content = modal.querySelector('.popup-content');
            if (content) {
                content.classList.remove('scale-100', 'opacity-100');
                content.classList.add('scale-95', 'opacity-0');
            }
            setTimeout(() => {
                modal.remove();
            }, 200);
        }

        modal.querySelector('.modal-close').addEventListener('click', () => closeModal(false));
        modal.querySelector('.modal-cancel').addEventListener('click', () => closeModal(false));
        modal.querySelector('.modal-confirm').addEventListener('click', () => closeModal(true));
    }
</script>
@endpush
@endsection
