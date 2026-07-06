@extends('layouts.admin')

@section('title', 'Platform Payouts — SGCart Admin')

@section('content')
<style>
    .interactive-row {
        cursor: pointer;
        transition: background-color 0.1s ease;
    }
    .interactive-card {
        cursor: pointer;
        transition: border-color 0.1s ease, background-color 0.1s ease;
    }
    .tooltip-trigger:hover .tooltip-content {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }
    /* Ensure no black browser outlines, and disable active focus ring shadows on buttons */
    input:focus, select:focus, textarea:focus {
        outline: none !important;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.15) !important;
    }
    button:focus {
        outline: none !important;
        box-shadow: none !important;
    }
</style>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-2">
        <div>
            <h1 class="font-display text-2xl font-bold tracking-tight text-slate-800 dark:text-white">Platform Payouts</h1>
            <x-breadcrumbs :items="[
                ['label' => 'Admin', 'url' => route('admin.dashboard')],
                ['label' => 'Marketplace', 'url' => route('admin.sellers.index')],
                ['label' => 'Payouts']
            ]" />
        </div>
    </div>

    <!-- KPI Statistics Banner -->
    @php
        $uniqueSellers = $pendingCommissions->pluck('seller')->unique('id');
        $unverifiedCount = $uniqueSellers->filter(fn($s) => $s && $s->account_verification_status !== 'verified')->count();
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x divide-slate-200 dark:divide-slate-800 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
        <div class="p-5 flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider">Withdrawable Balance</span>
                <h4 class="text-2xl font-extrabold text-slate-800 dark:text-white mt-1.5 font-mono">₹{{ number_format($totalPendingAmount, 2) }}</h4>
            </div>
            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-800 px-3 py-1 rounded-full border border-slate-200 dark:border-slate-700/60 shadow-2xs">{{ $pendingCommissions->count() }} commissions</span>
        </div>
        <div class="p-5 flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider">Total Disbursed</span>
                <h4 class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-450 mt-1.5 font-mono">₹{{ number_format($totalDisbursedAmount, 2) }}</h4>
            </div>
            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-800 px-3 py-1 rounded-full border border-slate-200 dark:border-slate-700/60 shadow-2xs">{{ $totalDisbursedCount }} payouts log</span>
        </div>
        <div class="p-5 flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider">Unverified Vendors</span>
                <h4 class="text-2xl font-extrabold text-amber-600 dark:text-amber-500 mt-1.5">{{ $unverifiedCount }} {{ Str::plural('Seller', $unverifiedCount) }}</h4>
            </div>
            <span class="text-xs font-bold {{ $unverifiedCount > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-400' }} flex items-center gap-1.5">
                @if($unverifiedCount > 0)
                    <i class="fa-solid fa-circle-exclamation text-[10px]"></i> Action required
                @else
                    <i class="fa-solid fa-circle-check text-[10px] text-emerald-500"></i> All verified
                @endif
            </span>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="flex border-b border-slate-200 dark:border-slate-800 gap-6">
        <button onclick="switchTab('pending-payouts-tab', 'disbursed-history-tab')" id="pending-tab-btn" class="pb-3 text-sm font-bold border-b-2 border-blue-600 text-blue-600 dark:text-blue-400 focus:outline-none transition-all flex items-center gap-2 cursor-pointer bg-transparent border-t-0 border-x-0">
            <i class="fa-solid fa-wallet text-xs"></i> Pending Payouts
            <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-blue-50 dark:bg-blue-900 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-850">
                {{ $pendingCommissions->count() }}
            </span>
        </button>
        <button onclick="switchTab('disbursed-history-tab', 'pending-payouts-tab')" id="history-tab-btn" class="pb-3 text-sm font-semibold border-b-2 border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300 focus:outline-none transition-all flex items-center gap-2 cursor-pointer bg-transparent border-t-0 border-x-0">
            <i class="fa-solid fa-history text-xs"></i> Disbursed Logs
            <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                {{ $payouts->total() }}
            </span>
        </button>
    </div>

    <!-- PENDING PAYOUTS TAB CONTENT -->
    <div id="pending-payouts-tab" class="space-y-3">
           <form id="bulkPayoutForm" action="{{ route('admin.payouts.bulk') }}" method="POST" onsubmit="return handleFormSubmit()" class="space-y-4">
            @csrf

            <!-- INTERACTIVE TOOLBAR -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-3 rounded-xl shadow-sm">
                <!-- 1. Default Search & Filters -->
                <div id="default-toolbar" class="flex flex-col sm:flex-row gap-4 items-center justify-between">
                    <div class="relative w-full sm:w-80">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" id="pendingSearchInput" onkeyup="filterPendingTable()" placeholder="Search order, seller, product..." class="w-full bg-slate-55 dark:bg-slate-950/40 border border-slate-200 dark:border-slate-800 rounded-lg pl-9 pr-3.5 h-8 text-xs focus:ring-1 focus:ring-blue-500 focus:outline-none transition-all text-slate-800 dark:text-slate-200">
                    </div>
                    <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                        <div class="min-w-[150px]">
                            <x-select2 
                                name="verification_filter" 
                                id="verificationFilter" 
                                placeholder="All Vendors"
                                :compact="true"
                                :allowClear="false"
                                :searchable="false"
                            >
                                <option value="all" selected>All Vendors</option>
                                <option value="verified">Verified Account</option>
                                <option value="unverified">Verification Pending</option>
                            </x-select2>
                        </div>
                        <button type="button" onclick="resetFilters()" class="h-8 text-xs font-bold text-slate-550 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 bg-slate-50 hover:bg-slate-100 dark:bg-slate-850 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700/60 px-3.5 rounded-lg cursor-pointer flex items-center gap-1.5 transition-colors shrink-0 outline-none focus:outline-none">
                            <i class="fa-solid fa-rotate-right text-[10px]"></i> Reset
                        </button>
                    </div>
                </div>

                <!-- 2. Professional Bulk Actions Toolbar (Swaps in dynamically) -->
                <div id="bulk-toolbar" class="hidden flex flex-col lg:flex-row gap-4 items-stretch lg:items-center justify-between">
                    <div class="flex items-center gap-2.5 shrink-0 h-8">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500 animate-pulse"></span>
                        <span class="text-sm font-bold text-slate-700 dark:text-slate-200">
                            Selected: <span id="selected-count-toolbar" class="text-blue-600 dark:text-blue-400 font-extrabold">0</span> orders 
                            (Total: <span id="selected-total-toolbar" class="text-emerald-600 dark:text-emerald-450 font-extrabold font-mono">₹0.00</span>)
                        </span>
                        <button type="button" onclick="clearSelections()" class="text-xs font-bold text-slate-400 hover:text-rose-500 bg-transparent border-none cursor-pointer flex items-center gap-1 ml-3 transition-colors outline-none focus:outline-none">
                            <i class="fa-solid fa-circle-xmark"></i> Clear
                        </button>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden sm:inline shrink-0">Method</span>
                            <div class="min-w-[180px] w-full sm:w-auto">
                                <x-select2 
                                    name="payment_method" 
                                    id="payment_method" 
                                    placeholder="Payment Method"
                                    :compact="true"
                                    :allowClear="false"
                                    :searchable="false"
                                >
                                    <option value="bank_transfer" selected>Bank Transfer (IMPS/NEFT)</option>
                                    <option value="upi">UPI (GPay/PhonePe)</option>
                                    <option value="cash">Cash Settlement</option>
                                    <option value="other">Other System</option>
                                </x-select2>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden sm:inline shrink-0">Memo</span>
                            <input type="text" name="admin_notes" class="w-full sm:w-56 h-8 bg-slate-55 dark:bg-slate-800 border border-slate-200 dark:border-slate-800 rounded-lg px-3 text-xs text-slate-800 dark:text-slate-200 outline-none focus:ring-1 focus:ring-blue-500 transition-all" placeholder="Optional audit note...">
                        </div>

                        <div class="w-full sm:w-auto">
                            <button type="submit" id="toolbar-submit-btn" disabled 
                                    class="w-full sm:w-auto h-8 px-5 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold rounded-lg text-xs transition-all cursor-pointer border-none flex items-center justify-center gap-1.5 transform active:scale-95 shadow-sm">
                                <span id="submit-btn-spinner" class="hidden"><i class="fa-solid fa-spinner fa-spin text-[10px]"></i></span>
                                <span id="submit-btn-text">Disburse Payouts</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>


            <!-- MOBILE VIEW: SELECT ALL HEADER -->
            @if($pendingCommissions->isNotEmpty())
                <div class="flex md:hidden items-center justify-between bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 px-4 py-3 rounded-lg">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="select-all-mobile" onclick="toggleSelectAll(this)" class="rounded border-slate-300 dark:border-slate-700 text-blue-600 focus:ring-blue-500 w-4.5 h-4.5 cursor-pointer">
                        <label for="select-all-mobile" class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider cursor-pointer">Select All Visible</label>
                    </div>
                </div>
            @endif

            <!-- DESKTOP TABLE VIEW -->
            <div class="hidden md:block bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left border-collapse" id="pendingCommissionsTable">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-800/50 text-xs font-semibold uppercase tracking-wider text-slate-450 dark:text-slate-500">
                                <th class="px-5 py-3.5 w-8 text-center">
                                    <input type="checkbox" id="select-all-desktop" onclick="toggleSelectAll(this)" class="rounded border-slate-300 dark:border-slate-700 text-blue-600 focus:ring-blue-500 w-4 h-4 cursor-pointer">
                                </th>
                                <th class="px-5 py-3.5">Delivered Date</th>
                                <th class="px-5 py-3.5">Order</th>
                                <th class="px-5 py-3.5">Seller (Shop)</th>
                                <th class="px-5 py-3.5">Product & Details</th>
                                <th class="px-5 py-3.5 text-right">Earning to Payout</th>
                                <th class="px-5 py-3.5 text-center">Account Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-600 dark:text-slate-300">
                            @foreach($pendingCommissions as $item)
                                @php
                                    $isVerified = $item->seller && $item->seller->account_verification_status === 'verified';
                                    $sellerName = $item->seller ? $item->seller->shop_name : 'Unknown Seller';
                                    $orderNo = $item->order->order_number ?? $item->order_id;
                                    $productName = $item->orderItem->product_name ?? 'Product details unavailable';
                                @endphp
                                <tr class="pending-row-item interactive-row hover:bg-slate-50/30 dark:hover:bg-slate-850/10 transition-colors {{ !$isVerified ? 'bg-slate-50/10 opacity-70' : '' }}" 
                                    id="desktop-row-{{ $item->id }}"
                                    data-id="{{ $item->id }}"
                                    data-search-text="{{ strtolower($sellerName . ' ' . $orderNo . ' ' . $productName) }}"
                                    data-verified-status="{{ $isVerified ? 'verified' : 'unverified' }}"
                                    onclick="handleRowClick(event, '{{ $item->id }}')">
                                    
                                    <td class="px-5 py-3.5 text-center" onclick="event.stopPropagation()">
                                        @if($isVerified)
                                            <input type="checkbox" name="commission_ids[]" value="{{ $item->id }}" 
                                                   id="desktop-cb-{{ $item->id }}"
                                                   class="comm-checkbox desktop-cb rounded border-slate-300 dark:border-slate-700 text-blue-600 focus:ring-blue-500 w-4 h-4 cursor-pointer"
                                                   data-earning="{{ $item->seller_earning }}"
                                                   data-seller-id="{{ $item->seller_id }}"
                                                   data-seller-name="{{ $sellerName }}"
                                                   data-order-no="{{ $orderNo }}"
                                                   onchange="syncSelection('{{ $item->id }}', this.checked)">
                                        @else
                                            <div class="tooltip-trigger relative inline-block">
                                                <input type="checkbox" disabled 
                                                       class="rounded border-slate-200 dark:border-slate-850 text-slate-300 dark:text-slate-700 cursor-not-allowed w-4 h-4">
                                                <div class="tooltip-content absolute bottom-full left-1/2 -translate-x-1/2 mb-1.5 w-40 p-2 bg-slate-900 text-white text-[10px] rounded shadow-lg opacity-0 visibility-hidden transition-all duration-150 transform translate-y-1 pointer-events-none z-10 text-center leading-normal">
                                                    Verify bank details on seller profile first.
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-xs text-slate-400 dark:text-slate-500 whitespace-nowrap">
                                        {{ $item->updated_at->format('d M Y, H:i') }}
                                    </td>
                                    <td class="px-5 py-3.5 font-mono font-semibold text-slate-800 dark:text-slate-100 whitespace-nowrap text-xs">
                                        #{{ $orderNo }}
                                    </td>
                                    <td class="px-5 py-3.5 whitespace-nowrap font-medium text-xs">
                                        @if($item->seller)
                                            <a href="{{ route('admin.sellers.show', $item->seller) }}" class="text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1 font-bold" onclick="event.stopPropagation()">
                                                {{ $sellerName }}
                                            </a>
                                        @else
                                            <span class="text-slate-400 italic">Unknown Seller</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <p class="font-bold text-slate-800 dark:text-slate-200 truncate max-w-xs leading-normal text-xs">{{ $productName }}</p>
                                        <p class="text-[11px] text-slate-400 dark:text-slate-500 flex items-center gap-1.5 mt-0.5">
                                            <span>₹{{ number_format($item->product_price, 2) }} x {{ $item->quantity }}</span>
                                            <span class="text-slate-300 dark:text-slate-700">|</span>
                                            <span>Comm: {{ $item->commission_rate }}%</span>
                                        </p>
                                    </td>
                                    <td class="px-5 py-3.5 font-bold text-slate-800 dark:text-slate-100 text-right whitespace-nowrap text-sm font-mono">
                                        ₹{{ number_format($item->seller_earning, 2) }}
                                    </td>
                                    <td class="px-5 py-3.5 text-center whitespace-nowrap" onclick="event.stopPropagation()">
                                        @if($isVerified)
                                            <span class="inline-flex items-center gap-0.5 px-2.5 py-0.5 rounded bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 text-[10px] font-bold uppercase">
                                                Verified
                                            </span>
                                        @else
                                            <a href="{{ $item->seller ? route('admin.sellers.show', $item->seller) : '#' }}" 
                                               class="inline-flex items-center gap-0.5 px-2.5 py-1 rounded bg-amber-50 hover:bg-amber-100 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400 border border-amber-200 dark:border-amber-800 transition-colors shadow-sm text-[10px] font-bold uppercase">
                                                Review Seller
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                    <!-- MOBILE CARD VIEW LIST -->
            <div class="md:hidden space-y-3" id="pendingCommissionsCards">
                @forelse($pendingCommissions as $item)
                    @php
                        $isVerified = $item->seller && $item->seller->account_verification_status === 'verified';
                        $sellerName = $item->seller ? $item->seller->shop_name : 'Unknown Seller';
                        $orderNo = $item->order->order_number ?? $item->order_id;
                        $productName = $item->orderItem->product_name ?? 'Product details unavailable';
                    @endphp
                    <div class="pending-row-item interactive-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-3.5 rounded-xl flex gap-3 shadow-sm relative transition-all duration-150 {{ !$isVerified ? 'opacity-70' : '' }}" 
                         id="mobile-card-{{ $item->id }}"
                         data-id="{{ $item->id }}"
                         data-search-text="{{ strtolower($sellerName . ' ' . $orderNo . ' ' . $productName) }}"
                         data-verified-status="{{ $isVerified ? 'verified' : 'unverified' }}"
                         onclick="toggleCardSelection(event, '{{ $item->id }}')">
                        
                        <!-- Checkbox -->
                        <div class="shrink-0" onclick="event.stopPropagation()">
                            @if($isVerified)
                                <input type="checkbox" name="commission_ids[]" value="{{ $item->id }}" 
                                       id="mobile-cb-{{ $item->id }}"
                                       class="comm-checkbox mobile-cb rounded border-slate-300 dark:border-slate-700 text-blue-600 focus:ring-blue-500 w-4.5 h-4.5 cursor-pointer"
                                       data-earning="{{ $item->seller_earning }}"
                                       data-seller-id="{{ $item->seller_id }}"
                                       data-seller-name="{{ $sellerName }}"
                                       data-order-no="{{ $orderNo }}"
                                       onchange="syncSelection('{{ $item->id }}', this.checked)">
                            @else
                                <input type="checkbox" disabled class="rounded border-slate-205 dark:border-slate-850 text-slate-300 dark:text-slate-700 cursor-not-allowed w-4.5 h-4.5">
                            @endif
                        </div>

                        <!-- Card Info -->
                        <div class="flex-1 min-w-0 space-y-2">
                            <div class="flex justify-between items-start gap-2">
                                <div>
                                    <span class="font-mono font-bold text-slate-800 dark:text-slate-100 text-sm">#{{ $orderNo }}</span>
                                    <span class="text-xs text-slate-400 dark:text-slate-500 block mt-0.5">{{ $item->updated_at->format('d M Y, H:i') }}</span>
                                </div>
                                <span class="text-sm font-extrabold text-slate-800 dark:text-white block font-mono">₹{{ number_format($item->seller_earning, 2) }}</span>
                            </div>

                            <div class="flex justify-between items-center text-xs border-t border-slate-100 dark:border-slate-800 pt-2">
                                <a href="{{ route('admin.sellers.show', $item->seller) }}" class="text-blue-600 dark:text-blue-400 font-bold hover:underline" onclick="event.stopPropagation()">
                                    {{ $sellerName }}
                                </a>
                                <div onclick="event.stopPropagation()">
                                    @if($isVerified)
                                        <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 uppercase">Verified</span>
                                    @else
                                        <a href="{{ $item->seller ? route('admin.sellers.show', $item->seller) : '#' }}" 
                                           class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400 border border-amber-200 dark:border-amber-800 transition-colors uppercase">Verify</a>
                                    @endif
                                </div>
                            </div>

                            <div class="text-xs text-slate-400 dark:text-slate-500 truncate leading-tight font-medium">
                                {{ $productName }} ({{ $item->quantity }}x, Comm: {{ $item->commission_rate }}%)
                            </div>
                        </div>
                    </div>
                @empty
                    <!-- Managed by JS alerts -->
                @endforelse
            </div>        </div>

            <!-- EMPTY STATES FOR SEARCH RESULTS -->
            @if($pendingCommissions->isEmpty())
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-xl text-center text-slate-400 dark:text-slate-500 italic">
                    <div class="flex flex-col items-center justify-center space-y-1">
                        <i class="fa-solid fa-circle-check text-2xl text-slate-200 dark:text-slate-800"></i>
                        <span class="font-medium text-xs">No pending order payouts found. All delivered seller orders settled.</span>
                    </div>
                </div>
            @endif

            <div id="noResultsRow" class="hidden bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-xl text-center text-slate-400 dark:text-slate-500 italic">
                <div class="flex flex-col items-center justify-center space-y-1">
                    <i class="fa-solid fa-magnifying-glass text-2xl text-slate-200 dark:text-slate-800"></i>
                    <span class="font-medium text-xs">No pending payouts match your query. Try resetting filters.</span>
                </div>
            </div>
        </form>
    </div>

    <!-- DISBURSED HISTORY LOGS TAB CONTENT -->
    <div id="disbursed-history-tab" class="hidden space-y-3">
        <!-- DESKTOP COMPLETED PAYOUTS LEDGER TABLE -->
        <div class="hidden md:block bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-800/50 text-xs font-semibold uppercase tracking-wider text-slate-455 dark:text-slate-500">
                            <th class="px-5 py-3.5">Date</th>
                            <th class="px-5 py-3.5">Payout Reference</th>
                            <th class="px-5 py-3.5">Seller (Shop)</th>
                            <th class="px-5 py-3.5">Payment Method</th>
                            <th class="px-5 py-3.5">UTR / Txn Ref</th>
                            <th class="px-5 py-3.5 text-right">Settled Amount</th>
                            <th class="px-5 py-3.5">Settled Orders</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-600 dark:text-slate-300">
                        @forelse($payouts as $item)
                            <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-850/10 transition-colors">
                                <td class="px-5 py-3.5 text-xs text-slate-400 dark:text-slate-500 whitespace-nowrap">
                                    {{ $item->payout_date->format('d M Y, H:i') }}
                                </td>
                                <td class="px-5 py-3.5 font-mono font-semibold text-slate-800 dark:text-slate-200 whitespace-nowrap text-xs">
                                    #{{ $item->ulid }}
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap font-medium text-xs">
                                    @if($item->seller)
                                        <a href="{{ route('admin.sellers.show', $item->seller) }}" class="text-blue-600 dark:text-blue-450 hover:underline font-bold">
                                            {{ $item->seller->shop_name }}
                                        </a>
                                    @else
                                        <span class="text-slate-400 italic">Unknown Seller</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $item->payment_method === 'bank_transfer' ? 'bg-blue-50 text-blue-700 dark:bg-blue-955/20 dark:text-blue-300 border border-blue-200/40' : ($item->payment_method === 'upi' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-955/20 dark:text-emerald-400 border border-emerald-200/40' : 'bg-slate-50 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200/40') }}">
                                        {{ str_replace('_', ' ', $item->payment_method) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-xs font-mono font-medium whitespace-nowrap">
                                    {{ $item->transaction_reference ?? 'N/A' }}
                                </td>
                                <td class="px-5 py-3.5 font-bold text-emerald-600 dark:text-emerald-450 text-right whitespace-nowrap text-sm font-mono">
                                    ₹{{ number_format($item->amount, 2) }}
                                </td>
                                <td class="px-5 py-3.5 text-xs max-w-xs truncate text-slate-400" title="{{ $item->commissions->map(fn($c) => '#' . ($c->order->order_number ?? $c->order_id))->unique()->implode(', ') }}">
                                    @php
                                        $orderRefs = $item->commissions->map(fn($c) => '#' . ($c->order->order_number ?? $c->order_id))->unique();
                                    @endphp
                                    @if($orderRefs->isEmpty())
                                        <span class="text-slate-400 italic">No linked orders</span>
                                    @else
                                        {{ $orderRefs->implode(', ') }}
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-8 text-center text-slate-400 dark:text-slate-550 italic">No payout records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- MOBILE VIEW FOR COMPLETED PAYOUTS LEDGER -->
        <div class="md:hidden space-y-3">
            @forelse($payouts as $item)
                @php
                    $sellerName = $item->seller ? $item->seller->shop_name : 'Unknown Seller';
                    $orderRefs = $item->commissions->map(fn($c) => '#' . ($c->order->order_number ?? $c->order_id))->unique();
                @endphp
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-3.5 rounded-xl space-y-2">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="font-mono font-bold text-slate-800 dark:text-slate-100 text-sm">#{{ $item->ulid }}</span>
                            <span class="text-xs text-slate-450 dark:text-slate-500 block mt-0.5">{{ $item->payout_date->format('d M Y, H:i') }}</span>
                        </div>
                        <span class="text-sm font-extrabold text-emerald-600 dark:text-emerald-450 font-mono">₹{{ number_format($item->amount, 2) }}</span>
                    </div>

                    <div class="flex items-center justify-between text-xs border-t border-slate-100 dark:border-slate-800/80 pt-2">
                        <a href="{{ $item->seller ? route('admin.sellers.show', $item->seller) : '#' }}" class="text-blue-600 dark:text-blue-400 font-bold hover:underline">
                            {{ $sellerName }}
                        </a>
                        <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400 uppercase">
                            {{ str_replace('_', ' ', $item->payment_method) }}
                        </span>
                    </div>

                    <div class="text-xs text-slate-550 dark:text-slate-400 flex items-center gap-1.5">
                        <span class="text-slate-400 font-medium">Ref:</span>
                        <span class="font-mono font-semibold">{{ $item->transaction_reference ?? 'N/A' }}</span>
                    </div>

                    <div class="text-xs text-slate-550 dark:text-slate-400 flex items-center gap-1.5 max-w-xs truncate">
                        <span class="text-slate-400 font-medium">Orders:</span>
                        <span class="font-semibold">{{ $orderRefs->implode(', ') }}</span>
                    </div>
                </div>            </div>
            @empty
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 rounded-xl text-center text-slate-400 dark:text-slate-500 italic">
                    No completed payout logs found.
                </div>
            @endforelse
        </div>

        @if($payouts->hasPages())
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-2 rounded-xl flex justify-center">
                {{ $payouts->links() }}
            </div>
        @endif
    </div>
</div>

<script>
    function switchTab(activeId, inactiveId) {
        document.getElementById(activeId).classList.remove('hidden');
        document.getElementById(inactiveId).classList.add('hidden');
        
        if (activeId === 'pending-payouts-tab') {
            document.getElementById('pending-tab-btn').className = "pb-3 text-sm font-bold border-b-2 border-blue-600 text-blue-600 dark:text-blue-400 focus:outline-none transition-all flex items-center gap-2 cursor-pointer bg-transparent border-t-0 border-x-0";
            document.getElementById('history-tab-btn').className = "pb-3 text-sm font-semibold border-b-2 border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300 focus:outline-none transition-all flex items-center gap-2 cursor-pointer bg-transparent border-t-0 border-x-0";
        } else {
            document.getElementById('history-tab-btn').className = "pb-3 text-sm font-bold border-b-2 border-blue-600 text-blue-600 dark:text-blue-400 focus:outline-none transition-all flex items-center gap-2 cursor-pointer bg-transparent border-t-0 border-x-0";
            document.getElementById('pending-tab-btn').className = "pb-3 text-sm font-semibold border-b-2 border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300 focus:outline-none transition-all flex items-center gap-2 cursor-pointer bg-transparent border-t-0 border-x-0";
        }
    }

    function toggleSelectAll(masterCheckbox) {
        const isChecked = masterCheckbox.checked;
        
        const selectAllDesktop = document.getElementById('select-all-desktop');
        const selectAllMobile = document.getElementById('select-all-mobile');
        if (selectAllDesktop) selectAllDesktop.checked = isChecked;
        if (selectAllMobile) selectAllMobile.checked = isChecked;

        const visibleRows = Array.from(document.querySelectorAll('.pending-row-item')).filter(row => row.style.display !== 'none');
        
        visibleRows.forEach(row => {
            const id = row.getAttribute('data-id');
            const cb = document.getElementById('desktop-cb-' + id);
            if (cb && !cb.disabled) {
                syncSelection(id, isChecked);
            }
        });
    }

    function syncSelection(id, checked) {
        const desktopCb = document.getElementById('desktop-cb-' + id);
        const mobileCb = document.getElementById('mobile-cb-' + id);
        
        if (desktopCb) desktopCb.checked = checked;
        if (mobileCb) mobileCb.checked = checked;
        
        const card = document.getElementById('mobile-card-' + id);
        const row = document.getElementById('desktop-row-' + id);
        
        if (card) {
            if (checked) {
                card.classList.add('ring-1', 'ring-blue-500/30', 'border-blue-500', 'bg-blue-50/5', 'dark:bg-blue-955/5');
            } else {
                card.classList.remove('ring-1', 'ring-blue-500/30', 'border-blue-500', 'bg-blue-50/5', 'dark:bg-blue-955/5');
            }
        }
        if (row) {
            if (checked) {
                row.classList.add('bg-blue-50/5', 'dark:bg-blue-955/5');
            } else {
                row.classList.remove('bg-blue-50/5', 'dark:bg-blue-955/5');
            }
        }
        
        updateSelectedTotals();
    }

    function handleRowClick(event, id) {
        const target = event.target;
        if (target.tagName === 'A' || target.tagName === 'BUTTON' || target.tagName === 'INPUT' || target.closest('a') || target.closest('button')) {
            return;
        }
        
        const desktopCb = document.getElementById('desktop-cb-' + id);
        if (desktopCb && !desktopCb.disabled) {
            syncSelection(id, !desktopCb.checked);
        }
    }

    function toggleCardSelection(event, id) {
        const target = event.target;
        if (target.tagName === 'A' || target.tagName === 'BUTTON' || target.tagName === 'INPUT' || target.closest('a') || target.closest('button')) {
            return;
        }
        
        const mobileCb = document.getElementById('mobile-cb-' + id);
        if (mobileCb && !mobileCb.disabled) {
            syncSelection(id, !mobileCb.checked);
        }
    }

    function clearSelections() {
        const checkboxes = document.querySelectorAll('.comm-checkbox');
        checkboxes.forEach(cb => {
            if (cb.checked) {
                cb.checked = false;
                const id = cb.closest('.pending-row-item').getAttribute('data-id');
                syncSelection(id, false);
            }
        });
        
        const selectAllDesktop = document.getElementById('select-all-desktop');
        const selectAllMobile = document.getElementById('select-all-mobile');
        if (selectAllDesktop) selectAllDesktop.checked = false;
        if (selectAllMobile) selectAllMobile.checked = false;
        
        updateSelectedTotals();
    }

    function updateSelectedTotals() {
        const checkboxes = document.querySelectorAll('.desktop-cb:checked');
        const activeCheckboxes = checkboxes.length > 0 ? checkboxes : document.querySelectorAll('.mobile-cb:checked');
        
        let total = 0;
        let count = activeCheckboxes.length;
        
        activeCheckboxes.forEach(cb => {
            total += parseFloat(cb.getAttribute('data-earning') || 0);
        });

        // Update Toolbar DOM
        document.getElementById('selected-count-toolbar').innerText = count;
        document.getElementById('selected-total-toolbar').innerText = '₹' + total.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        
        const defaultToolbar = document.getElementById('default-toolbar');
        const bulkToolbar = document.getElementById('bulk-toolbar');
        const submitBtn = document.getElementById('toolbar-submit-btn');
        
        if (count > 0) {
            defaultToolbar.classList.add('hidden');
            bulkToolbar.classList.remove('hidden');
            
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        } else {
            defaultToolbar.classList.remove('hidden');
            bulkToolbar.classList.add('hidden');
            
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
        }

        // Sync main Select-All state
        const allActiveCheckboxes = Array.from(document.querySelectorAll('.comm-checkbox:not(:disabled)')).filter(cb => cb.closest('.pending-row-item').style.display !== 'none');
        const uniqueActiveCount = allActiveCheckboxes.length / 2;
        
        const selectAllDesktop = document.getElementById('select-all-desktop');
        const selectAllMobile = document.getElementById('select-all-mobile');
        
        const isAllSelected = count === uniqueActiveCount && uniqueActiveCount > 0;
        
        if (selectAllDesktop) selectAllDesktop.checked = isAllSelected;
        if (selectAllMobile) selectAllMobile.checked = isAllSelected;
    }

    function filterPendingTable() {
        const query = document.getElementById('pendingSearchInput').value.toLowerCase();
        const verification = $('#verificationFilter').val();
        const rows = document.querySelectorAll('.pending-row-item');
        
        let visibleCount = 0;
        
        rows.forEach(row => {
            const searchText = row.getAttribute('data-search-text') || '';
            const status = row.getAttribute('data-verified-status') || '';
            
            const matchesQuery = searchText.includes(query);
            const matchesVerification = (verification === 'all') || 
                                        (verification === 'verified' && status === 'verified') || 
                                        (verification === 'unverified' && status === 'unverified');
            
            if (matchesQuery && matchesVerification) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
                const inputs = row.querySelectorAll('.comm-checkbox');
                inputs.forEach(input => {
                    if (input.checked) {
                        input.checked = false;
                    }
                });
            }
        });

        const noResultsRow = document.getElementById('noResultsRow');
        const totalRowsCount = document.querySelectorAll('.pending-row-item').length;
        
        if (totalRowsCount > 0) {
            const uniqueVisibleCount = visibleCount / 2;
            if (uniqueVisibleCount === 0) {
                noResultsRow.classList.remove('hidden');
                noResultsRow.style.display = '';
            } else {
                noResultsRow.classList.add('hidden');
                noResultsRow.style.display = 'none';
            }
        }
        
        updateSelectedTotals();
    }

    function resetFilters() {
        document.getElementById('pendingSearchInput').value = '';
        $('#verificationFilter').val('all').trigger('change.select2');
        filterPendingTable();
    }

    function handleFormSubmit() {
        const submitBtn = document.getElementById('toolbar-submit-btn');
        const spinner = document.getElementById('submit-btn-spinner');
        const textSpan = document.getElementById('submit-btn-text');
        
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
        if (spinner) spinner.classList.remove('hidden');
        if (textSpan) textSpan.innerText = 'Wait...';
        
        return true;
    }

    // Set up Select2 change listeners
    $(document).ready(function() {
        $('#verificationFilter').on('change', function() {
            filterPendingTable();
        });
    });
</script>
@endsection
