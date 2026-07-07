@extends('marketplace::layouts.seller')

@section('title', 'My Orders — Seller Portal')

@section('content')
<!-- Page Header -->
<div class="flex flex-wrap items-end justify-between gap-3 mb-6">
    <div>
        <h1 class="font-display text-2xl font-bold">My Orders</h1>
        <p class="text-sm text-slate-400 mt-0.5">Manage customer orders containing your products.</p>
    </div>
</div>

@php
    $headers = [
        ['label' => 'Order ID', 'key' => 'id', 'sortable' => false],
        ['label' => 'Customer', 'key' => 'customer', 'sortable' => false],
        ['label' => 'Total items', 'key' => 'items_count', 'sortable' => false],
        ['label' => 'Date placed', 'key' => 'created_at', 'sortable' => false],
        ['label' => 'Status', 'key' => 'status', 'sortable' => false],
        ['label' => 'Actions', 'key' => 'actions', 'sortable' => false, 'align' => 'right'],
    ];
@endphp

<x-data-table
    title="Customer Orders"
    :totalCount="$orders->total()"
    searchPlaceholder="Search orders…"
    action="{{ route('seller.orders.index') }}"
    tableId="ordersTableWrapper"
    searchInputId="orderSearchInput"
    totalCountId="ordersTotalCount"
    clearBtnId="ordersClearBtn"
    clearBtnWrapperId="ordersClearBtnWrapper"
    :items="$orders"
    :headers="$headers"
    :filterKeys="['status', 'date_range']"
    refreshBtn="true"
>
    <x-slot name="advancedFilters">
        <!-- Order Status Filter -->
        <div class="flex flex-col gap-1.5 w-full">
            <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Order Status</span>
            <x-select2 
                name="status" 
                id="order_status_filter"
                placeholder="All Order Statuses"
                :selected="request('status')"
                :compact="true"
                :allowClear="false"
                :searchable="false"
            >
                <option value="New Order" {{ request('status') === 'New Order' ? 'selected' : '' }}>New Order</option>
                <option value="Processed" {{ request('status') === 'Processed' ? 'selected' : '' }}>Processed</option>
                <option value="Shipped" {{ request('status') === 'Shipped' ? 'selected' : '' }}>Shipped</option>
                <option value="Out for Delivery" {{ request('status') === 'Out for Delivery' ? 'selected' : '' }}>Out for Delivery</option>
                <option value="Delivered" {{ request('status') === 'Delivered' ? 'selected' : '' }}>Delivered</option>
                <option value="Cancelled" {{ request('status') === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
            </x-select2>
        </div>

        <!-- Date Range Filter -->
        <div class="flex flex-col gap-1.5 w-full col-span-1 sm:col-span-2 md:col-span-3">
            <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Date Range</span>
            <x-date-picker 
                id="orderDateRangePicker" 
                name="date_range" 
                enableTime="true" 
                time_24hr="false" 
                dateFormat="d-m-Y h:i K" 
                placeholder="Filter by date & time…" 
                width="w-full" 
            />
        </div>
    </x-slot>
    @forelse($orders as $order)
        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/15 transition-colors">
            <td class="px-5 py-3.5 font-semibold whitespace-nowrap text-slate-800 dark:text-slate-100">
                #{{ $order->order_number ?? $order->id }}
            </td>
            <td class="px-5 py-3.5 text-xs text-slate-600 dark:text-slate-300 whitespace-nowrap">{{ $order->customer->name ?? 'Guest Customer' }}</td>
            <td class="px-5 py-3.5 text-xs text-slate-600 dark:text-slate-300 whitespace-nowrap font-medium">{{ $order->items->count() }} items</td>
            <td class="px-5 py-3.5 text-xs text-slate-500 dark:text-slate-400 whitespace-nowrap">{{ $order->created_at->format('d M Y, H:i A') }}</td>
            <td class="px-5 py-3.5 whitespace-nowrap">
                <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $order->status === 'Completed' || $order->status === 'Delivered' ? 'bg-emerald-50 text-emerald-700' : ($order->status === 'Cancelled' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700') }}">
                    {{ is_object($order->status) ? ($order->status->value ?? $order->status->name) : $order->status }}
                </span>
            </td>
            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                <a href="{{ route('seller.orders.show', $order) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold no-underline transition-colors">
                    Manage Order
                </a>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="6" class="px-5 py-8 text-center text-slate-400 dark:text-slate-600 italic">No customer orders logged.</td>
        </tr>
    @endforelse
</x-data-table>
@endsection
