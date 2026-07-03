@extends('layouts.admin')

@section('title', 'Dashboard & Analytics — SGCart Admin')

@section('content')
<!-- Toast/Validation Alerts -->
@if(session('error') || $errors->any())
<div class="mb-6 p-4 rounded-xl bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-400 text-sm flex items-center gap-3">
    <i class="fa-solid fa-triangle-exclamation text-lg"></i>
    <div>
        <p class="font-semibold">Validation Alert</p>
        <p class="text-xs mt-0.5">{{ session('error') ?? $errors->first() }}</p>
    </div>
</div>
@endif

<!-- Page Header -->
<div class="flex flex-wrap items-end justify-between gap-3 mb-6">
    <div>
        <h1 class="font-display text-2xl font-bold">Dashboard</h1>
        <p class="text-sm text-slate-400 mt-0.5">Welcome back, <span class="font-semibold text-slate-700 dark:text-slate-300">{{ auth()->user()->name }}</span>!</p>
    </div>
    <div class="flex gap-2">
        <button class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">
            <i class="fa-solid fa-calendar-days"></i> Last 7 days
        </button>
        <button class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium">
            <i class="fa-solid fa-download"></i> Export Reports
        </button>
    </div>
</div>

<!-- Standard Dashboard Summary Cards -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
    <!-- Total Users -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-5 flex items-center justify-between">
        <div>
            <p class="text-sm text-slate-400 font-medium">Total Users</p>
            <p class="font-display text-3xl font-extrabold mt-1">{{ $totalUsers }}</p>
            <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 mt-2">
                <i class="fa-solid fa-arrow-up"></i> Active
            </span>
        </div>
        <div class="w-14 h-14 rounded-xl bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center text-2xl">
            <i class="fa-solid fa-users"></i>
        </div>
    </div>
    
    <!-- Total Roles -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-5 flex items-center justify-between">
        <div>
            <p class="text-sm text-slate-400 font-medium">Active Roles</p>
            <p class="font-display text-3xl font-extrabold mt-1">{{ $totalRoles }}</p>
            <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 mt-2">
                <i class="fa-solid fa-check"></i> Configured
            </span>
        </div>
        <div class="w-14 h-14 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-2xl">
            <i class="fa-solid fa-shield-halved"></i>
        </div>
    </div>

    <!-- Total Permissions -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-5 flex items-center justify-between">
        <div>
            <p class="text-sm text-slate-400 font-medium">Spatie Permissions</p>
            <p class="font-display text-3xl font-extrabold mt-1">{{ $totalPermissions }}</p>
            <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 mt-2">
                <i class="fa-solid fa-lock"></i> Secured
            </span>
        </div>
        <div class="w-14 h-14 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-2xl">
            <i class="fa-solid fa-key"></i>
        </div>
    </div>
</div>

