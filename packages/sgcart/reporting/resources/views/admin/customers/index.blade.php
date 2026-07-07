@extends('layouts.admin')

@section('title', 'Customers Report — SGCart Admin')

@section('content')

{{-- ── Page Header ──────────────────────────────────────────────────────── --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900 dark:text-slate-50 font-display flex items-center gap-2">
            Customers Report
        </h1>
        <x-breadcrumbs :items="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Reporting'],
            ['label' => 'Customers']
        ]" />
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
            Showing customer analytics from
            <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $date_from->format('d M Y') }}</span>
            to
            <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $date_to->format('d M Y') }}</span>
            <span class="text-slate-400">({{ $days_in_range }} {{ Str::plural('day', $days_in_range) }})</span>
        </p>
    </div>

    {{-- Date Range Filter --}}
    <form method="GET" action="{{ route('admin.reports.customers') }}"
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
                class="flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-medium shadow-lg shadow-blue-600/10 no-underline mt-4 sm:mt-auto self-end sm:self-auto">
            <i class="fa-solid fa-filter mr-1.5"></i>Apply
        </button>
        <a href="{{ route('admin.reports.customers') }}"
           class="px-4 py-2 rounded-xl text-sm font-semibold border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors mt-4 sm:mt-auto self-end sm:self-auto text-center">
            Reset
        </a>
        <a href="{{ route('admin.reports.customers.export', request()->only(['date_from', 'date_to'])) }}"
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
        <a href="{{ route('admin.reports.customers', ['date_from' => $f, 'date_to' => $t]) }}"
           class="px-3 py-1.5 text-xs font-semibold rounded-lg border transition-colors
                  {{ request('date_from') === $f && request('date_to') === $t
                       ? 'bg-blue-600 text-white border-blue-600'
                       : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

{{-- ── Metric Cards Grid ────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    {{-- Total Registered --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 flex flex-col gap-1 transition-shadow duration-200 hover:shadow-md">
        <div class="flex items-start justify-between gap-2">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Registered</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-slate-50 font-display leading-none mt-1">{{ number_format($metrics['total_customers']) }}</p>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">All-time database registrations</p>
            </div>
            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-base flex-shrink-0 bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>
    </div>

    {{-- New Registrations --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 flex flex-col gap-1 transition-shadow duration-200 hover:shadow-md">
        <div class="flex items-start justify-between gap-2">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">New Registrations</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-slate-50 font-display leading-none mt-1">{{ number_format($metrics['new_customers']) }}</p>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Registered in period</p>
            </div>
            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-base flex-shrink-0 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400">
                <i class="fa-solid fa-user-plus"></i>
            </div>
        </div>
    </div>

    {{-- Active Buyers --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 flex flex-col gap-1 transition-shadow duration-200 hover:shadow-md">
        <div class="flex items-start justify-between gap-2">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Active Buyers</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-slate-50 font-display leading-none mt-1">{{ number_format($metrics['active_customers']) }}</p>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Placed order in period</p>
            </div>
            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-base flex-shrink-0 bg-violet-50 dark:bg-violet-950/40 text-violet-600 dark:text-violet-400">
                <i class="fa-solid fa-shopping-bag"></i>
            </div>
        </div>
    </div>

    {{-- Returning Customers --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 flex flex-col gap-1 transition-shadow duration-200 hover:shadow-md">
        <div class="flex items-start justify-between gap-2">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Returning Customers</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-slate-50 font-display leading-none mt-1">{{ number_format($metrics['returning_customers']) }}</p>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Had pre-existing orders</p>
            </div>
            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-base flex-shrink-0 bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400">
                <i class="fa-solid fa-rotate-right"></i>
            </div>
        </div>
    </div>

    {{-- Total Orders in Period --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 flex flex-col gap-1 transition-shadow duration-200 hover:shadow-md">
        <div class="flex items-start justify-between gap-2">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Orders</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-slate-50 font-display leading-none mt-1">{{ number_format($metrics['total_orders']) }}</p>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Orders in selected period</p>
            </div>
            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-base flex-shrink-0 bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400">
                <i class="fa-solid fa-receipt"></i>
            </div>
        </div>
    </div>

    {{-- Average Orders per Customer --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 flex flex-col gap-1 transition-shadow duration-200 hover:shadow-md">
        <div class="flex items-start justify-between gap-2">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Avg. Orders / Cust.</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-slate-50 font-display leading-none mt-1">{{ number_format($metrics['avg_orders'], 2) }}</p>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Total Orders ÷ Active Buyers</p>
            </div>
            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-base flex-shrink-0 bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400">
                <i class="fa-solid fa-calculator"></i>
            </div>
        </div>
    </div>

    {{-- Average Customer Lifetime Value --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 flex flex-col gap-1 transition-shadow duration-200 hover:shadow-md lg:col-span-2">
        <div class="flex items-start justify-between gap-2">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Avg. Customer Lifetime Value (CLV)</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-slate-50 font-display leading-none mt-1">₹{{ number_format($metrics['avg_clv'], 2) }}</p>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Average total spent by active customers</p>
            </div>
            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-base flex-shrink-0 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400">
                <i class="fa-solid fa-indian-rupee-sign"></i>
            </div>
        </div>
    </div>
</div>

{{-- ── Charts Row ───────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
    {{-- Customer Growth Trend --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 lg:col-span-2">
        <p class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-chart-line text-blue-500 text-sm"></i>
            Customer Growth Trend
        </p>
        <div class="relative h-60">
            <canvas id="chartGrowth"></canvas>
        </div>
    </div>

    {{-- New vs Returning --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5">
        <p class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-chart-pie text-violet-500 text-sm"></i>
            New vs. Returning Buyers
        </p>
        <div class="relative h-48 flex items-center justify-center">
            @if($metrics['active_customers'] > 0)
                <canvas id="chartRatio"></canvas>
            @else
                <div class="text-center text-slate-400 dark:text-slate-600">
                    <i class="fa-solid fa-chart-pie text-3xl mb-2 block"></i>
                    <p class="text-sm">No active buyers in period</p>
                </div>
            @endif
        </div>
        @if($metrics['active_customers'] > 0)
        <div class="mt-3 flex justify-center gap-6">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                <span class="text-xs text-slate-600 dark:text-slate-400">New ({{ $metrics['new_customers'] }})</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                <span class="text-xs text-slate-600 dark:text-slate-400">Returning ({{ $metrics['returning_customers'] }})</span>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Top Lists Row --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
    {{-- Top by Purchase Amount --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5">
        <p class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-crown text-amber-500 text-sm"></i>
            Top Customers by Purchase Amount
        </p>
        <div class="relative h-56">
            <canvas id="chartTopSales"></canvas>
        </div>
    </div>

    {{-- Top by Orders --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5">
        <p class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-ranking-star text-violet-500 text-sm"></i>
            Top Customers by Number of Orders
        </p>
        <div class="relative h-56">
            <canvas id="chartTopOrders"></canvas>
        </div>
    </div>
</div>

{{-- ── Detailed Table ──────────────────────────────────────────────────── --}}
@php
    $headers = [
        ['label' => 'Customer Name', 'key' => 'customer_name', 'sortable' => false],
        ['label' => 'Email', 'key' => 'email', 'sortable' => false],
        ['label' => 'Phone', 'key' => 'phone', 'sortable' => false],
        ['label' => 'Total Orders', 'key' => 'total_orders', 'sortable' => true, 'align' => 'right'],
        ['label' => 'Total Spent', 'key' => 'total_spent', 'sortable' => true, 'align' => 'right'],
        ['label' => 'Avg. Order Value', 'key' => 'aov', 'sortable' => true, 'align' => 'right'],
        ['label' => 'Last Order Date', 'key' => 'last_order_date', 'sortable' => true],
    ];
@endphp

<x-data-table
    title="Detailed Customer Report"
    :totalCount="$customers->total()"
    action="{{ route('admin.reports.customers') }}"
    tableId="detailedCustomersTableWrapper"
    searchInputId="detailedCustomersSearchInput"
    totalCountId="detailedCustomersTotalCount"
    :items="$customers"
    :headers="$headers"
>
    <x-slot name="filters">
        <input type="hidden" name="date_from" value="{{ request('date_from', $date_from->format('Y-m-d')) }}">
        <input type="hidden" name="date_to" value="{{ request('date_to', $date_to->format('Y-m-d')) }}">
    </x-slot>

    @forelse($customers as $row)
        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
            <td class="px-4 py-3.5 text-sm text-slate-700 dark:text-slate-300 whitespace-nowrap font-medium text-slate-900 dark:text-slate-100">
                {{ $row->first_name }} {{ $row->last_name }}
            </td>
            <td class="px-4 py-3.5 text-sm text-slate-700 dark:text-slate-300 whitespace-nowrap">
                {{ $row->email ?? '—' }}
            </td>
            <td class="px-4 py-3.5 text-sm text-slate-700 dark:text-slate-300 whitespace-nowrap">
                {{ $row->phone ?? '—' }}
            </td>
            <td class="px-4 py-3.5 text-sm text-slate-700 dark:text-slate-300 whitespace-nowrap text-right font-semibold">
                {{ number_format($row->total_orders) }}
            </td>
            <td class="px-4 py-3.5 text-sm text-slate-700 dark:text-slate-300 whitespace-nowrap text-right font-bold text-slate-900 dark:text-slate-100">
                ₹{{ number_format($row->total_spent, 2) }}
            </td>
            <td class="px-4 py-3.5 text-sm text-slate-700 dark:text-slate-300 whitespace-nowrap text-right text-slate-500 dark:text-slate-400">
                ₹{{ number_format($row->aov, 2) }}
            </td>
            <td class="px-4 py-3.5 text-sm text-slate-700 dark:text-slate-300 whitespace-nowrap text-slate-500 dark:text-slate-400">
                {{ Carbon\Carbon::parse($row->last_order_date)->format('d M Y H:i') }}
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="7" class="px-4 py-16 text-center text-slate-400 dark:text-slate-600">
                <i class="fa-solid fa-inbox text-4xl mb-3 block"></i>
                <p class="text-sm font-medium">No customer purchases found for this date range.</p>
            </td>
        </tr>
    @endforelse
</x-data-table>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
(function () {
    'use strict';

    const isDark = () => document.documentElement.classList.contains('dark');
    const gridColor = () => isDark() ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.05)';
    const tickColor = () => isDark() ? '#94a3b8' : '#64748b';

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

    // ── registrations growth line ───────────────────────────────────────────
    const growthLabels = @json($chart_growth_labels);
    const growthData   = @json($chart_growth_data);

    const ctxGrowth = document.getElementById('chartGrowth');
    if (ctxGrowth) {
        new Chart(ctxGrowth, {
            type: 'line',
            data: {
                labels: growthLabels,
                datasets: [{
                    label: 'New Customers',
                    data: growthData,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59,130,246,0.1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: tooltipTheme()
                },
                scales: {
                    x: { grid: { color: gridColor() }, ticks: { color: tickColor() } },
                    y: { grid: { color: gridColor() }, ticks: { color: tickColor() }, beginAtZero: true }
                }
            }
        });
    }

    // ── ratio doughnut ──────────────────────────────────────────────────────
    const ctxRatio = document.getElementById('chartRatio');
    if (ctxRatio) {
        new Chart(ctxRatio, {
            type: 'doughnut',
            data: {
                labels: ['New', 'Returning'],
                datasets: [{
                    data: [{{ $metrics['new_customers'] }}, {{ $metrics['returning_customers'] }}],
                    backgroundColor: ['#3b82f6', '#10b981'],
                    borderColor: isDark() ? '#0f172a' : '#ffffff',
                    borderWidth: 3,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { display: false },
                    tooltip: tooltipTheme()
                }
            }
        });
    }

    // ── top customer sales bar ──────────────────────────────────────────────
    const topSalesLabels = @json($top_by_sales_labels);
    const topSalesData   = @json($top_by_sales_data);

    const ctxSales = document.getElementById('chartTopSales');
    if (ctxSales) {
        new Chart(ctxSales, {
            type: 'bar',
            data: {
                labels: topSalesLabels,
                datasets: [{
                    label: 'Total Spent (₹)',
                    data: topSalesData,
                    backgroundColor: 'rgba(59,130,246,0.7)',
                    borderRadius: 6
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: tooltipTheme()
                },
                scales: {
                    x: { grid: { color: gridColor() }, ticks: { color: tickColor() } },
                    y: { grid: { color: 'transparent' }, ticks: { color: tickColor() } }
                }
            }
        });
    }

    // ── top customer orders bar ─────────────────────────────────────────────
    const topOrdersLabels = @json($top_by_orders_labels);
    const topOrdersData   = @json($top_by_orders_data);

    const ctxOrders = document.getElementById('chartTopOrders');
    if (ctxOrders) {
        new Chart(ctxOrders, {
            type: 'bar',
            data: {
                labels: topOrdersLabels,
                datasets: [{
                    label: 'Orders Count',
                    data: topOrdersData,
                    backgroundColor: 'rgba(16,185,129,0.7)',
                    borderRadius: 6
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: tooltipTheme()
                },
                scales: {
                    x: { grid: { color: gridColor() }, ticks: { color: tickColor() }, precision: 0 },
                    y: { grid: { color: 'transparent' }, ticks: { color: tickColor() } }
                }
            }
        });
    }

})();
</script>
@endpush
