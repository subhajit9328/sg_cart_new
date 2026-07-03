@extends('layouts.admin')

@section('title', 'Orders Report — SGCart Admin')

@section('content')

{{-- ── Page Header ──────────────────────────────────────────────────────── --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900 dark:text-slate-50 font-display flex items-center gap-2">
            <i class="fa-solid fa-chart-line text-blue-500"></i>
            Orders Report
        </h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
            Showing data from
            <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $date_from->format('d M Y') }}</span>
            to
            <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $date_to->format('d M Y') }}</span>
            <span class="text-slate-400">({{ $days_in_range }} {{ Str::plural('day', $days_in_range) }})</span>
        </p>
    </div>

    {{-- Date Range Filter --}}
    <form method="GET" action="{{ route('admin.reports.orders') }}"
          id="reportFilterForm"
          class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
        <div class="flex items-center gap-2">
            <div class="flex flex-col">
                <label for="date_from" class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-0.5">From</label>
                <input type="date" id="date_from" name="date_from"
                       value="{{ request('date_from', $date_from->format('Y-m-d')) }}"
                       class="px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
            </div>
            <span class="text-slate-400 mt-4">→</span>
            <div class="flex flex-col">
                <label for="date_to" class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-0.5">To</label>
                <input type="date" id="date_to" name="date_to"
                       value="{{ request('date_to', $date_to->format('Y-m-d')) }}"
                       class="px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
            </div>
        </div>
        <button type="submit"
                class="btn btn-primary px-4! py-2! rounded-xl text-sm font-semibold sm:mt-auto self-end sm:self-auto">
            <i class="fa-solid fa-filter mr-1.5"></i>Apply
        </button>
        <a href="{{ route('admin.reports.orders') }}"
           class="px-4 py-2 rounded-xl text-sm font-semibold border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors mt-4 sm:mt-auto self-end sm:self-auto text-center">
            Reset
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
        <a href="{{ route('admin.reports.orders', ['date_from' => $f, 'date_to' => $t]) }}"
           class="px-3 py-1.5 text-xs font-semibold rounded-lg border transition-colors
                  {{ request('date_from') === $f && request('date_to') === $t
                       ? 'bg-blue-600 text-white border-blue-600'
                       : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

{{-- ── Dashboard Metric Cards ───────────────────────────────────────────── --}}
<div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    {{-- Total Revenue --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 flex flex-col gap-1 transition-shadow duration-200 hover:shadow-md">
        <div class="flex items-start justify-between gap-2">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Revenue</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-slate-55 font-display leading-none mt-1">₹{{ number_format($metrics['total_revenue'], 2) }}</p>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Gross across all statuses</p>
            </div>
            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-base flex-shrink-0 bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400">
                <i class="fa-solid fa-indian-rupee-sign"></i>
            </div>
        </div>
    </div>

    {{-- Total Orders --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 flex flex-col gap-1 transition-shadow duration-200 hover:shadow-md">
        <div class="flex items-start justify-between gap-2">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Orders</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-slate-55 font-display leading-none mt-1">{{ number_format($metrics['total_orders']) }}</p>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">In selected period</p>
            </div>
            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-base flex-shrink-0 bg-violet-50 dark:bg-violet-950/40 text-violet-600 dark:text-violet-400">
                <i class="fa-solid fa-receipt"></i>
            </div>
        </div>
    </div>

    {{-- AOV --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 flex flex-col gap-1 transition-shadow duration-200 hover:shadow-md">
        <div class="flex items-start justify-between gap-2">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Avg. Order Value</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-slate-55 font-display leading-none mt-1">₹{{ number_format($metrics['avgOrderValue'], 2) }}</p>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Revenue ÷ Orders</p>
            </div>
            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-base flex-shrink-0 bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400">
                <i class="fa-solid fa-chart-bar"></i>
            </div>
        </div>
    </div>

    {{-- Items Sold --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 flex flex-col gap-1 transition-shadow duration-200 hover:shadow-md">
        <div class="flex items-start justify-between gap-2">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Items Sold</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-slate-55 font-display leading-none mt-1">{{ number_format($metrics['items_sold']) }}</p>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Total units sold</p>
            </div>
            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-base flex-shrink-0 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
        </div>
    </div>

    {{-- Processing --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 flex flex-col gap-1 transition-shadow duration-200 hover:shadow-md">
        <div class="flex items-start justify-between gap-2">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Processing</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-slate-55 font-display leading-none mt-1">{{ number_format($metrics['processing']) }}</p>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Orders being prepared</p>
            </div>
            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-base flex-shrink-0 bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
        </div>
    </div>

    {{-- Shipped --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 flex flex-col gap-1 transition-shadow duration-200 hover:shadow-md">
        <div class="flex items-start justify-between gap-2">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Shipped</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-slate-55 font-display leading-none mt-1">{{ number_format($metrics['shipped']) }}</p>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">In transit</p>
            </div>
            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-base flex-shrink-0 bg-violet-50 dark:bg-violet-950/40 text-violet-600 dark:text-violet-400">
                <i class="fa-solid fa-truck"></i>
            </div>
        </div>
    </div>

    {{-- Delivered --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 flex flex-col gap-1 transition-shadow duration-200 hover:shadow-md">
        <div class="flex items-start justify-between gap-2">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Delivered</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-slate-55 font-display leading-none mt-1">{{ number_format($metrics['delivered']) }}</p>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Successfully fulfilled</p>
            </div>
            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-base flex-shrink-0 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>
    </div>

    {{-- Cancelled --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 flex flex-col gap-1 transition-shadow duration-200 hover:shadow-md">
        <div class="flex items-start justify-between gap-2">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Cancelled</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-slate-55 font-display leading-none mt-1">{{ number_format($metrics['cancelled']) }}</p>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Orders cancelled</p>
            </div>
            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-base flex-shrink-0 bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400">
                <i class="fa-solid fa-ban"></i>
            </div>
        </div>
    </div>

</div>

{{-- ── Revenue Analytics ────────────────────────────────────────────────── --}}
<div class="mb-6">
    <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3">Revenue Analytics</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">

        {{-- Gross Revenue --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-lg flex-shrink-0 bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400">
                <i class="fa-solid fa-arrow-trend-up"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Gross Revenue</p>
                <p class="text-lg font-bold text-slate-900 dark:text-slate-50 font-display leading-tight">
                    ₹{{ number_format($revenue_analytics['gross_revenue'], 2) }}
                </p>
                <p class="text-xs text-slate-400">All orders in period</p>
            </div>
        </div>

        {{-- Net Revenue --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-lg flex-shrink-0 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Net Revenue</p>
                <p class="text-lg font-bold text-slate-900 dark:text-slate-55 font-display leading-tight">
                    ₹{{ number_format($revenue_analytics['net_revenue'], 2) }}
                </p>
                <p class="text-xs text-slate-400">Excl. cancellations</p>
            </div>
        </div>

        {{-- Refund Amount --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-lg flex-shrink-0 bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400">
                <i class="fa-solid fa-rotate-left"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Refund Amount</p>
                <p class="text-lg font-bold text-slate-900 dark:text-slate-55 font-display leading-tight">
                    ₹{{ number_format($revenue_analytics['refund_amount'], 2) }}
                </p>
                <p class="text-xs text-slate-400">Cancelled order value</p>
            </div>
        </div>

        {{-- Revenue Growth --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-lg flex-shrink-0
                {{ $revenue_analytics['revenue_growth'] === null ? 'bg-slate-100 dark:bg-slate-800 text-slate-400' : ($revenue_analytics['revenue_growth'] >= 0 ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400' : 'bg-rose-50 dark:bg-rose-950/40 text-rose-555 dark:text-rose-400') }}">
                @if($revenue_analytics['revenue_growth'] === null)
                    <i class="fa-solid fa-minus"></i>
                @elseif($revenue_analytics['revenue_growth'] >= 0)
                    <i class="fa-solid fa-arrow-up"></i>
                @else
                    <i class="fa-solid fa-arrow-down"></i>
                @endif
            </div>
            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Revenue Growth</p>
                <p class="text-lg font-bold font-display leading-tight
                    {{ $revenue_analytics['revenue_growth'] === null ? 'text-slate-400' : ($revenue_analytics['revenue_growth'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-500 dark:text-rose-400') }}">
                    @if($revenue_analytics['revenue_growth'] === null)
                        N/A
                    @else
                        {{ $revenue_analytics['revenue_growth'] >= 0 ? '+' : '' }}{{ number_format($revenue_analytics['revenue_growth'], 1) }}%
                    @endif
                </p>
                <p class="text-xs text-slate-400">vs. prev. period</p>
            </div>
        </div>

        {{-- Avg Revenue / Day --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-lg flex-shrink-0 bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400">
                <i class="fa-solid fa-calendar-day"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Avg / Day</p>
                <p class="text-lg font-bold text-slate-900 dark:text-slate-55 font-display leading-tight">
                    ₹{{ number_format($revenue_analytics['avg_revenue_per_day'], 2) }}
                </p>
                <p class="text-xs text-slate-400">Average daily revenue</p>
            </div>
        </div>

    </div>
</div>

{{-- ── Charts Row ───────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">

    {{-- Revenue Trend --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 lg:col-span-2">
        <div class="flex items-center justify-between mb-1">
            <p class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-chart-area text-blue-500 text-sm"></i>
                Revenue Trend
            </p>
            <span class="text-xs text-slate-400">{{ $date_from->format('d M') }} – {{ $date_to->format('d M Y') }}</span>
        </div>
        <div class="relative h-56">
            <canvas id="chartRevenue"></canvas>
        </div>
    </div>

    {{-- Status Distribution --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5">
        <p class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-chart-pie text-violet-500 text-sm"></i>
            Order Status Distribution
        </p>
        <div class="relative h-48 flex items-center justify-center">
            @if($metrics['total_orders'] > 0)
                <canvas id="chartStatus"></canvas>
            @else
                <div class="text-center text-slate-400 dark:text-slate-600">
                    <i class="fa-solid fa-chart-pie text-3xl mb-2 block"></i>
                    <p class="text-sm">No orders in this period</p>
                </div>
            @endif
        </div>
        {{-- Legend --}}
        @if($metrics['total_orders'] > 0)
        <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1.5">
            @foreach(['Processing' => 'bg-blue-100 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400', 'Shipped' => 'bg-violet-100 text-violet-700 dark:bg-violet-950/40 dark:text-violet-400', 'Delivered' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400', 'Cancelled' => 'bg-rose-100 text-rose-750 dark:bg-rose-950/40 dark:text-rose-400'] as $status => $badgeClass)
                @php $count = $metrics[strtolower($status)] @endphp
                @if($count > 0)
                <div class="flex items-center gap-1.5">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badgeClass }}">{{ $status }}</span>
                    <span class="text-xs text-slate-500 dark:text-slate-400">{{ $count }}</span>
                </div>
                @endif
            @endforeach
        </div>
        @endif
    </div>

</div>

{{-- Orders Trend chart (full width) --}}
<div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 mb-6">
    <div class="flex items-center justify-between mb-1">
        <p class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-chart-line text-emerald-500 text-sm"></i>
            Order Volume Trend
        </p>
        <span class="text-xs text-slate-400">Total orders per day</span>
    </div>
    <div class="relative h-48">
        <canvas id="chartOrders"></canvas>
    </div>
</div>

{{-- ── Detailed Orders Table ────────────────────────────────────────────── --}}
<div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 overflow-hidden mb-6">
    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-800">
        <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
            <i class="fa-solid fa-table-list text-slate-400 text-sm"></i>
            Detailed Orders
        </h2>
        <span class="text-xs text-slate-400 dark:text-slate-500">
            {{ $orders->total() }} {{ Str::plural('order', $orders->total()) }} found
        </span>
    </div>

    @if($orders->isEmpty())
        <div class="text-center py-16 text-slate-400 dark:text-slate-600">
            <i class="fa-solid fa-inbox text-4xl mb-3 block"></i>
            <p class="text-sm font-medium">No orders found for this date range.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[900px]">
                <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-100 dark:border-slate-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">Order ID</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">Customer</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">Payment</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">Method</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">Total</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">Discount</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">Tax</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">Shipping</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">Net Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($orders as $order)
                    @php
                        $netAmount = $order->total - ($order->discount ?? 0);
                        $latestPayment = $order->payments->first();
                        $paymentStatus = $latestPayment?->status?->value ?? $latestPayment?->status ?? 'Pending';
                        $paymentMethod = $latestPayment?->payment_method ?? '—';

                        $statusBadge = match($order->status?->value ?? $order->status) {
                            'Processing' => 'bg-blue-100 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400',
                            'Shipped'    => 'bg-violet-100 text-violet-700 dark:bg-violet-950/40 dark:text-violet-400',
                            'Delivered'  => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400',
                            'Cancelled'  => 'bg-rose-100 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400',
                            default      => 'bg-slate-100 text-slate-500',
                        };
                        $paymentBadge = match($paymentStatus) {
                            'Paid'    => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400',
                            'Failed'  => 'bg-rose-100 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400',
                            default   => 'bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400',
                        };
                    @endphp
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors group">
                        <td class="px-4 py-3.5 text-sm text-slate-700 dark:text-slate-300 whitespace-nowrap">
                            <a href="{{ route('admin.orders.show', $order) }}"
                               class="font-mono text-blue-600 dark:text-blue-400 hover:underline text-xs font-semibold">
                                #{{ $order->order_number }}
                            </a>
                        </td>
                        <td class="px-4 py-3.5 text-sm text-slate-700 dark:text-slate-300 whitespace-nowrap">
                            <span class="font-medium text-slate-800 dark:text-slate-200">
                                {{ $order->first_name }} {{ $order->last_name }}
                            </span>
                            @if($order->email)
                            <p class="text-xs text-slate-400 truncate max-w-[160px]">{{ $order->email }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-sm text-slate-700 dark:text-slate-300 whitespace-nowrap text-slate-500 dark:text-slate-400">
                            {{ $order->created_at->format('d M Y') }}
                            <p class="text-xs text-slate-400">{{ $order->created_at->format('H:i') }}</p>
                        </td>
                        <td class="px-4 py-3.5 text-sm text-slate-700 dark:text-slate-300 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $statusBadge }}">
                                {{ $order->status?->value ?? $order->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-sm text-slate-700 dark:text-slate-300 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $paymentBadge }}">{{ $paymentStatus }}</span>
                        </td>
                        <td class="px-4 py-3.5 text-sm text-slate-700 dark:text-slate-300 whitespace-nowrap text-slate-500 dark:text-slate-400 text-xs">{{ $paymentMethod }}</td>
                        <td class="px-4 py-3.5 text-sm text-slate-700 dark:text-slate-300 whitespace-nowrap text-right font-semibold text-slate-800 dark:text-slate-200">
                            ₹{{ number_format($order->total, 2) }}
                        </td>
                        <td class="px-4 py-3.5 text-sm text-slate-700 dark:text-slate-300 whitespace-nowrap text-right text-slate-500 dark:text-slate-400">
                            {{ $order->discount ? '₹'.number_format($order->discount, 2) : '—' }}
                        </td>
                        <td class="px-4 py-3.5 text-sm text-slate-700 dark:text-slate-300 whitespace-nowrap text-right text-slate-500 dark:text-slate-400">
                            {{ $order->tax ? '₹'.number_format($order->tax, 2) : '—' }}
                        </td>
                        <td class="px-4 py-3.5 text-sm text-slate-700 dark:text-slate-300 whitespace-nowrap text-right text-slate-500 dark:text-slate-400">
                            {{ $order->shipping_charge ? '₹'.number_format($order->shipping_charge, 2) : '—' }}
                        </td>
                        <td class="px-4 py-3.5 text-sm text-slate-700 dark:text-slate-300 whitespace-nowrap text-right font-bold text-slate-900 dark:text-slate-100">
                            ₹{{ number_format($netAmount, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($orders->hasPages())
        <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-xs text-slate-400">
                Showing {{ $orders->firstItem() }}–{{ $orders->lastItem() }} of {{ $orders->total() }} orders
            </p>
            <div class="flex items-center gap-1">
                {{-- Previous --}}
                @if($orders->onFirstPage())
                    <span class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-700 text-slate-300 dark:text-slate-600 cursor-not-allowed">
                        <i class="fa-solid fa-chevron-left"></i>
                    </span>
                @else
                    <a href="{{ $orders->previousPageUrl() }}"
                       class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                        <i class="fa-solid fa-chevron-left"></i>
                    </a>
                @endif

                {{-- Page numbers --}}
                @foreach($orders->getUrlRange(max(1, $orders->currentPage() - 2), min($orders->lastPage(), $orders->currentPage() + 2)) as $page => $url)
                    @if($page === $orders->currentPage())
                        <span class="px-3 py-1.5 text-xs rounded-lg bg-blue-600 text-white font-semibold border border-blue-600">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}"
                           class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach

                {{-- Next --}}
                @if($orders->hasMorePages())
                    <a href="{{ $orders->nextPageUrl() }}"
                       class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                @else
                    <span class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-700 text-slate-300 dark:text-slate-600 cursor-not-allowed">
                        <i class="fa-solid fa-chevron-right"></i>
                    </span>
                @endif
            </div>
        </div>
        @endif
    @endif
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
(function () {
    'use strict';

    // ── Shared helpers ──────────────────────────────────────────────────────
    const isDark = () => document.documentElement.classList.contains('dark');

    function gridColor() {
        return isDark() ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.05)';
    }

    function tickColor() {
        return isDark() ? '#94a3b8' : '#64748b';
    }

    function tooltipTheme() {
        return {
            backgroundColor: isDark() ? '#0f172a' : '#1e293b',
            titleColor:      '#f1f5f9',
            bodyColor:       '#94a3b8',
            borderColor:     isDark() ? '#334155' : '#475569',
            borderWidth:     1,
            padding:         12,
            cornerRadius:    10,
        };
    }

    function baseScaleConfig(showGrid = true) {
        return {
            x: {
                grid: { display: showGrid, color: gridColor(), drawBorder: false },
                ticks: { color: tickColor(), font: { size: 11 }, maxTicksLimit: 10 },
            },
            y: {
                grid: { display: showGrid, color: gridColor(), drawBorder: false },
                ticks: { color: tickColor(), font: { size: 11 } },
                beginAtZero: true,
            },
        };
    }

    // ── Dataset ─────────────────────────────────────────────────────────────
    const labels  = @json($chart_labels);
    const revenue = @json($chart_revenue);
    const orders  = @json($chart_orders);

    const statusLabels = @json($chart_status_labels);
    const statusData   = @json($chart_status_data);
    const statusColors = @json($chart_status_colors);

    // ── Revenue Trend (area chart) ───────────────────────────────────────────
    const ctxRevenue = document.getElementById('chartRevenue');
    if (ctxRevenue) {
        const revenueGradient = ctxRevenue.getContext('2d').createLinearGradient(0, 0, 0, 220);
        revenueGradient.addColorStop(0, 'rgba(59,130,246,0.25)');
        revenueGradient.addColorStop(1, 'rgba(59,130,246,0.0)');

        new Chart(ctxRevenue, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'Revenue (₹)',
                    data: revenue,
                    borderColor:     '#3b82f6',
                    backgroundColor: revenueGradient,
                    borderWidth:     2.5,
                    pointRadius:     labels.length > 31 ? 0 : 3,
                    pointHoverRadius: 5,
                    pointBackgroundColor: '#3b82f6',
                    tension: 0.4,
                    fill: true,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        ...tooltipTheme(),
                        callbacks: {
                            label: ctx => ' ₹' + ctx.parsed.y.toLocaleString('en-IN', { minimumFractionDigits: 2 }),
                        },
                    },
                },
                scales: {
                    ...baseScaleConfig(),
                    y: {
                        ...baseScaleConfig().y,
                        ticks: {
                            color: tickColor(),
                            font: { size: 11 },
                            callback: v => '₹' + (v >= 1000 ? (v / 1000).toFixed(1) + 'k' : v),
                        },
                    },
                },
            },
        });
    }

    // ── Orders Volume Trend (bar chart) ─────────────────────────────────────
    const ctxOrders = document.getElementById('chartOrders');
    if (ctxOrders) {
        new Chart(ctxOrders, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Orders',
                    data: orders,
                    backgroundColor: 'rgba(16,185,129,0.6)',
                    hoverBackgroundColor: 'rgba(16,185,129,0.85)',
                    borderRadius: 4,
                    borderSkipped: false,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        ...tooltipTheme(),
                        callbacks: {
                            label: ctx => ' ' + ctx.parsed.y + ' orders',
                        },
                    },
                },
                scales: {
                    ...baseScaleConfig(),
                    y: {
                        ...baseScaleConfig().y,
                        ticks: {
                            color: tickColor(),
                            font: { size: 11 },
                            precision: 0,
                        },
                    },
                },
            },
        });
    }

    // ── Status Distribution (doughnut) ──────────────────────────────────────
    const ctxStatus = document.getElementById('chartStatus');
    if (ctxStatus && statusData.length > 0) {
        new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusData,
                    backgroundColor: statusColors,
                    hoverBackgroundColor: statusColors.map(c => c + 'cc'),
                    borderColor: isDark() ? '#0f172a' : '#ffffff',
                    borderWidth: 3,
                    hoverOffset: 6,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        ...tooltipTheme(),
                        callbacks: {
                            label: ctx => ' ' + ctx.label + ': ' + ctx.parsed + ' orders',
                        },
                    },
                },
            },
        });
    }

    // ── Re-render charts on theme toggle ────────────────────────────────────
    // Observes class changes on <html> for dark mode toggling
    const observer = new MutationObserver(() => {
        Chart.instances && Object.values(Chart.instances).forEach(chart => {
            if (!chart.canvas) return;
            const ds = chart.data.datasets;
            if (chart.config.type === 'line') {
                chart.options.scales.x.grid.color = gridColor();
                chart.options.scales.y.grid.color = gridColor();
                chart.options.scales.x.ticks.color = tickColor();
                chart.options.scales.y.ticks.color = tickColor();
            } else if (chart.config.type === 'bar') {
                chart.options.scales.x.grid.color = gridColor();
                chart.options.scales.y.grid.color = gridColor();
                chart.options.scales.x.ticks.color = tickColor();
                chart.options.scales.y.ticks.color = tickColor();
            } else if (chart.config.type === 'doughnut') {
                ds[0].borderColor = isDark() ? '#0f172a' : '#ffffff';
            }
            chart.update('none');
        });
    });
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

})();
</script>
@endpush
