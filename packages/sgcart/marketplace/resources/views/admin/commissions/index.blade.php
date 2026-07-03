@extends('layouts.admin')

@section('title', 'Platform Revenue — SGCart Admin')

@section('content')
<!-- Page Header -->
<div class="flex flex-wrap items-end justify-between gap-3 mb-6">
    <div>
        <h1 class="font-display text-2xl font-bold">Platform Revenue</h1>
        <x-breadcrumbs :items="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Marketplace'],
            ['label' => 'Platform Revenue']
        ]" />
    </div>
</div>

<!-- Revenue Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Gross Marketplace Sales</p>
            <h3 class="text-2xl font-bold text-slate-800 dark:text-white mt-1">${{ number_format($totalSales, 2) }}</h3>
        </div>
        <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-950/50 flex items-center justify-center text-blue-600">
            <i class="fa-solid fa-cart-shopping text-xl"></i>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Commission Collected</p>
            <h3 class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">${{ number_format($totalCommissions, 2) }}</h3>
        </div>
        <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 flex items-center justify-center text-emerald-600">
            <i class="fa-solid fa-percent text-xl"></i>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Net Vendor Earnings</p>
            <h3 class="text-2xl font-bold text-slate-800 dark:text-white mt-1">${{ number_format($totalSellerEarnings, 2) }}</h3>
        </div>
        <div class="w-12 h-12 rounded-xl bg-violet-50 dark:bg-violet-950/50 flex items-center justify-center text-violet-600">
            <i class="fa-solid fa-wallet text-xl"></i>
        </div>
    </div>
</div>

@php
    $headers = [
        ['label' => 'Date', 'key' => 'created_at', 'sortable' => false],
        ['label' => 'Order', 'key' => 'order_id', 'sortable' => false],
        ['label' => 'Seller', 'key' => 'seller', 'sortable' => false],
        ['label' => 'Product Details', 'key' => 'product_details', 'sortable' => false],
        ['label' => 'Subtotal', 'key' => 'subtotal', 'sortable' => false],
        ['label' => 'Comm. Rate', 'key' => 'commission_rate', 'sortable' => false],
        ['label' => 'Platform Revenue', 'key' => 'commission_amount', 'sortable' => false],
        ['label' => 'Seller Share', 'key' => 'seller_earning', 'sortable' => false],
        ['label' => 'Status', 'key' => 'status', 'sortable' => false],
    ];
@endphp

<x-data-table
    title="Platform Revenues Ledger"
    :totalCount="$ledger->total()"
    searchPlaceholder="Search transactions…"
    action="{{ route('admin.commissions.index') }}"
    tableId="commissionsTableWrapper"
    searchInputId="commissionSearchInput"
    totalCountId="commissionsTotalCount"
    clearBtnId="commissionsClearBtn"
    clearBtnWrapperId="commissionsClearBtnWrapper"
    :items="$ledger"
    :headers="$headers"
>
    @forelse($ledger as $item)
        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/15 transition-colors">
            <td class="px-5 py-3.5 text-xs text-slate-500 dark:text-slate-400 whitespace-nowrap">
                {{ $item->created_at->format('d M Y, H:i A') }}
            </td>
            <td class="px-5 py-3.5 font-semibold text-slate-800 dark:text-slate-100 whitespace-nowrap">
                #{{ $item->order->order_number ?? $item->order->id }}
            </td>
            <td class="px-5 py-3.5 text-xs font-semibold text-blue-600">
                {{ $item->seller ? $item->seller->shop_name : 'Unknown Seller' }}
            </td>
            <td class="px-5 py-3.5">
                <p class="font-semibold text-xs text-slate-800 dark:text-slate-100 leading-tight">{{ $item->orderItem->product_name }}</p>
                <p class="text-[10px] text-slate-400 mt-0.5">${{ number_format($item->product_price, 2) }} x {{ $item->quantity }}</p>
            </td>
            <td class="px-5 py-3.5 text-xs text-slate-600 dark:text-slate-300 whitespace-nowrap font-medium">${{ number_format($item->subtotal, 2) }}</td>
            <td class="px-5 py-3.5 text-xs text-slate-550 dark:text-slate-450 whitespace-nowrap font-mono">{{ $item->commission_rate }}%</td>
            <td class="px-5 py-3.5 text-xs text-emerald-600 dark:text-emerald-400 whitespace-nowrap font-bold">+${{ number_format($item->commission_amount, 2) }}</td>
            <td class="px-5 py-3.5 text-xs text-slate-600 dark:text-slate-300 whitespace-nowrap font-medium">${{ number_format($item->seller_earning, 2) }}</td>
            <td class="px-5 py-3.5 whitespace-nowrap">
                <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $item->status === 'allocated' || $item->status === 'paid' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/20 dark:text-emerald-400' : ($item->status === 'cancelled' ? 'bg-rose-50 text-rose-700 dark:bg-rose-950/20 dark:text-rose-400' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/20 dark:text-amber-400') }}">
                    {{ $item->status }}
                </span>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="9" class="px-5 py-8 text-center text-slate-400 dark:text-slate-600 italic">No commission transactions found.</td>
        </tr>
    @endforelse
</x-data-table>
@endsection
