@extends('layouts.admin')

@section('title', 'Order Management — SGCart Admin')

@section('content')
@php
    if (!function_exists('sortUrl')) {
        function sortUrl($field) {
            $currentSortBy = request('sort_by', 'created_at');
            $currentSortOrder = request('sort_order', 'desc');
            
            $newOrder = ($currentSortBy === $field && $currentSortOrder === 'asc') ? 'desc' : 'asc';
            
            return request()->fullUrlWithQuery([
                'sort_by' => $field,
                'sort_order' => $newOrder,
                'page' => 1
            ]);
        }
    }

    if (!function_exists('sortIcon')) {
        function sortIcon($field) {
            $currentSortBy = request('sort_by', 'created_at');
            $currentSortOrder = request('sort_order', 'desc');
            
            if ($currentSortBy !== $field) {
                return '<i class="fa-solid fa-sort ml-1.5 text-slate-300 dark:text-slate-700 opacity-60"></i>';
            }
            
            return $currentSortOrder === 'asc' 
                ? '<i class="fa-solid fa-sort-up ml-1.5 text-blue-600 dark:text-blue-400"></i>' 
                : '<i class="fa-solid fa-sort-down ml-1.5 text-blue-600 dark:text-blue-400"></i>';
        }
    }
@endphp

<!-- Page Header -->
<div class="flex flex-wrap items-end justify-between gap-3 mb-6">
    <div>
        <h1 class="font-display text-2xl font-bold">Orders</h1>
        <x-breadcrumbs :items="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Sales'],
            ['label' => 'Orders']
        ]" />
    </div>
</div>

<!-- KPI Metrics Section -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5 mb-6">
    <!-- Card 1: Total Orders -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-xs flex items-center gap-4 transition-all hover:shadow-md">
        <div class="w-12 h-12 rounded-lg bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center text-blue-600 dark:text-blue-400 text-lg shrink-0">
            <i class="fa-solid fa-cart-shopping"></i>
        </div>
        <div>
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total Orders</span>
            <span class="text-xl font-extrabold text-slate-950 dark:text-white block mt-0.5">{{ number_format($totalOrders) }}</span>
        </div>
    </div>

    <!-- Card 2: Processing Orders Count -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-xs flex items-center gap-4 transition-all hover:shadow-md">
        <div class="w-12 h-12 rounded-lg bg-amber-50 dark:bg-amber-500/10 flex items-center justify-center text-amber-600 dark:text-amber-400 text-lg shrink-0">
            <i class="fa-solid fa-spinner animate-spin"></i>
        </div>
        <div>
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">In progress</span>
            <span class="text-xl font-extrabold text-slate-950 dark:text-white block mt-0.5">{{ number_format($processingOrdersCount) }}</span>
        </div>
    </div>

    <!-- Card 3: Delivered Orders Count -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-xs flex items-center gap-4 transition-all hover:shadow-md">
        <div class="w-12 h-12 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-lg shrink-0">
            <i class="fa-solid fa-truck-ramp-box"></i>
        </div>
        <div>
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Delivered Orders</span>
            <span class="text-xl font-extrabold text-slate-950 dark:text-white block mt-0.5">{{ number_format($deliveredOrdersCount) }}</span>
        </div>
    </div>

    <!-- Card 4: Delivered/Completed Revenue -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-xs flex items-center gap-4 transition-all hover:shadow-md">
        <div class="w-12 h-12 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center text-emerald-600 dark:text-emerald-400 text-lg shrink-0">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div>
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Delivered Revenue</span>
            <span class="text-xl font-extrabold text-slate-950 dark:text-white block mt-0.5">₹{{ number_format($deliveredValue, 2) }}</span>
        </div>
    </div>

    <!-- Card 5: Cancelled Orders -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-xs flex items-center gap-4 transition-all hover:shadow-md">
        <div class="w-12 h-12 rounded-lg bg-rose-50 dark:bg-rose-500/10 flex items-center justify-center text-rose-600 dark:text-rose-400 text-lg shrink-0">
            <i class="fa-solid fa-ban"></i>
        </div>
        <div>
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Cancelled Orders</span>
            <span class="text-xl font-extrabold text-slate-950 dark:text-white block mt-0.5">{{ number_format($cancelledOrders) }}</span>
        </div>
    </div>
