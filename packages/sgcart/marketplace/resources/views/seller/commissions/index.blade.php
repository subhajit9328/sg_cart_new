@extends('marketplace::layouts.seller')

@section('title', 'My Earnings — Seller Portal')
@section('loader_text', 'Updating statistics...')

@section('content')
<!-- Page Header -->
<div class="flex flex-wrap items-end justify-between gap-3 mb-6">
    <div>
        <h1 class="font-display text-2xl font-bold">My Earnings</h1>
        <p class="text-sm text-slate-400 mt-0.5">Track your payouts, commissions, and revenue statistics.</p>
    </div>
</div><!-- Earnings Statistics Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
    <!-- Gross Sales Volume -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm flex items-center justify-between">
        <div class="min-w-0">
            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider truncate" title="Gross Sales Volume">Gross Sales</p>
            <h3 class="text-lg font-bold text-slate-800 dark:text-white mt-1 truncate">₹{{ number_format($totalSales, 2) }}</h3>
        </div>
        <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-950/40 flex items-center justify-center text-blue-600 dark:text-blue-400 flex-shrink-0 ml-2">
            <i class="fa-solid fa-chart-line text-base"></i>
        </div>
    </div>

    <!-- Platform Fee -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm flex items-center justify-between">
        <div class="min-w-0">
            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider truncate" title="Platform Fee (Commission)">Platform Fee</p>
            <h3 class="text-lg font-bold text-slate-800 dark:text-white mt-1 truncate">₹{{ number_format($totalCommissionPaid, 2) }}</h3>
        </div>
        <div class="w-10 h-10 rounded-lg bg-rose-50 dark:bg-rose-955/20 flex items-center justify-center text-rose-600 dark:text-rose-400 flex-shrink-0 ml-2">
            <i class="fa-solid fa-percent text-base"></i>
        </div>
    </div>

    <!-- Lifetime Net Earnings -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm flex items-center justify-between">
        <div class="min-w-0">
            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider truncate" title="Lifetime Net Earnings">Net Earnings</p>
            <h3 class="text-lg font-bold text-slate-800 dark:text-white mt-1 truncate">₹{{ number_format($lifetimeEarnings, 2) }}</h3>
        </div>
        <div class="w-10 h-10 rounded-lg bg-slate-50 dark:bg-slate-950/30 flex items-center justify-center text-slate-500 dark:text-slate-400 flex-shrink-0 ml-2">
            <i class="fa-solid fa-coins text-base"></i>
        </div>
    </div>

    <!-- Pending Balance -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm flex items-center justify-between">
        <div class="min-w-0">
            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider truncate" title="Pending Balance (Undelivered)">Pending Bal.</p>
            <h3 class="text-lg font-bold text-amber-600 dark:text-amber-400 mt-1 truncate">₹{{ number_format($pendingBalance, 2) }}</h3>
        </div>
        <div class="w-10 h-10 rounded-lg bg-amber-50 dark:bg-amber-955/20 flex items-center justify-center text-amber-600 dark:text-amber-400 flex-shrink-0 ml-2" title="Undelivered orders">
            <i class="fa-solid fa-clock text-base"></i>
        </div>
    </div>

    <!-- Allocated Balance -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm flex items-center justify-between">
        <div class="min-w-0">
            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider truncate" title="Allocated Balance (Due/Withdrawable)">Allocated Bal.</p>
            <h3 class="text-lg font-bold text-emerald-600 dark:text-emerald-400 mt-1 truncate">₹{{ number_format($allocatedBalance, 2) }}</h3>
        </div>
        <div class="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-955/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400 flex-shrink-0 ml-2" title="Due/Withdrawable">
            <i class="fa-solid fa-circle-check text-base"></i>
        </div>
    </div>

    <!-- Settled Balance -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm flex items-center justify-between">
        <div class="min-w-0">
            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider truncate" title="Settled Balance (Paid)">Settled Bal.</p>
            <h3 class="text-lg font-bold text-indigo-600 dark:text-indigo-400 mt-1 truncate">₹{{ number_format($settledBalance, 2) }}</h3>
        </div>
        <div class="w-10 h-10 rounded-lg bg-indigo-50 dark:bg-indigo-955/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400 flex-shrink-0 ml-2" title="Paid">
            <i class="fa-solid fa-money-bill-transfer text-base"></i>
        </div>
    </div>
</div>

@php
    $headers = [
        ['label' => 'Date', 'key' => 'created_at', 'sortable' => false],
        ['label' => 'Order', 'key' => 'order_id', 'sortable' => false],
        ['label' => 'Item Details', 'key' => 'product_name', 'sortable' => false],
        ['label' => 'Subtotal', 'key' => 'subtotal', 'sortable' => false],
        ['label' => 'Comm. %', 'key' => 'commission_rate', 'sortable' => false],
        ['label' => 'Comm. Deducted', 'key' => 'commission_amount', 'sortable' => false],
        ['label' => 'My Earning', 'key' => 'seller_earning', 'sortable' => false],
        ['label' => 'Status', 'key' => 'status', 'sortable' => false],
    ];
@endphp

<x-data-table
    title="My Earnings Ledger"
    :totalCount="$ledger->total()"
    searchPlaceholder="Search earnings…"
    action="{{ route('seller.commissions') }}"
    tableId="earningsTableWrapper"
    searchInputId="earningSearchInput"
    totalCountId="earningsTotalCount"
    clearBtnId="earningsClearBtn"
    clearBtnWrapperId="earningsClearBtnWrapper"
    :items="$ledger"
    :headers="$headers"
    :filterKeys="['date_range']"