<!-- Role-Based Analytics Section -->
@if($showAnalytics)
<div class="border-t border-slate-200 dark:border-slate-800 pt-8 mb-8">
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold tracking-tight text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-chart-line text-blue-500"></i> Analytics Overview
            </h2>
            <p class="text-xs text-slate-400 mt-0.5">Custom date filters, orders progression, and searches.</p>
        </div>

        <!-- Date Filter Control -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-3 rounded-xl shadow-xs">
            <form action="{{ route('admin.dashboard') }}" method="GET" id="filterForm" class="flex flex-col gap-2.5 sm:flex-row sm:items-center">
                <div class="flex flex-wrap gap-1.5 items-center">
                    <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mr-1">Range:</span>
                    
                    <label class="relative cursor-pointer">
                        <input type="radio" name="date_preset" value="7days" class="peer sr-only" {{ $preset === '7days' ? 'checked' : '' }}>
                        <span class="px-2.5 py-1 rounded-md border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-[10px] font-medium text-slate-600 dark:text-slate-400 peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 transition-all block">1 W</span>
                    </label>
                    
                    <label class="relative cursor-pointer">
                        <input type="radio" name="date_preset" value="15days" class="peer sr-only" {{ $preset === '15days' ? 'checked' : '' }}>
                        <span class="px-2.5 py-1 rounded-md border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-[10px] font-medium text-slate-600 dark:text-slate-400 peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 transition-all block">15 D</span>
                    </label>
                    
                    <label class="relative cursor-pointer">
                        <input type="radio" name="date_preset" value="30days" class="peer sr-only" {{ $preset === '30days' ? 'checked' : '' }}>
                        <span class="px-2.5 py-1 rounded-md border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-[10px] font-medium text-slate-600 dark:text-slate-400 peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 transition-all block">1 M</span>
                    </label>
                    
                    <label class="relative cursor-pointer">
                        <input type="radio" name="date_preset" value="6months" class="peer sr-only" {{ $preset === '6months' ? 'checked' : '' }}>
                        <span class="px-2.5 py-1 rounded-md border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-[10px] font-medium text-slate-600 dark:text-slate-400 peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 transition-all block">6 M</span>
                    </label>
                    
                    <label class="relative cursor-pointer">
                        <input type="radio" name="date_preset" value="custom" id="presetCustom" class="peer sr-only" {{ $preset === 'custom' ? 'checked' : '' }}>
                        <span class="px-2.5 py-1 rounded-md border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-[10px] font-medium text-slate-600 dark:text-slate-400 peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 transition-all block">Custom</span>
                    </label>
                </div>
                
                <div id="customDateRange" class="{{ $preset === 'custom' ? 'flex' : 'hidden' }} flex-wrap gap-1.5 items-center">
                    <input type="date" name="start_date" id="start_date" value="{{ $start ? $start->format('Y-m-d') : '' }}" class="px-2 py-1 text-xs rounded border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 focus:outline-none">
                    <span class="text-[10px] text-slate-400">to</span>
                    <input type="date" name="end_date" id="end_date" value="{{ $end ? $end->format('Y-m-d') : '' }}" class="px-2 py-1 text-xs rounded border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 focus:outline-none">
                </div>

                <button type="submit" class="px-3.5 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-[10px] font-semibold transition-colors flex items-center justify-center gap-1">
                    <i class="fa-solid fa-filter"></i> Apply
                </button>
            </form>
        </div>
    </div>

    <!-- Active Range Info -->
    <div class="mb-5 flex items-center justify-between px-4 py-2.5 rounded-lg bg-blue-50/50 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-900/20 text-[11px] text-blue-800 dark:text-blue-300">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-clock-rotate-left"></i>
            <span>Showing analytics from <strong>{{ $start->format('d M Y') }}</strong> to <strong>{{ $end->format('d M Y') }}</strong></span>
        </div>
        <span class="bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-400 font-semibold px-2 py-0.5 rounded text-[9px] uppercase tracking-wider">
            {{ $preset === 'custom' ? 'Custom' : str_replace('days', ' Days', $preset) }}
        </span>
    </div>

    <!-- KPI Section -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
        <!-- Revenue -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-xl flex flex-col justify-between hover:scale-[1.02] transition-transform duration-200 shadow-xs">
            <div class="flex justify-between items-start">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Sales</span>
                <span class="text-emerald-500 text-sm"><i class="fa-solid fa-money-bill-trend-up"></i></span>
            </div>
            <div class="mt-3">
                <p class="font-display text-xl font-black text-slate-800 dark:text-slate-100">₹{{ number_format($totalOrdersRevenue, 2) }}</p>
                <p class="text-[9px] text-slate-400 mt-0.5">Excludes rejected</p>
            </div>
        </div>

        <!-- Orders -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-xl flex flex-col justify-between hover:scale-[1.02] transition-transform duration-200 shadow-xs">
            <div class="flex justify-between items-start">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Orders</span>
                <span class="text-blue-500 text-sm"><i class="fa-solid fa-receipt"></i></span>
            </div>
            <div class="mt-3">
                <p class="font-display text-xl font-black text-slate-800 dark:text-slate-100">{{ $totalOrdersCount }}</p>
                <p class="text-[9px] text-slate-400 mt-0.5">Placed in range</p>
            </div>
        </div>

        <!-- Rejects -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-xl flex flex-col justify-between hover:scale-[1.02] transition-transform duration-200 shadow-xs">
            <div class="flex justify-between items-start">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Reject Orders</span>
                <span class="text-rose-500 text-sm"><i class="fa-solid fa-ban"></i></span>
            </div>
            <div class="mt-3">
                <p class="font-display text-xl font-black text-slate-800 dark:text-slate-100">{{ $rejectedOrdersCount }}</p>
                <p class="text-[9px] text-slate-400 mt-0.5">
                    @if($totalOrdersCount > 0)
                        {{ number_format(($rejectedOrdersCount / $totalOrdersCount) * 100, 1) }}% of total
                    @else
                        0% of total
                    @endif
                </p>
            </div>
        </div>

        <!-- Searches -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-xl flex flex-col justify-between hover:scale-[1.02] transition-transform duration-200 shadow-xs">
            <div class="flex justify-between items-start">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Searches</span>
                <span class="text-amber-500 text-sm"><i class="fa-solid fa-magnifying-glass"></i></span>
            </div>
            <div class="mt-3">
                <p class="font-display text-xl font-black text-slate-800 dark:text-slate-100">{{ $totalSearchesCount }}</p>
                <p class="text-[9px] text-slate-400 mt-0.5">Total queries logged</p>
            </div>
        </div>

        <!-- Coupons -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-xl flex flex-col justify-between hover:scale-[1.02] transition-transform duration-200 shadow-xs">
            <div class="flex justify-between items-start">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Top Coupon</span>
                <span class="text-indigo-500 text-sm"><i class="fa-solid fa-ticket"></i></span>
            </div>
            <div class="mt-3">
                @if($topCoupon)
                    <p class="font-display text-[15px] font-black text-slate-800 dark:text-slate-100 truncate" title="{{ $topCoupon->coupon_code }}">{{ $topCoupon->coupon_code }}</p>
                    <p class="text-[9px] text-slate-400 mt-0.5">Used {{ $topCoupon->count }} times</p>
                @else
                    <p class="font-display text-[15px] font-black text-slate-400 dark:text-slate-500">None</p>
                    <p class="text-[9px] text-slate-400 mt-0.5">No usage logs</p>
                @endif
            </div>
        </div>

        <!-- Peak hour -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-xl flex flex-col justify-between hover:scale-[1.02] transition-transform duration-200 shadow-xs">
            <div class="flex justify-between items-start">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Peak Sales Slot</span>
                <span class="text-violet-500 text-sm"><i class="fa-solid fa-business-time"></i></span>
            </div>
            <div class="mt-3">
                @if($peakSlotName !== 'None')
                    <p class="font-display text-[12px] font-black text-slate-800 dark:text-slate-100 truncate" title="{{ $peakSlotName }}">
                        {{ explode(' ', $peakSlotName)[0] }}
                    </p>
                    <p class="text-[9px] text-slate-400 mt-0.5">{{ explode(' ', $peakSlotName)[1] ?? '' }}</p>
                @else
                    <p class="font-display text-[15px] font-black text-slate-400 dark:text-slate-500">None</p>
                    <p class="text-[9px] text-slate-400 mt-0.5">No sales logs</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Graphics Charts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Revenue Trend -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-xs font-bold text-slate-800 dark:text-slate-100">Revenue & Order Trends</h3>
                <div class="flex items-center gap-3 text-[10px]">
                    <span class="flex items-center gap-1 text-blue-500"><i class="fa-solid fa-circle text-[6px]"></i> Sales (₹)</span>
                    <span class="flex items-center gap-1 text-emerald-500"><i class="fa-solid fa-circle text-[6px]"></i> Orders</span>
                </div>
            </div>
            <div class="h-64 mt-4 relative">
                <canvas id="trendChart"></canvas>
            </div>
        </div>

        <!-- Time distribution -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-xs font-bold text-slate-800 dark:text-slate-100">Sales Time distribution</h3>
                <span class="text-[10px] text-violet-500 flex items-center gap-1"><i class="fa-solid fa-circle text-[6px]"></i> Orders count</span>
            </div>
            <div class="h-64 mt-4 relative">
                <canvas id="hourlyChart"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Top Products Bar -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-xl">
            <h3 class="text-xs font-bold text-slate-800 dark:text-slate-100 pb-3 border-b border-slate-100 dark:border-slate-800">Top 10 High Demand Products</h3>
            <div class="h-64 mt-4 relative">
                @if($topProducts->isEmpty())
                    <div class="absolute inset-0 flex items-center justify-center text-slate-400 text-xs">No product sales in range</div>
                @else
                    <canvas id="productChart"></canvas>
                @endif
            </div>
        </div>

        <!-- Top Searches Doughnut -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-xl">
            <h3 class="text-xs font-bold text-slate-800 dark:text-slate-100 pb-3 border-b border-slate-100 dark:border-slate-800">Top 10 Searches</h3>
            <div class="h-64 mt-4 relative">
                @if($topSearches->isEmpty())
                    <div class="absolute inset-0 flex items-center justify-center text-slate-400 text-xs">No search terms in range</div>
                @else
                    <canvas id="searchChart"></canvas>
                @endif
            </div>
        </div>
    </div>

    <!-- Data tables grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Top Products List -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-xs">
            <div class="px-4 py-3 bg-slate-50/50 dark:bg-slate-850 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-xs font-bold text-slate-800 dark:text-slate-100">Top Product Leaderboard</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/40 text-slate-400 font-semibold text-[10px] uppercase">
                            <th class="px-4 py-2.5 text-left w-12">Rank</th>
                            <th class="px-4 py-2.5 text-left">Product</th>
                            <th class="px-4 py-2.5 text-center w-24">Qty Sold</th>
                            <th class="px-4 py-2.5 text-right w-28">Revenue (₹)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-850">
                        @forelse($topProducts as $index => $prod)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-850/30 transition-colors">
                            <td class="px-4 py-3 font-bold text-slate-400">#{{ $index + 1 }}</td>
                            <td class="px-4 py-3 font-medium text-slate-700 dark:text-slate-200">{{ $prod->product_name }}</td>
                            <td class="px-4 py-3 text-center font-bold">{{ $prod->qty }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-emerald-600 dark:text-emerald-400">₹{{ number_format($prod->revenue, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-slate-400">No products sold in range.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Searches List -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-xs">
            <div class="px-4 py-3 bg-slate-50/50 dark:bg-slate-850 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-xs font-bold text-slate-800 dark:text-slate-100">Top Search Keywords</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/40 text-slate-400 font-semibold text-[10px] uppercase">
                            <th class="px-4 py-2.5 text-left w-12">Rank</th>
                            <th class="px-4 py-2.5 text-left">Search Term</th>
                            <th class="px-4 py-2.5 text-center w-28">Count</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-850">
                        @forelse($topSearches as $index => $search)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-850/30 transition-colors">
                            <td class="px-4 py-3 font-bold text-slate-400">#{{ $index + 1 }}</td>
                            <td class="px-4 py-3 font-medium text-slate-700 dark:text-slate-200 capitalize">"{{ $search->term }}"</td>
                            <td class="px-4 py-3 text-center font-bold">{{ $search->count }} hits</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-4 py-6 text-center text-slate-400">No search terms logged in range.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@else
<!-- Original Mock Overview for non-admin users who do not have Spatie view analytics -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-5 mb-6">
    <div class="lg:col-span-7 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-sm">System Access Summary</h2>
                <p class="text-xs text-slate-400 mt-0.5">Mock analytics of weekly operations</p>
            </div>
            <span class="text-xs font-medium text-blue-500 bg-blue-50 dark:bg-blue-500/10 px-2.5 py-1 rounded-lg">Online</span>
        </div>
        <div class="p-5">
            <div class="flex items-end gap-3 h-44">
                <div class="flex-1 flex flex-col items-center gap-2 h-full justify-end">
                    <div class="w-full max-w-[28px] rounded-t-md bg-blue-600/20" style="height:35%"></div>
                    <span class="text-xs text-slate-400">Mon</span>
                </div>
                <div class="flex-1 flex flex-col items-center gap-2 h-full justify-end">
                    <div class="w-full max-w-[28px] rounded-t-md bg-blue-600/40" style="height:55%"></div>
                    <span class="text-xs text-slate-400">Tue</span>
                </div>
                <div class="flex-1 flex flex-col items-center gap-2 h-full justify-end">
                    <div class="w-full max-w-[28px] rounded-t-md bg-blue-600/30" style="height:45%"></div>
                    <span class="text-xs text-slate-400">Wed</span>
                </div>
                <div class="flex-1 flex flex-col items-center gap-2 h-full justify-end">
                    <div class="w-full max-w-[28px] rounded-t-md bg-blue-600/70" style="height:75%"></div>
                    <span class="text-xs text-slate-400">Thu</span>
                </div>
                <div class="flex-1 flex flex-col items-center gap-2 h-full justify-end">
                    <div class="w-full max-w-[28px] rounded-t-md bg-blue-600/60" style="height:65%"></div>
                    <span class="text-xs text-slate-400">Fri</span>
                </div>
                <div class="flex-1 flex flex-col items-center gap-2 h-full justify-end">
                    <div class="w-full max-w-[28px] rounded-t-md bg-blue-600" style="height:95%"></div>
                    <span class="text-xs text-slate-400">Sat</span>
                </div>
                <div class="flex-1 flex flex-col items-center gap-2 h-full justify-end">
                    <div class="w-full max-w-[28px] rounded-t-md bg-blue-600/50" style="height:50%"></div>
                    <span class="text-xs text-slate-400">Sun</span>
                </div>
            </div>
        </div>
    </div>

    <div class="lg:col-span-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800"><h2 class="font-semibold text-sm">Security Log Highlights</h2></div>
        <div class="p-5 space-y-4">
            <div class="flex gap-3">
                <div class="w-8 h-8 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs flex-shrink-0"><i class="fa-solid fa-check"></i></div>
                <div>
                    <p class="text-sm">Database successfully seeded and ready.</p>
                    <p class="text-xs text-slate-400">Just now</p>
                </div>
            </div>
            <div class="flex gap-3">
                <div class="w-8 h-8 rounded-full bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xs flex-shrink-0"><i class="fa-solid fa-user-plus"></i></div>
                <div>
                    <p class="text-sm">Admin user session initialized successfully.</p>
                    <p class="text-xs text-slate-400">2 minutes ago</p>
                </div>
            </div>
            <div class="flex gap-3">
                <div class="w-8 h-8 rounded-full bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs flex-shrink-0"><i class="fa-solid fa-shield-halved"></i></div>
                <div>
                    <p class="text-sm">Spatie standard policies registered.</p>
                    <p class="text-xs text-slate-400">1 hour ago</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Original Recent Users Table (always visible at bottom) -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden mt-6">
    <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
        <h2 class="font-semibold text-sm">Recently Registered Users</h2>
        @can('manage users')
            <a href="{{ route('admin.users.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700">Manage all users <i class="fa-solid fa-arrow-right ml-1 text-xs"></i></a>
        @endcan
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-800/50">
                    <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">User</th>
                    <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">Email</th>
                    <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">Roles</th>
                    <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">Joined Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach($recentUsers as $user)
                <tr>
                    <td class="px-5 py-3.5 font-medium flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center font-bold text-slate-500 dark:text-slate-400 text-xs">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                        {{ $user->name }}
                    </td>
                    <td class="px-5 py-3.5 text-slate-400">{{ $user->email }}</td>
                    <td class="px-5 py-3.5">
                        @foreach($user->roles as $role)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 mr-1">
                                {{ $role->name }}
                            </span>
                        @endforeach
                    </td>
                    <td class="px-5 py-3.5 text-slate-400">{{ $user->created_at->format('d M Y, h:i A') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
@if($showAnalytics)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- 1. Date Range picker client-side validation ---
        const today = new Date().toISOString().split('T')[0];
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        const form = document.getElementById('filterForm');

        if (startDateInput && endDateInput) {
            startDateInput.setAttribute('max', today);
            endDateInput.setAttribute('max', today);

            startDateInput.addEventListener('change', function() {
                endDateInput.setAttribute('min', this.value);
            });

            endDateInput.addEventListener('change', function() {
                startDateInput.setAttribute('max', this.value);
            });
            
            form.addEventListener('submit', function(e) {
                const presetSelect = document.querySelector('input[name="date_preset"]:checked');
                if (presetSelect && presetSelect.value === 'custom') {
                    if (!startDateInput.value || !endDateInput.value) {
                        alert('Custom date range requires both start and end dates.');
                        e.preventDefault();
                        return false;
                    }
                    
                    const start = new Date(startDateInput.value + 'T00:00:00');
                    const end = new Date(endDateInput.value + 'T23:59:59');
                    const todayDate = new Date();
                    
                    if (start > todayDate) {
                        alert('Start date cannot be in the future.');
                        e.preventDefault();
                        return false;
                    }
                    if (end > todayDate) {
                        // If it's today's date, let it pass
                        const endStartOfDay = new Date(endDateInput.value + 'T00:00:00');
                        const todayStartOfDay = new Date(todayDate.toISOString().split('T')[0] + 'T00:00:00');
                        if (endStartOfDay > todayStartOfDay) {
                            alert('End date cannot be in the future.');
                            e.preventDefault();
                            return false;
                        }
                    }
                    if (start > end) {
                        alert('Start date cannot be after End date.');
                        e.preventDefault();
                        return false;
                    }
                    
                    const diffTime = Math.abs(end - start);
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                    if (diffDays > 180) {
                        alert('Custom date range cannot exceed 6 months (180 days).');
                        e.preventDefault();
                        return false;
                    }
                }
            });
        }
        
        // Toggle custom date picker panel
        const presets = document.querySelectorAll('input[name="date_preset"]');
        const customDateRangeEl = document.getElementById('customDateRange');
        
        presets.forEach(preset => {
            preset.addEventListener('change', function() {
                if (this.value === 'custom') {
                    customDateRangeEl.classList.remove('hidden');
                    customDateRangeEl.classList.add('flex');
                } else {
                    customDateRangeEl.classList.add('hidden');
                    customDateRangeEl.classList.remove('flex');
                }
            });
        });

        // --- 2. Chart.js Configurations ---
        const isDark = document.documentElement.classList.contains('dark');
        const gridColor = isDark ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.05)';
        const labelColor = isDark ? '#94a3b8' : '#64748b';

        // Chart 1: Revenue Line Chart
        const trendData = @json($trendList);
        const trendLabels = trendData.map(d => d.date);
        const trendRevenue = trendData.map(d => d.revenue);
        const trendCount = trendData.map(d => d.count);

        const ctxTrend = document.getElementById('trendChart').getContext('2d');
        const revGradient = ctxTrend.createLinearGradient(0, 0, 0, 240);
        revGradient.addColorStop(0, 'rgba(59, 130, 246, 0.25)');
        revGradient.addColorStop(1, 'rgba(59, 130, 246, 0.00)');

        new Chart(ctxTrend, {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [
                    {
                        label: 'Sales Revenue (₹)',
                        data: trendRevenue,
                        borderColor: '#3b82f6',
                        borderWidth: 2,
                        backgroundColor: revGradient,
                        fill: true,
                        tension: 0.35,
                        yAxisID: 'yRevenue'
                    },
                    {
                        label: 'Orders Count',
                        data: trendCount,
                        borderColor: '#10b981',
                        borderWidth: 1.5,
                        backgroundColor: 'transparent',
                        pointStyle: 'circle',
                        pointRadius: 3,
                        tension: 0.35,
                        yAxisID: 'yOrders'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: labelColor, font: { size: 9 } }
                    },
                    yRevenue: {
                        type: 'linear',
                        position: 'left',
                        grid: { color: gridColor },
                        ticks: {
                            color: labelColor,
                            font: { size: 9 },
                            callback: function(value) { return '₹' + value; }
                        }
                    },
                    yOrders: {
                        type: 'linear',
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        ticks: { color: labelColor, font: { size: 9 }, stepSize: 1 }
                    }
                }
            }
        });

        // Chart 2: Hourly distribution bar chart
        const hourlySlots = @json($timeSlots);
        const hourlyLabels = Object.keys(hourlySlots).map(k => k.split(' ')[0]);
        const hourlyOrders = Object.values(hourlySlots).map(v => v.count);

        const ctxHourly = document.getElementById('hourlyChart').getContext('2d');
        new Chart(ctxHourly, {
            type: 'bar',
            data: {
                labels: hourlyLabels,
                datasets: [{
                    data: hourlyOrders,
                    backgroundColor: '#8b5cf6',
                    borderRadius: 4,
                    barThickness: 24,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: labelColor, font: { size: 9 } }
                    },
                    y: {
                        grid: { color: gridColor },
                        ticks: { color: labelColor, font: { size: 9 }, stepSize: 1 }
                    }
                }
            }
        });

        // Chart 3: Top Products horizontal bar chart
        const topProducts = @json($topProducts);
        if (topProducts && topProducts.length > 0) {
            const productNames = topProducts.map(p => p.product_name.length > 15 ? p.product_name.substring(0, 15) + '..' : p.product_name);
            const productQty = topProducts.map(p => p.qty);

            const ctxProduct = document.getElementById('productChart').getContext('2d');
            new Chart(ctxProduct, {
                type: 'bar',
                data: {
                    labels: productNames,
                    datasets: [{
                        data: productQty,
                        backgroundColor: '#f43f5e',
                        borderRadius: 4,
                        barThickness: 12,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: {
                            grid: { color: gridColor },
                            ticks: { color: labelColor, font: { size: 9 } }
                        },
                        y: {
                            grid: { display: false },
                            ticks: { color: labelColor, font: { size: 9 } }
                        }
                    }
                }
            });
        }

        // Chart 4: Top Searches doughnut chart
        const topSearches = @json($topSearches);
        if (topSearches && topSearches.length > 0) {
            const searchTerms = topSearches.map(s => '"' + s.term + '"');
            const searchCounts = topSearches.map(s => s.count);

            const ctxSearch = document.getElementById('searchChart').getContext('2d');
            new Chart(ctxSearch, {
                type: 'doughnut',
                data: {
                    labels: searchTerms,
                    datasets: [{
                        data: searchCounts,
                        backgroundColor: [
                            'rgba(6, 182, 212, 0.75)',
                            'rgba(59, 130, 246, 0.75)',
                            'rgba(99, 102, 241, 0.75)',
                            'rgba(139, 92, 246, 0.75)',
                            'rgba(236, 72, 153, 0.75)',
                            'rgba(244, 63, 94, 0.75)',
                            'rgba(249, 115, 22, 0.75)',
                            'rgba(234, 179, 8, 0.75)',
                            'rgba(16, 185, 129, 0.75)',
                            'rgba(107, 114, 128, 0.75)'
                        ],
                        borderColor: isDark ? '#0f172a' : '#ffffff',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                color: labelColor,
                                font: { size: 8.5 },
                                boxWidth: 8
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endif
@endpush