</div>

<!-- Orders Table Card -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
    <!-- Card Header with Filters -->
    <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-4 bg-slate-50/50 dark:bg-slate-900/50">
        <div class="flex items-center gap-2">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider">All Sales Orders</h2>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-300/35 dark:border-slate-700/50">
                {{ $orders->total() }}
            </span>
        </div>
        
        <form action="{{ route('admin.orders.index') }}" method="GET" class="flex flex-wrap items-center gap-2 w-full lg:w-auto">
            <!-- Search Box -->
            <div class="relative flex items-center w-full sm:w-64">
                <i class="fa-solid fa-magnifying-glass absolute left-3 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 pl-8 pr-3 text-xs placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-slate-800 dark:text-slate-100"
                    placeholder="Search by order #, email, name…">
            </div>

            <!-- Order Status Filter -->
            <select name="status" onchange="this.form.submit()" class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 px-3 text-xs outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-slate-800 dark:text-slate-100 cursor-pointer">
                <option value="">All Order Statuses</option>
                <option value="Processing" {{ request('status') === 'Processing' ? 'selected' : '' }}>Processing</option>
                <option value="Shipped" {{ request('status') === 'Shipped' ? 'selected' : '' }}>Shipped</option>
                <option value="Delivered" {{ request('status') === 'Delivered' ? 'selected' : '' }}>Delivered</option>
                <option value="Cancelled" {{ request('status') === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>

            <!-- Payment Status Filter -->
            <select name="payment_status" onchange="this.form.submit()" class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 px-3 text-xs outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-slate-800 dark:text-slate-100 cursor-pointer">
                <option value="">All Payment Statuses</option>
                <option value="Pending" {{ request('payment_status') === 'Pending' ? 'selected' : '' }}>Pending</option>
                <option value="Paid" {{ request('payment_status') === 'Paid' ? 'selected' : '' }}>Paid</option>
                <option value="Failed" {{ request('payment_status') === 'Failed' ? 'selected' : '' }}>Failed</option>
            </select>
            
            @if(request()->anyFilled(['search', 'status', 'payment_status']))
                <a href="{{ route('admin.orders.index') }}" class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-600 dark:bg-rose-950/20 dark:border-rose-900/30 dark:text-rose-400 text-xs font-semibold transition-colors text-center no-underline flex items-center justify-center">
                    Clear Filters
                </a>
            @endif
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-800/50">
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">
                        <a href="{!! sortUrl('order_number') !!}" class="text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white flex items-center no-underline">
                            Order Number {!! sortIcon('order_number') !!}
                        </a>
                    </th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">
                        <a href="{!! sortUrl('first_name') !!}" class="text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white flex items-center no-underline">
                            Customer {!! sortIcon('first_name') !!}
                        </a>
                    </th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">
                        <a href="{!! sortUrl('status') !!}" class="text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white flex items-center no-underline">
                            Order Status {!! sortIcon('status') !!}
                        </a>
                    </th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">
                        <a href="{!! sortUrl('payment_status') !!}" class="text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white flex items-center no-underline">
                            Payment Status {!! sortIcon('payment_status') !!}
                        </a>
                    </th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">
                        <a href="{!! sortUrl('total') !!}" class="text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white flex items-center no-underline">
                            Total {!! sortIcon('total') !!}
                        </a>
                    </th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">
                        <a href="{!! sortUrl('created_at') !!}" class="text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white flex items-center no-underline">
                            Date {!! sortIcon('created_at') !!}
                        </a>
                    </th>
                    <th class="px-5 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($orders as $order)
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/15 transition-colors">
                    <td class="px-5 py-3.5 whitespace-nowrap text-sm">
                        <a href="{{ route('admin.orders.show', $order->ulid) }}" class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 font-bold font-mono no-underline flex items-center gap-1.5">
                            <i class="fa-solid fa-receipt text-slate-400 text-xs"></i>
                            {{ $order->order_number }}
                        </a>
                    </td>
                    <td class="px-5 py-3.5 whitespace-nowrap">
                        <div class="text-xs font-semibold text-slate-800 dark:text-slate-100">
                            {{ $order->first_name }} {{ $order->last_name }}
                        </div>
                        <div class="text-[10px] text-slate-400 mt-0.5 font-mono">
                            {{ $order->email }}
                        </div>
                        <div class="text-[9px] text-slate-500 mt-1.5 flex flex-col gap-0.5">
                            <span class="flex items-center gap-1"><i class="fa-solid fa-truck text-[8px] opacity-65 w-3"></i>Ship to: {{ $order->city }}, {{ $order->country }}</span>
                            @if(!$order->shipping_and_billing_same)
                                <span class="flex items-center gap-1 text-blue-600 dark:text-blue-450"><i class="fa-solid fa-receipt text-[8px] opacity-65 w-3"></i>Bill to: {{ $order->billing_city }}, {{ $order->billing_country }}</span>
                            @else
                                <span class="flex items-center gap-1 text-slate-400"><i class="fa-solid fa-clone text-[8px] opacity-65 w-3"></i>Billing Same</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-5 py-3.5 whitespace-nowrap">
                        @php
                            $statusColors = [
                                'Processing' => 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border-amber-200/20',
                                'Shipped' => 'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 border-blue-200/20',
                                'Delivered' => 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-200/20',
                                'Cancelled' => 'bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 border-rose-200/20',
                            ];
                            $statusIcons = [
                                'Processing' => 'fa-solid fa-spinner animate-spin mr-1',
                                'Shipped' => 'fa-solid fa-truck-fast mr-1',
                                'Delivered' => 'fa-solid fa-circle-check mr-1',
                                'Cancelled' => 'fa-solid fa-ban mr-1',
                            ];
                            $colorClass = $statusColors[$order->status->value] ?? $statusColors[$order->status] ?? 'bg-slate-50 text-slate-700 border-slate-200';
                            $iconClass = $statusIcons[$order->status->value] ?? $statusIcons[$order->status] ?? 'fa-solid fa-circle-info mr-1';
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $colorClass }}">
                            <i class="{{ $iconClass }}"></i>
                            {{ $order->status->value ?? $order->status }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 whitespace-nowrap">
                        @php
                            $paymentColors = [
                                'Pending' => 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border-amber-200/20',
                                'Paid' => 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-200/20',
                                'Failed' => 'bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 border-rose-200/20',
                            ];
                            $paymentClass = $paymentColors[$order->payment_status->value] ?? $paymentColors[$order->payment_status] ?? 'bg-slate-50 text-slate-700 border-slate-200';
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $paymentClass }}">
                            <i class="fa-solid fa-credit-card mr-1 text-[9px] opacity-70"></i>
                            {{ $order->payment_status->value ?? $order->payment_status }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-slate-800 dark:text-slate-100 font-bold text-xs whitespace-nowrap font-mono">
                        ₹{{ number_format($order->total, 2) }}
                    </td>
                    <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400 text-xs whitespace-nowrap">
                        {{ $order->created_at->format('M d, Y h:i A') }}
                    </td>
                    <td class="px-5 py-3.5 text-right whitespace-nowrap">
                        <div class="inline-flex gap-1.5 justify-end">
                            <a href="{{ route('admin.orders.show', $order->ulid) }}" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center transition-colors text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200" title="View Details">
                                <i class="fa-solid fa-eye text-xs"></i>
                            </a>
                            <button onclick="openDeleteModal('{{ $order->ulid }}', '{{ $order->order_number }}')" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-rose-50 dark:hover:bg-rose-500/10 flex items-center justify-center transition-colors cursor-pointer text-rose-500 hover:text-rose-700" title="Delete Order">
                                <i class="fa-solid fa-trash text-xs"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                        <i class="fa-solid fa-receipt text-4xl mb-3 opacity-20 block"></i>
                        No sales orders found matching the criteria.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination Footer -->
    @if($orders->hasPages())
        <div class="px-5 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
            {{ $orders->links() }}
        </div>
    @endif
</div>

<form id="deleteForm" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

<script>
    function openDeleteModal(orderId, orderNum) {
        showConfirm(
            `Are you sure you want to delete order "${orderNum}"? This action cannot be undone and will remove the order permanently.`,
            () => {
                const form = document.getElementById('deleteForm');
                form.action = `/admin/orders/${orderId}`;
                form.submit();
            },
            'Delete Order?'
        );
    }
</script>
@endsection
