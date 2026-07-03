@extends('layouts.admin')

@section('title', 'Conversion & Abandonment Funnel — SGCart Admin')

@section('content')

{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900 dark:text-slate-50 font-display flex items-center gap-2">
            Conversion & Abandonment Funnel
        </h1>
        <x-breadcrumbs :items="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Reporting'],
            ['label' => 'Conversion & Abandonment']
        ]" />
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
            Storefront conversion steps and cart abandonment from
            <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $date_from->format('d M Y') }}</span>
            to
            <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $date_to->format('d M Y') }}</span>
        </p>
    </div>

    <form method="GET" action="{{ route('admin.reports.conversion') }}"
          class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
        <div class="flex items-center gap-2">
            <div class="flex flex-col">
                <label class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-0.5">From</label>
                <input type="date" name="date_from" value="{{ request('date_from', $date_from->format('Y-m-d')) }}"
                       class="px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200">
            </div>
            <span class="text-slate-400 mt-4">→</span>
            <div class="flex flex-col">
                <label class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-0.5">To</label>
                <input type="date" name="date_to" value="{{ request('date_to', $date_to->format('Y-m-d')) }}"
                       class="px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200">
            </div>
        </div>
        <button type="submit" class="flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-medium shadow-lg shadow-blue-600/10 no-underline mt-4 sm:mt-auto">Apply</button>
        <a href="{{ route('admin.reports.conversion') }}" class="px-4 py-2 rounded-xl text-sm font-semibold border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 mt-4 sm:mt-auto text-center">Reset</a>
        <a href="{{ route('admin.reports.conversion.export', request()->only(['date_from', 'date_to'])) }}"
           class="px-4! py-2! rounded-xl text-sm font-semibold border! border-slate-200! !dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors mt-4 sm:mt-auto self-end sm:self-auto text-center">
            Export CSV
        </a>
    </form>
</div>

