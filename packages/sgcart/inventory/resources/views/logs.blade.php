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
</div>

<!-- Logs Ledger Card -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
    <!-- Card Header (with dynamic Search Box) -->
    <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-4 bg-slate-50/50 dark:bg-slate-900/50">
        <div class="flex items-center gap-2">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider">All Stock Movements</h2>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-300/35 dark:border-slate-700/50">
                {{ $logs->total() }} Entries
            </span>
        </div>

        <!-- Inline Search form -->
        <form action="{{ route('admin.inventory.logs') }}" method="GET" class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
            <!-- Search Box -->
            <div class="relative flex items-center w-full sm:w-64">
                <i class="fa-solid fa-magnifying-glass absolute left-3 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 pl-8 pr-3 text-xs placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-850 dark:text-slate-100"
                    placeholder="Search by product, SKU, operator...">
            </div>

            @if(request()->filled('search'))
                <a href="{{ route('admin.inventory.logs') }}" class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-600 dark:bg-rose-950/20 dark:border-rose-900/30 dark:text-rose-400 text-xs font-semibold transition-colors text-center no-underline flex items-center justify-center">
                    Clear
                </a>
            @endif
            <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs font-semibold shadow-xs transition-colors cursor-pointer border-none">
                Filter
            </button>
        </form>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-800/50">
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">Date / Time</th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">Product / Item details</th>
                    <th class="px-5 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap w-24">Movement</th>
                    <th class="px-5 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap w-36">Ledger Stock</th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap w-40">Action / Source</th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">Operator</th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">Reason / Details</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
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
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-650 dark:text-slate-350 border border-slate-200/50">
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
                            @if(request('search'))
                                <span class="text-[10px] text-slate-400 dark:text-slate-500">Try adjusting your filters or search keywords.</span>
                            @else
                                <span class="text-[10px] text-slate-400 dark:text-slate-500">Stock changes will appear here in chronological order.</span>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination Footer -->
    @if($logs->hasPages())
        <div class="px-5 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
            {{ $logs->links() }}
        </div>
    @endif
</div>
@endsection
