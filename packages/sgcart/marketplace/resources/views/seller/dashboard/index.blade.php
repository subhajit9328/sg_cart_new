@extends('marketplace::layouts.seller')

@section('title', 'Dashboard — Seller Portal')

@section('content')
<div class="space-y-6">
    <!-- Welcome Header -->
    <div>
        <h1 class="font-display text-2xl font-bold">Dashboard Overview</h1>
        <p class="text-sm text-slate-400 mt-0.5">Welcome back to your shop manager, <span class="font-semibold text-slate-700 dark:text-slate-350">{{ auth('seller')->user()->name }}</span>!</p>
    </div>

    @if(auth('seller')->user()->status->value !== 'approved')
        @if(auth('seller')->user()->status->value === 'suspended')
            <!-- Suspended Account Message -->
            <div class="bg-white dark:bg-slate-900 border border-rose-500/20 dark:border-rose-500/30 rounded-xl p-8 text-center shadow-sm">
                <div class="w-16 h-16 rounded-full bg-rose-500/10 text-rose-500 flex items-center justify-center text-3xl mx-auto mb-4">
                    <i class="fa-solid fa-ban"></i>
                </div>
                <h2 class="font-display text-xl font-bold text-slate-900 dark:text-slate-100">Account Suspended</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-2 max-w-md mx-auto">
                    Your seller account for <strong>{{ auth('seller')->user()->shop_name }}</strong> has been suspended. Please contact our support team to resolve this issue.
                </p>
                <div class="mt-6 inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-500 border border-rose-500/20">
                    Status: Suspended
                </div>
            </div>
        @else
            <!-- Pending Approval Message -->
            <div class="bg-white dark:bg-slate-900 border border-amber-500/20 dark:border-amber-500/30 rounded-xl p-8 text-center shadow-sm">
                <div class="w-16 h-16 rounded-full bg-amber-500/10 text-amber-500 flex items-center justify-center text-3xl mx-auto mb-4 animate-pulse">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <h2 class="font-display text-xl font-bold text-slate-900 dark:text-slate-100">Awaiting Admin Approval</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-2 max-w-md mx-auto">
                    Your shop registration (<strong>{{ auth('seller')->user()->shop_name }}</strong>) is currently pending review by our administrator. Once approved, you will be able to add products, manage customer orders, and view your earnings.
                </p>
                <div class="mt-6 inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-500 border border-amber-500/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span>
                    Status: Pending Approval
                </div>
            </div>
        @endif
    @else
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Gross Sales -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 flex items-center justify-between shadow-sm">
            <div>
                <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Gross Sales</p>
                <p class="font-display text-2xl font-extrabold mt-1.5">${{ number_format($totalSales, 2) }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-950/40 text-blue-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-chart-line"></i>
            </div>
        </div>

        <!-- Net Earnings -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 flex items-center justify-between shadow-sm">
            <div>
                <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Net Earnings</p>
                <p class="font-display text-2xl font-extrabold mt-1.5 text-emerald-600 dark:text-emerald-450">${{ number_format($totalEarnings, 2) }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-wallet"></i>
            </div>
        </div>

        <!-- Total Products -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 flex items-center justify-between shadow-sm">
            <div>
                <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Total Products</p>
                <p class="font-display text-2xl font-extrabold mt-1.5">{{ $totalProductsCount }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-violet-50 dark:bg-violet-950/40 text-violet-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-box"></i>
            </div>
        </div>

        <!-- Scoped Orders -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 flex items-center justify-between shadow-sm">
            <div>
                <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Total Orders</p>
                <p class="font-display text-2xl font-extrabold mt-1.5">{{ $totalOrdersCount }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-shopping-bag"></i>
            </div>
        </div>
    </div>

    <!-- Recent Orders Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800">
            <h3 class="text-sm font-bold text-slate-800 dark:text-white">Recent Customer Orders</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20 text-slate-500 font-semibold text-xs uppercase">
                        <th class="py-3 px-6">Order ID</th>
                        <th class="py-3 px-6">Customer</th>
                        <th class="py-3 px-6">Total Items</th>
                        <th class="py-3 px-6">Status</th>
                        <th class="py-3 px-6">Date</th>
                        <th class="py-3 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-350">
                    @forelse($recentOrders as $order)
                        <tr>
                            <td class="py-4 px-6 font-semibold">#{{ $order->order_number ?? $order->id }}</td>
                            <td class="py-4 px-6">{{ $order->customer->name ?? 'Guest Customer' }}</td>
                            <td class="py-4 px-6 font-semibold">{{ $order->items->count() }} items</td>
                            <td class="py-4 px-6">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $order->status === 'Completed' || $order->status === 'Delivered' ? 'bg-emerald-50 text-emerald-700' : ($order->status === 'Cancelled' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700') }}">
                                    {{ is_object($order->status) ? ($order->status->value ?? $order->status->name) : $order->status }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-xs">{{ $order->created_at->format('M d, Y') }}</td>
                            <td class="py-4 px-6 text-right">
                                <a href="{{ route('seller.orders.show', $order) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-1 px-2.5 rounded text-xs transition-colors">View Order</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">No orders received yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