>
    <x-slot name="filters">
        <x-date-picker id="commissionDateRangePicker" name="date_range" enableTime="false" dateFormat="d-m-Y" placeholder="Filter by date range…" width="w-60" />
    </x-slot>
    @forelse($ledger as $item)
        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/15 transition-colors">
            <td class="px-5 py-3.5 text-xs text-slate-500 dark:text-slate-400 whitespace-nowrap">
                {{ $item->created_at->format('d M Y, H:i A') }}
            </td>
            <td class="px-5 py-3.5 font-semibold text-slate-800 dark:text-slate-100 whitespace-nowrap">
                #{{ $item->order->order_number ?? $item->order->id }}
            </td>
            <td class="px-5 py-3.5">
                <p class="font-semibold text-xs text-slate-800 dark:text-slate-100 leading-tight">{{ $item->orderItem->product_name }}</p>
                <p class="text-[10px] text-slate-400 mt-0.5">₹{{ number_format($item->product_price, 2) }} x {{ $item->quantity }}</p>
            </td>
            <td class="px-5 py-3.5 text-xs text-slate-600 dark:text-slate-300 whitespace-nowrap font-medium">₹{{ number_format($item->subtotal, 2) }}</td>
            <td class="px-5 py-3.5 text-xs text-slate-550 dark:text-slate-450 whitespace-nowrap font-mono">{{ $item->commission_rate }}%</td>
            <td class="px-5 py-3.5 text-xs text-rose-600 dark:text-rose-400 whitespace-nowrap font-medium">₹{{ number_format($item->commission_amount, 2) }}</td>
            <td class="px-5 py-3.5 text-xs text-emerald-600 dark:text-emerald-400 whitespace-nowrap font-bold">₹{{ number_format($item->seller_earning, 2) }}</td>
            <td class="px-5 py-3.5 whitespace-nowrap">
                <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $item->status === 'allocated' || $item->status === 'paid' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/20 dark:text-emerald-400' : ($item->status === 'cancelled' ? 'bg-rose-50 text-rose-700 dark:bg-rose-950/20 dark:text-rose-400' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/20 dark:text-amber-400') }}">
                    {{ $item->status }}
                </span>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="8" class="px-5 py-8 text-center text-slate-400 dark:text-slate-600 italic">No earnings logged.</td>
        </tr>
    @endforelse
</x-data-table>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const dateInput = document.getElementById('commissionDateRangePicker');
        const searchInput = document.getElementById('earningSearchInput');
        const dateForm = document.getElementById('commissionDateForm');

        if (searchInput && dateForm) {
            let hiddenSearch = dateForm.querySelector('input[name="search"]');
            if (!hiddenSearch) {
                hiddenSearch = document.createElement('input');
                hiddenSearch.type = 'hidden';
                hiddenSearch.name = 'search';
                dateForm.appendChild(hiddenSearch);
            }

            // Sync on typing/changes to search input
            searchInput.addEventListener('input', () => {
                hiddenSearch.value = searchInput.value;
            });
            searchInput.addEventListener('change', () => {
                hiddenSearch.value = searchInput.value;
            });

            // Also handle initial value
            hiddenSearch.value = searchInput.value;
        }

        if (dateForm) {
            dateForm.addEventListener('submit', (e) => {
                e.preventDefault();

                let hiddenSearch = dateForm.querySelector('input[name="search"]');
                if (!hiddenSearch) {
                    hiddenSearch = document.createElement('input');
                    hiddenSearch.type = 'hidden';
                    hiddenSearch.name = 'search';
                    dateForm.appendChild(hiddenSearch);
                }
                if (searchInput) {
                    hiddenSearch.value = searchInput.value;
                }

                const formData = new FormData(dateForm);
                const params = new URLSearchParams();
                for (const [key, value] of formData.entries()) {
                    if (value.trim() !== '') {
                        params.append(key, value);
                    }
                }
                const queryString = params.toString();
                const url = queryString ? `${dateForm.action}?${queryString}` : dateForm.action;

                if (typeof window.showFullPageLoader === 'function') {
                    window.showFullPageLoader();
                }
                window.location.href = url;
            });
        }

        if (dateInput && searchInput) {
            const searchForm = searchInput.form;
            if (searchForm) {
                let hiddenDate = searchForm.querySelector('input[name="date_range"]');
                if (!hiddenDate) {
                    hiddenDate = document.createElement('input');
                    hiddenDate.type = 'hidden';
                    hiddenDate.name = 'date_range';
                    searchForm.appendChild(hiddenDate);
                }

                // Sync on date changes
                dateInput.addEventListener('change', () => {
                    hiddenDate.value = dateInput.value;
                });
                dateInput.addEventListener('input', () => {
                    hiddenDate.value = dateInput.value;
                });

                // Handle flatpickr change trigger if any
                const fp = dateInput._flatpickr;
                if (fp) {
                    fp.config.onChange.push(() => {
                        hiddenDate.value = dateInput.value;
                    });
                } else {
                    // Fallback in case flatpickr is not initialized yet
                    setTimeout(() => {
                        const fpLazy = dateInput._flatpickr;
                        if (fpLazy) {
                            fpLazy.config.onChange.push(() => {
                                hiddenDate.value = dateInput.value;
                            });
                        }
                    }, 500);
                }

                // Also handle initial value
                hiddenDate.value = dateInput.value;
            }
        }
    });
</script>
@endpush

