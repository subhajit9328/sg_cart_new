@extends('marketplace::layouts.seller')

@section('title', 'Order Details — Seller Portal')

@section('content')
<div class="w-full space-y-6">
    <!-- Breadcrumb & Status Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-2">
        <div class="flex items-center gap-3">
            <a href="{{ route('seller.orders.index') }}" class="w-10 h-10 rounded-lg border border-slate-200 dark:border-slate-800 flex items-center justify-center text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="font-display text-2xl font-bold text-slate-900 dark:text-white">Order Details</h1>
                <p class="text-xs text-slate-400 mt-0.5">Placed on {{ $order->created_at->format('d M Y, H:i A') }}</p>
            </div>
        </div>
        
        <!-- Status Badges -->
        <div class="flex flex-wrap items-center gap-2">
            <!-- Order Status -->
            @php
                $statusColors = [
                    'New Order' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/30 dark:text-blue-400 dark:border-blue-900/40',
                    'Processing' => 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-950/30 dark:text-indigo-400 dark:border-indigo-900/40',
                    'Processed' => 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-950/30 dark:text-indigo-400 dark:border-indigo-900/40',
                    'Shipped' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/30 dark:text-amber-400 dark:border-amber-900/40',
                    'Out for Delivery' => 'bg-orange-50 text-orange-700 border-orange-200 dark:bg-orange-950/30 dark:text-orange-400 dark:border-orange-900/40',
                    'Delivered' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/30 dark:text-emerald-400 dark:border-emerald-900/40',
                    'Cancelled' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/30 dark:text-rose-400 dark:border-rose-900/40',
                ];
                $orderStatusVal = $order->status->value ?? $order->status->name ?? $order->status;
                $statusColorClass = $statusColors[$orderStatusVal] ?? 'bg-slate-50 text-slate-700 border-slate-200 dark:bg-slate-900 dark:text-slate-400 dark:border-slate-800';
            @endphp
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-xs font-bold uppercase tracking-wider {{ $statusColorClass }}">
                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                {{ $orderStatusVal }}
            </span>

            <!-- Payment Status -->
            @php
                $paymentStatusColors = [
                    'Pending' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/30 dark:text-amber-400 dark:border-amber-900/40',
                    'Paid' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/30 dark:text-emerald-400 dark:border-emerald-900/40',
                    'Failed' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/30 dark:text-rose-400 dark:border-rose-900/40',
                ];
                $payStatusVal = $order->payment_status->value ?? $order->payment_status->name ?? $order->payment_status ?? 'Pending';
                $payColorClass = $paymentStatusColors[$payStatusVal] ?? 'bg-slate-50 text-slate-700 border-slate-200 dark:bg-slate-900/10';
            @endphp
            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border text-xs font-bold uppercase tracking-wider {{ $payColorClass }}">
                <i class="fa-solid fa-wallet text-[10px] mr-0.5"></i>
                Payment: {{ $payStatusVal }}
            </span>
        </div>
    </div>

    <!-- Financial Calculation & Setup -->
    @php
        $mySubtotal = 0;
        $totalCommission = 0;
        $totalEarnings = 0;
        $itemCount = 0;
        
        foreach($order->items as $item) {
            $mySubtotal += $item->price * $item->quantity;
            $itemCount += $item->quantity;
            $comm = $commissions->get($item->id);
            if ($comm) {
                $totalCommission += (float) $comm->commission_amount;
                $totalEarnings += (float) $comm->seller_earning;
            } else {
                $totalEarnings += $item->price * $item->quantity;
            }
        }
    @endphp

    <!-- KPI Summary Cards Row -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Items Count -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-sm flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-lg bg-blue-500/10 dark:bg-blue-500/20 text-blue-500 flex items-center justify-center text-lg flex-shrink-0">
                <i class="fa-solid fa-box"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Items</p>
                <p class="font-display text-xl font-bold text-slate-800 dark:text-white mt-1">{{ $itemCount }} {{ Str::plural('unit', $itemCount) }}</p>
            </div>
        </div>

        <!-- Card 2: Gross Sales -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-sm flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-lg bg-indigo-500/10 dark:bg-indigo-500/20 text-indigo-500 flex items-center justify-center text-lg flex-shrink-0">
                <i class="fa-solid fa-file-invoice-dollar"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">My Gross Sales</p>
                <p class="font-display text-xl font-bold text-slate-800 dark:text-white mt-1 font-mono">₹{{ number_format($mySubtotal, 2) }}</p>
            </div>
        </div>

        <!-- Card 3: Platform Fees -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-sm flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-lg bg-rose-500/10 dark:bg-rose-500/20 text-rose-500 flex items-center justify-center text-lg flex-shrink-0">
                <i class="fa-solid fa-percent"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Platform Fees</p>
                <p class="font-display text-xl font-bold text-slate-800 dark:text-white mt-1 font-mono">-₹{{ number_format($totalCommission, 2) }}</p>
            </div>
        </div>

        <!-- Card 4: Net Earnings -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-sm flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-lg bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-500 flex items-center justify-center text-lg flex-shrink-0">
                <i class="fa-solid fa-wallet"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Net Earnings</p>
                <p class="font-display text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-1 font-mono">₹{{ number_format($totalEarnings, 2) }}</p>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Order Items Column -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 dark:border-slate-850 flex items-center justify-between">
                    <h3 class="font-display text-base font-bold text-slate-800 dark:text-white">Order Reference: {{ $order->order_number }}</h3>
                    <span class="text-xs text-slate-400 font-mono">{{ $order->ulid }}</span>
                </div>
                
                <div class="divide-y divide-slate-100 dark:divide-slate-850">
                    @foreach($order->items as $item)
                        @php
                            $comm = $commissions->get($item->id);
                            $productImage = $item->product && $item->product->image ? Storage::url($item->product->image) : asset('images/no-image.svg');
                        @endphp
                        <div class="p-6 hover:bg-slate-50/50 dark:hover:bg-slate-850/20 transition-colors">
                            <div class="flex flex-col sm:flex-row sm:items-start gap-4">
                                <!-- Product Image -->
                                <div class="w-16 h-16 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-800 flex-shrink-0 bg-slate-50">
                                    <img src="{{ $productImage }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover">
                                </div>

                                <!-- Product Info -->
                                <div class="flex-grow space-y-1">
                                    <h4 class="font-semibold text-slate-800 dark:text-white hover:text-blue-500 transition-colors">
                                        {{ $item->product_name }}
                                    </h4>
                                    
                                    <div class="flex flex-wrap items-center gap-3 text-xs text-slate-400">
                                        <span>SKU: <strong class="text-slate-650 dark:text-slate-350 font-mono font-normal">{{ $item->product_sku ?? 'N/A' }}</strong></span>
                                        @if($item->size)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-[11px] font-medium text-slate-600 dark:text-slate-400">
                                                Size: {{ $item->size }}
                                            </span>
                                        @endif
                                        @if($item->color)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-[11px] font-medium text-slate-600 dark:text-slate-400">
                                                Color: <span class="w-2.5 h-2.5 rounded-full border border-slate-300 dark:border-slate-700 animate-pulse" style="background-color: {{ $item->color }}"></span>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Financial Specs -->
                                <div class="text-right flex-shrink-0 space-y-1 min-w-[120px]">
                                    <p class="text-sm font-semibold text-slate-800 dark:text-white font-mono">₹{{ number_format($item->price, 2) }} × {{ $item->quantity }}</p>
                                    <p class="text-xs text-slate-450">Subtotal: <span class="font-semibold font-mono">₹{{ number_format($item->price * $item->quantity, 2) }}</span></p>
                                </div>
                            </div>

                            <!-- Commission breakdown per item if available -->
                            @if($comm)
                                <div class="mt-4 pt-3 border-t border-dashed border-slate-100 dark:border-slate-805 flex flex-wrap items-center justify-between text-xs gap-3">
                                    <div class="flex items-center gap-4 text-slate-400">
                                        <span>Commission Rate: <strong class="text-slate-600 dark:text-slate-300">{{ number_format($comm->commission_rate, 1) }}%</strong></span>
                                        <span>Fee deducted: <strong class="text-rose-500 font-mono">-₹{{ number_format($comm->commission_amount, 2) }}</strong></span>
                                    </div>
                                    <div class="text-emerald-600 dark:text-emerald-400 font-semibold">
                                        My Earnings: <span class="font-bold font-mono text-sm">₹{{ number_format($comm->seller_earning, 2) }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Sidebar / Contact / Details Column -->
        <div class="space-y-6">
            <!-- Customer Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm space-y-4">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-850">
                    <div class="w-10 h-10 rounded-full bg-blue-500/10 text-blue-500 flex items-center justify-center font-bold text-sm">
                        {{ substr($order->customer->name ?? 'Guest', 0, 2) }}
                    </div>
                    <div>
                        <h4 class="font-display font-bold text-slate-850 dark:text-white">{{ $order->customer->name ?? 'Guest Customer' }}</h4>
                        <p class="text-xs text-slate-400">Customer Account</p>
                    </div>
                </div>
                
                <div class="text-xs space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Email Address</span>
                        <div class="flex items-center gap-1.5">
                            <span class="text-slate-700 dark:text-slate-350 font-semibold font-mono">{{ $order->customer->email ?? 'N/A' }}</span>
                            @if($order->customer->email)
                                <button onclick="navigator.clipboard.writeText('{{ $order->customer->email }}'); window.showToast('Email copied to clipboard!', 'success');" class="text-slate-400 hover:text-blue-500 transition-colors border-none bg-transparent cursor-pointer p-0.5">
                                    <i class="fa-regular fa-copy"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                    @if($order->phone)
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Phone Number</span>
                            <span class="text-slate-700 dark:text-slate-350 font-semibold font-mono">{{ $order->phone }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Shipping Address Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-850">
                    <h4 class="font-display font-bold text-slate-850 dark:text-white flex items-center gap-1.5">
                        <i class="fa-solid fa-truck text-slate-400 text-sm"></i>
                        Shipping Address
                    </h4>
                    @if($order->address_type)
                        @php
                            $typeIcons = [
                                'office' => 'fa-briefcase',
                                'work' => 'fa-laptop-code',
                                'other' => 'fa-location-dot'
                            ];
                            $icon = $typeIcons[strtolower($order->address_type)] ?? 'fa-location-dot';
                        @endphp
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            <i class="fa-solid {{ $icon }} text-[9px]"></i> {{ $order->address_type }}
                        </span>
                    @endif
                </div>

                @if($order->address)
                    <div class="text-xs space-y-2">
                        <div class="space-y-1 text-slate-600 dark:text-slate-400">
                            <p class="font-bold text-slate-800 dark:text-white text-sm mb-1.5">{{ $order->first_name }} {{ $order->last_name }}</p>
                            <p class="leading-relaxed">{{ $order->address }}</p>
                            @if($order->landmark)
                                <p class="italic text-[11px] text-slate-400 pt-0.5">Landmark: {{ $order->landmark }}</p>
                            @endif
                            <p class="pt-1">{{ $order->city }}, {{ $order->state ?? '—' }} {{ $order->zip }}</p>
                            <p class="font-semibold text-slate-700 dark:text-slate-350 mt-1 flex items-center gap-1"><i class="fa-solid fa-globe text-slate-450 text-xs"></i> {{ $order->country ?? '—' }}</p>
                        </div>
                    </div>
                @else
                    <p class="text-xs text-slate-400 italic text-center py-2">No shipping address recorded.</p>
                @endif
            </div>

            <!-- Payment & Logistics Info -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm space-y-3.5">
                <h4 class="font-display font-bold text-slate-805 dark:text-white flex items-center gap-1.5 pb-2 border-b border-slate-100 dark:border-slate-850">
                    <i class="fa-solid fa-circle-info text-slate-450 text-sm"></i>
                    Transaction Info
                </h4>

                <div class="text-xs space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Payment Method</span>
                        <span class="text-slate-800 dark:text-slate-200 font-semibold">{{ $order->payment_method }}</span>
                    </div>

                    @if($order->transaction_id)
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Transaction ID</span>
                            <div class="flex items-center gap-1">
                                <code class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 rounded text-[10px] text-slate-700 dark:text-slate-300 font-mono">{{ $order->transaction_id }}</code>
                                <button onclick="navigator.clipboard.writeText('{{ $order->transaction_id }}'); window.showToast('Transaction ID copied!', 'success');" class="text-slate-450 hover:text-blue-500 transition-colors border-none bg-transparent cursor-pointer p-0.5">
                                    <i class="fa-regular fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    @endif

                    @if($order->shipping_method)
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Shipping Courier</span>
                            <span class="text-slate-800 dark:text-slate-200 font-semibold">{{ $order->shipping_method }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
