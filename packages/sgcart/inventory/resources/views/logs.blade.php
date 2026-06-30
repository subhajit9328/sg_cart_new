@extends('layouts.admin')

@section('title', 'Stock History Log — SGCart Admin')

@section('content')
<!-- Page Header -->
<div class="flex flex-wrap items-end justify-between gap-3 mb-6">
    <div>
        <h1 class="font-display text-2xl font-bold">Stock History Log</h1>
        <x-breadcrumbs :items="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Inventory', 'url' => route('admin.inventory.index')],
            ['label' => 'Stock History Log']
        ]" />
    </div>
    <a href="{{ route('admin.inventory.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 text-sm font-semibold transition-colors shadow-xs no-underline">
        <i class="fa-solid fa-boxes-stacked text-blue-500"></i> View Stock Levels
    </a>
</div>@php
    $headers = [
        ['label' => 'Date / Time', 'key' => 'created_at', 'sortable' => true],
        ['label' => 'Product / Item details', 'key' => 'product_id', 'sortable' => false],
        ['label' => 'Movement', 'key' => 'quantity', 'sortable' => true, 'align' => 'center'],
        ['label' => 'Ledger Stock', 'key' => 'after_stock', 'sortable' => true, 'align' => 'center'],
        ['label' => 'Action / Source', 'key' => 'action', 'sortable' => true],
        ['label' => 'Operator', 'key' => 'user_id', 'sortable' => false],
        ['label' => 'Reason / Details', 'key' => 'reason', 'sortable' => false],
    ];
@endphp

<x-data-table
    title="All Stock Movements"
    :totalCount="$logs->total()"
    searchPlaceholder="Search by product, SKU, operator..."
    action="{{ route('admin.inventory.logs') }}"
    tableId="logsTableWrapper"
    searchInputId="logSearchInput"
    totalCountId="logsTotalCount"
    :items="$logs"
    :headers="$headers"
>
    @forelse($logs as $log)
        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/15 transition-colors">
            <!-- Date -->
            <td class="px-5 py-3.5 whitespace-nowrap text-slate-400 font-mono text-xs">
                {{ \Carbon\Carbon::parse($log->created_at)->format('d M Y, H:i') }}
            </td>
            <!-- Product/Variant details -->
            <td class="px-5 py-3.5 text-slate-800 dark:text-slate-200">
                <div class="min-w-0">
                    @if($log->product)
                        <a href="{{ route('admin.products.show', $log->product) }}" class="font-bold text-xs text-blue-600 dark:text-blue-400 hover:underline">
                            {{ $log->product->name }}
                        </a>
                    @else
                        <span class="font-bold text-xs text-slate-400">Deleted Product</span>
                    @endif
                    
                    @if($hasVariants && $log->variant)
                        @php
                            $attrs = [];
                            if($log->variant->color) $attrs[] = $log->variant->color->name;
                            if($log->variant->size) $attrs[] = $log->variant->size->code;
                        @endphp
                        @if(count($attrs) > 0)
                            <span class="text-[10px] text-slate-450 dark:text-slate-500 font-bold ml-1.5">
                                ({{ implode(' / ', $attrs) }})
                            </span>
                        @endif
                    @endif

                    <div class="font-mono text-[9px] text-slate-450 dark:text-slate-500 mt-0.5">
                        SKU: {{ ($hasVariants && $log->variant && $log->variant->sku) ? $log->variant->sku : ($log->product->sku ?? '—') }}
                    </div>
                </div>
            </td>
            <!-- Movement -->
            <td class="px-5 py-3.5 text-center whitespace-nowrap font-mono font-bold">
                @if($log->quantity > 0)
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200/30">
                        +{{ $log->quantity }}
                    </span>
                @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 border border-rose-200/30">
                        {{ $log->quantity }}
                    </span>
                @endif
            </td>
            <!-- Ledger Stock -->
            <td class="px-5 py-3.5 text-center whitespace-nowrap text-xs text-slate-500 dark:text-slate-400 font-mono">
                <span class="text-slate-400">{{ $log->before_stock }}</span>
                <i class="fa-solid fa-arrow-right text-[9px] mx-1.5 text-slate-350 dark:text-slate-600"></i>
                <span class="font-bold text-slate-700 dark:text-slate-300">{{ $log->after_stock }}</span>
            </td>
            <!-- Action -->
            <td class="px-5 py-3.5 whitespace-nowrap">
                @if($log->action === 'manual_adjustment')
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-200/20">
                        <i class="fa-solid fa-user-pen text-[9px]"></i> Manual Adjustment
                    </span>
                @elseif($log->action === 'order_sale')
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 border border-blue-200/20">
                        <i class="fa-solid fa-cart-shopping text-[9px]"></i> Customer Purchase
                    </span>
                @elseif($log->action === 'order_refund')
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200/20">
                        <i class="fa-solid fa-rotate-left text-[9px]"></i> Restocked Order
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-655 dark:text-slate-350 border border-slate-200/50">
                        {{ ucfirst($log->action) }}
                    </span>
                @endif
            </td>
            <!-- User Operator -->
            <td class="px-5 py-3.5 text-slate-600 dark:text-slate-300 font-semibold whitespace-nowrap text-xs">
                {{ $log->user->name ?? 'System Event' }}
            </td>
            <!-- Reason -->
            <td class="px-5 py-3.5 text-slate-550 dark:text-slate-400 max-w-xs truncate text-xs" title="{{ $log->reason }}">
                {{ $log->reason ?: '—' }}
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="7" class="px-5 py-20 text-center text-slate-400 dark:text-slate-500 bg-slate-50/20 dark:bg-slate-900/50">
                <i class="fa-solid fa-clock-rotate-left text-3xl mb-2 opacity-20 block"></i>
                <span class="font-medium text-xs block mb-0.5">No stock movements found</span>
                <span class="text-[10px] text-slate-400 dark:text-slate-500">Stock changes will appear here in chronological order.</span>
            </td>
        </tr>
    @endforelse
</x-data-table>
@endsection
