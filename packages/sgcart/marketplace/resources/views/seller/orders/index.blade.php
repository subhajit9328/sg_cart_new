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
>
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