{{-- ── Quick Date Shortcuts ─────────────────────────────────────────────── --}}
<div class="flex flex-wrap gap-2 mb-6" id="dateShortcuts">
    @php
        $shortcuts = [
            'Today'        => [now()->format('Y-m-d'), now()->format('Y-m-d')],
            'Last 7 days'  => [now()->subDays(6)->format('Y-m-d'), now()->format('Y-m-d')],
            'This month'   => [now()->startOfMonth()->format('Y-m-d'), now()->format('Y-m-d')],
            'Last month'   => [now()->subMonthNoOverflow()->startOfMonth()->format('Y-m-d'), now()->subMonthNoOverflow()->endOfMonth()->format('Y-m-d')],
            'Last 90 days' => [now()->subDays(89)->format('Y-m-d'), now()->format('Y-m-d')],
            'This year'    => [now()->startOfYear()->format('Y-m-d'), now()->format('Y-m-d')],
        ];
    @endphp
    @foreach($shortcuts as $label => [$f, $t])
        <a href="{{ route('admin.reports.conversion', ['date_from' => $f, 'date_to' => $t]) }}"
           class="px-3 py-1.5 text-xs font-semibold rounded-lg border transition-colors
                  {{ request('date_from') === $f && request('date_to') === $t
                       ? 'bg-blue-600 text-white border-blue-600'
                       : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

{{-- Top Row: Conversion Funnel and Abandonment Rate --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    {{-- Conversion conversion bar --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 lg:col-span-2 shadow-sm">
        <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-funnel-dollar text-blue-500"></i>Storefront Conversion
        </h2>
        <div class="space-y-4">
            {{-- Sessions --}}
            <div>
                <div class="flex justify-between text-xs font-semibold text-slate-500 mb-1">
                    <span>1. Total Sessions (Views)</span>
                    <span>{{ number_format($funnel['sessions']) }}</span>
                </div>
                <div class="w-full bg-slate-100 dark:bg-slate-800 h-6 rounded-lg overflow-hidden">
                    <div class="bg-blue-500 h-full text-white text-xs flex items-center justify-end pr-2 font-bold" style="width: 100%">100%</div>
                </div>
            </div>
            {{-- Add to Cart --}}
            <div>
                @php $cartPct = $funnel['sessions'] > 0 ? ($funnel['cart_adds'] / $funnel['sessions']) * 100 : 0 @endphp
                <div class="flex justify-between text-xs font-semibold text-slate-500 mb-1">
                    <span>2. Add to Cart</span>
                    <span>{{ number_format($funnel['cart_adds']) }} ({{ number_format($cartPct, 1) }}%)</span>
                </div>
                <div class="w-full bg-slate-100 dark:bg-slate-800 h-6 rounded-lg overflow-hidden">
                    <div class="bg-violet-500 h-full text-white text-xs flex items-center justify-end pr-2 font-bold" style="width: {{ $cartPct }}%">{{ number_format($cartPct, 1) }}%</div>
                </div>
            </div>
            {{-- Purchases --}}
            <div>
                @php $purchasePct = $funnel['sessions'] > 0 ? ($funnel['purchases'] / $funnel['sessions']) * 100 : 0 @endphp
                <div class="flex justify-between text-xs font-semibold text-slate-500 mb-1">
                    <span>3. Completed Purchase (Orders)</span>
                    <span>{{ number_format($funnel['purchases']) }} ({{ number_format($purchasePct, 1) }}%)</span>
                </div>
                <div class="w-full bg-slate-100 dark:bg-slate-800 h-6 rounded-lg overflow-hidden">
                    <div class="bg-emerald-500 h-full text-white text-xs flex items-center justify-end pr-2 font-bold" style="width: {{ $purchasePct }}%">{{ number_format($purchasePct, 1) }}%</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Abandonment rate gauge card --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 flex flex-col items-center justify-center text-center shadow-sm">
        <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Cart Abandonment Rate</h2>
        <div class="text-4xl font-extrabold text-rose-500 font-display mb-2">{{ number_format($funnel['abandonment_rate'], 1) }}%</div>
        <p class="text-xs text-slate-400 max-w-[200px]">{{ number_format($funnel['carts_abandoned']) }} out of {{ number_format($funnel['carts_created']) }} carts abandoned without purchase.</p>
    </div>
</div>

{{-- Abandoned Carts Table --}}
<div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 overflow-hidden mb-6 shadow-sm">
    <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800">
        <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">Abandoned Carts log</h2>
    </div>

    @if($abandoned_carts->isEmpty())
        <div class="text-center py-16 text-slate-400">
            <i class="fa-solid fa-cart-arrow-down text-4xl mb-3 block"></i>
            <p class="text-sm font-medium">No abandoned carts found for this range.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-100 dark:border-slate-800">
                    <tr>
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Cart ID</th>
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Session ID</th>
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Customer Name</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Items Count</th>
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Abandoned At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($abandoned_carts as $row)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
                        <td class="px-4 py-3.5 text-sm font-semibold">#{{ $row->id }}</td>
                        <td class="px-4 py-3.5 text-sm text-slate-500 font-mono">{{ substr($row->session_id, 0, 10) }}...</td>
                        <td class="px-4 py-3.5 text-sm font-medium text-slate-900 dark:text-slate-100">{{ $row->customer_name ?? 'Guest Visitor' }}</td>
                        <td class="px-4 py-3.5 text-sm text-right font-medium">{{ number_format($row->items_count ?? 0) }}</td>
                        <td class="px-4 py-3.5 text-sm text-slate-400">{{ $row->created_at }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($abandoned_carts->hasPages())
        <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <p class="text-xs text-slate-400">
                Showing {{ $abandoned_carts->firstItem() }}–{{ $abandoned_carts->lastItem() }} of {{ $abandoned_carts->total() }} carts
            </p>
            <div class="flex items-center gap-1">
                @if($abandoned_carts->onFirstPage())
                    <span class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-700 text-slate-300 cursor-not-allowed">
                        <i class="fa-solid fa-chevron-left"></i>
                    </span>
                @else
                    <a href="{{ $abandoned_carts->previousPageUrl() }}" class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50">
                        <i class="fa-solid fa-chevron-left"></i>
                    </a>
                @endif

                @if($abandoned_carts->hasMorePages())
                    <a href="{{ $abandoned_carts->nextPageUrl() }}" class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50">
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                @else
                    <span class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-700 text-slate-300 cursor-not-allowed">
                        <i class="fa-solid fa-chevron-right"></i>
                    </span>
                @endif
            </div>
        </div>
        @endif
    @endif
</div>

@endsection
