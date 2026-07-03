@extends('layouts.admin')

@section('title', 'Revenue Breakdown — SGCart Admin')

@section('content')

{{-- Page Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900 dark:text-slate-50 font-display flex items-center gap-2">
            <i class="fa-solid fa-indian-rupee-sign text-blue-500"></i>
            Revenue Reports
        </h1>
        <x-breadcrumbs :items="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Reporting'],
            ['label' => 'Revenue']
        ]" />
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
            Grouped by product and category from
            <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $date_from->format('d M Y') }}</span>
            to
            <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $date_to->format('d M Y') }}</span>
        </p>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.reports.revenue') }}"
          class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
        <div class="flex items-center gap-2">
            <div class="flex flex-col">
                <label class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-0.5">From</label>
                <input type="date" name="date_from" value="{{ request('date_from', $date_from->format('Y-m-d')) }}"
                       class="px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <span class="text-slate-400 mt-4">→</span>
            <div class="flex flex-col">
                <label class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-0.5">To</label>
                <input type="date" name="date_to" value="{{ request('date_to', $date_to->format('Y-m-d')) }}"
                       class="px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
        <button type="submit" class="flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-medium shadow-lg shadow-blue-600/10 no-underline mt-4 sm:mt-auto">
            <i class="fa-solid fa-filter mr-1.5"></i>
            Apply
        </button>
        <a href="{{ route('admin.reports.revenue') }}" class="px-4 py-2 rounded-xl text-sm font-semibold border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 mt-4 sm:mt-auto text-center">
            Reset
        </a>
        <a href="{{ route('admin.reports.revenue.export', request()->only(['date_from', 'date_to'])) }}"
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
        <a href="{{ route('admin.reports.revenue', ['date_from' => $f, 'date_to' => $t]) }}"
           class="px-3 py-1.5 text-xs font-semibold rounded-lg border transition-colors
                  {{ request('date_from') === $f && request('date_to') === $t
                       ? 'bg-blue-600 text-white border-blue-600'
                       : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

{{-- Top Row: Category Share and Top Products --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    {{-- Category Revenue Chart --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5">
        <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-chart-pie text-violet-500"></i>Category Share
        </h2>
        <div class="relative h-60 flex items-center justify-center">
            @if($by_category->isNotEmpty())
                <canvas id="chartCategory"></canvas>
            @else
                <div class="text-center text-slate-400">No category sales data.</div>
            @endif
        </div>
    </div>

    {{-- Top Products Bar Chart --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 lg:col-span-2">
        <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-chart-bar text-blue-500"></i>Top Product Revenue
        </h2>
        <div class="relative h-60">
            @if($by_product->isNotEmpty())
                <canvas id="chartProduct"></canvas>
            @else
                <div class="text-center text-slate-400 pt-20">No product sales data.</div>
            @endif
        </div>
    </div>
</div>

{{-- Tables Category & Product --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Category Sales Table --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800">
            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Revenue by Category</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-100 dark:border-slate-800">
                    <tr>
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Category</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Units Sold</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Gross Sales</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($by_category as $row)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
                        <td class="px-4 py-3.5 text-sm font-medium">{{ $row->category_name }}</td>
                        <td class="px-4 py-3.5 text-sm text-right">{{ number_format($row->units_sold) }}</td>
                        <td class="px-4 py-3.5 text-sm text-right font-bold text-slate-900 dark:text-slate-100">₹{{ number_format($row->gross_revenue, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center py-10 text-slate-400">No data found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Product Sales Table --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800">
            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Top Product Revenue</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-100 dark:border-slate-800">
                    <tr>
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Product</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Units Sold</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Gross Sales</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($by_product as $row)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
                        <td class="px-4 py-3.5 text-sm font-medium truncate max-w-[200px]">{{ $row->product_name }}</td>
                        <td class="px-4 py-3.5 text-sm text-right">{{ number_format($row->units_sold) }}</td>
                        <td class="px-4 py-3.5 text-sm text-right font-bold text-slate-900 dark:text-slate-100">₹{{ number_format($row->gross_revenue, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center py-10 text-slate-400">No data found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
(function () {
    'use strict';

    const isDark = () => document.documentElement.classList.contains('dark');
    const gridColor = () => isDark() ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.05)';
    const tickColor = () => isDark() ? '#94a3b8' : '#64748b';

    // Category chart
    const ctxCat = document.getElementById('chartCategory');
    if (ctxCat) {
        const catLabels = @json($by_category->pluck('category_name'));
        const catData = @json($by_category->pluck('gross_revenue'));

        new Chart(ctxCat, {
            type: 'doughnut',
            data: {
                labels: catLabels,
                datasets: [{
                    data: catData,
                    backgroundColor: ['#3b82f6', '#8b5cf6', '#10b981', '#f59e0b', '#ef4444', '#ec4899', '#14b8a6'],
                    borderColor: isDark() ? '#0f172a' : '#ffffff',
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });
    }

    // Product chart
    const ctxProd = document.getElementById('chartProduct');
    if (ctxProd) {
        const prodLabels = @json($by_product->pluck('product_name'));
        const prodData = @json($by_product->pluck('gross_revenue'));

        new Chart(ctxProd, {
            type: 'bar',
            data: {
                labels: prodLabels.map(l => l.length > 20 ? l.substring(0, 18) + '..' : l),
                datasets: [{
                    label: 'Gross Sales (₹)',
                    data: prodData,
                    backgroundColor: 'rgba(59,130,246,0.75)',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { color: tickColor() } },
                    y: { grid: { color: gridColor() }, ticks: { color: tickColor() } }
                }
            }
        });
    }
})();
</script>
@endpush
