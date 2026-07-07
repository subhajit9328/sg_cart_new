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
@php
    $headers = [
        ['label' => 'Order Number', 'key' => 'order_number', 'sortable' => true],
        ['label' => 'Customer', 'key' => 'first_name', 'sortable' => true],
        ['label' => 'Order Status', 'key' => 'status', 'sortable' => true],
        ['label' => 'Payment Status', 'key' => 'payment_status', 'sortable' => true],
        ['label' => 'Total', 'key' => 'total', 'sortable' => true],
        ['label' => 'Date', 'key' => 'created_at', 'sortable' => true],
        ['label' => 'Actions', 'key' => 'actions', 'sortable' => false, 'align' => 'right'],
    ];
@endphp

<x-data-table
    title="All Sales Orders"
    :totalCount="$orders->total()"
    searchPlaceholder="Search by order #, email, name…"
    action="{{ route('admin.orders.index') }}"
    tableId="ordersTableWrapper"
    searchInputId="orderSearchInput"
    totalCountId="ordersTotalCount"
    clearBtnId="ordersClearBtn"
    clearBtnWrapperId="ordersClearBtnWrapper"
    :items="$orders"
    :headers="$headers"
    :filterKeys="['status', 'payment_status', 'date_range']"
    refreshBtn="true"
>
    <x-slot name="advancedFilters">
        <!-- Order Status Filter -->
        <div class="flex flex-col gap-1.5 w-full">
            <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Order Status</span>
            <x-select2 
                name="status" 
                id="order_status_filter"
                placeholder="All Order Statuses"
                :selected="request('status')"
                :compact="true"
                :allowClear="false"
                :searchable="false"
            >
                <option value="New Order" {{ request('status') === 'New Order' ? 'selected' : '' }}>New Order</option>
                <option value="Processed" {{ request('status') === 'Processed' ? 'selected' : '' }}>Processed</option>
                <option value="Shipped" {{ request('status') === 'Shipped' ? 'selected' : '' }}>Shipped</option>
                <option value="Out for Delivery" {{ request('status') === 'Out for Delivery' ? 'selected' : '' }}>Out for Delivery</option>
                <option value="Delivered" {{ request('status') === 'Delivered' ? 'selected' : '' }}>Delivered</option>
                <option value="Cancelled" {{ request('status') === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
            </x-select2>
        </div>

        <!-- Payment Status Filter -->
        <div class="flex flex-col gap-1.5 w-full">
            <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Payment Status</span>
            <x-select2 
                name="payment_status" 
                id="payment_status_filter"
                placeholder="All Payment Statuses"
                :selected="request('payment_status')"
                :compact="true"
                :allowClear="false"
                :searchable="false"
            >
                <option value="Pending" {{ request('payment_status') === 'Pending' ? 'selected' : '' }}>Pending</option>
                <option value="Paid" {{ request('payment_status') === 'Paid' ? 'selected' : '' }}>Paid</option>
                <option value="Failed" {{ request('payment_status') === 'Failed' ? 'selected' : '' }}>Failed</option>
            </x-select2>
        </div>

        <!-- Date Range Filter -->
        <div class="flex flex-col gap-1.5 w-full col-span-1 sm:col-span-2">
            <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Date Range</span>
            <x-date-picker 
                id="orderDateRangePicker" 
                name="date_range" 
                enableTime="false" 
                dateFormat="d-m-Y" 
                placeholder="Filter by date range…" 
                width="w-full" 
            />
        </div>
    </x-slot>

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
                        'New Order' => 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border-amber-200/20',
                        'Processed' => 'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 border-blue-200/20',
                        'Shipped' => 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 border-indigo-200/20',
                        'Out for Delivery' => 'bg-purple-50 dark:bg-purple-500/10 text-purple-700 dark:text-purple-400 border-purple-200/20',
                        'Delivered' => 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-200/20',
                        'Cancelled' => 'bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 border-rose-200/20',
                        'Processing' => 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border-amber-200/20',
                    ];
                    $statusIcons = [
                        'New Order' => 'fa-solid fa-spinner animate-spin mr-1',
                        'Processed' => 'fa-solid fa-box mr-1',
                        'Shipped' => 'fa-solid fa-truck-fast mr-1',
                        'Out for Delivery' => 'fa-solid fa-truck-ramp-box mr-1',
                        'Delivered' => 'fa-solid fa-circle-check mr-1',
                        'Cancelled' => 'fa-solid fa-ban mr-1',
                        'Processing' => 'fa-solid fa-spinner animate-spin mr-1',
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
</x-data-table>

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
