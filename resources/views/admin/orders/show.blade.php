@extends('layouts.admin')

@section('title', "Order {$order->order_number} — SGCart Admin")

@section('content')
<!-- Print Stylesheet -->
<style>
    @media print {
        body * {
            visibility: hidden;
        }
        #printArea, #printArea * {
            visibility: visible;
        }
        #printArea {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        .no-print {
            display: none !important;
        }
    }
</style>

<!-- Page Header -->
<div class="flex flex-wrap items-center justify-between gap-4 mb-6 no-print">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.orders.index') }}" class="w-10 h-10 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 flex items-center justify-center text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors no-underline">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="font-display text-2xl font-bold">Order Details</h1>
            <x-breadcrumbs :items="[
                ['label' => 'Admin', 'url' => route('admin.dashboard')],
                ['label' => 'Sales', 'url' => route('admin.orders.index')],
                ['label' => $order->order_number]
            ]" />
        </div>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.orders.invoice', $order->ulid) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 text-sm font-medium transition-colors no-underline cursor-pointer">
            <i class="fa-solid fa-print"></i> Print Invoice
        </a>
    </div>
</div>

<!-- Order Status Timeline Progress Stepper -->
@php
    $statusVal = $order->status->value ?? $order->status;
    $steps = ['Processing', 'Shipped', 'Delivered'];
    $currentStepIndex = array_search($statusVal, $steps);
    if ($currentStepIndex === false) {
        $currentStepIndex = -1; // If Cancelled
    }
@endphp

<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-6 mb-6 shadow-xs no-print">
    <div class="flex flex-col md:flex-row items-center justify-between gap-6">
        <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Timeline & Progress</span>
            <div class="flex items-center gap-2 mt-1">
                <h3 class="text-lg font-bold text-slate-950 dark:text-white">Current Status:</h3>
                @php
                    $statusColors = [
                        'Processing' => 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border-amber-200/20',
                        'Shipped' => 'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 border-blue-200/20',
                        'Delivered' => 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-200/20',
                        'Cancelled' => 'bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 border-rose-200/20',
                    ];
                    $colorClass = $statusColors[$statusVal] ?? 'bg-slate-50 text-slate-700 border-slate-200';
                @endphp
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $colorClass }}">
                    {{ $statusVal }}
                </span>
            </div>
        </div>

        @if($statusVal !== 'Cancelled')
        <!-- Stepper Indicators -->
        <div class="flex items-center w-full md:w-auto md:max-w-md flex-1 px-4 relative mt-2 md:mt-0">
            <!-- Progress Line Background & Active Progress Line -->
            <div class="absolute top-4 left-0 right-0 mx-8 h-1 bg-slate-100 dark:bg-slate-800 -translate-y-1/2 z-0 rounded-full">
                @php
                    $lineWidth = '0%';
                    if ($currentStepIndex === 0) $lineWidth = '33.33%';
                    if ($currentStepIndex === 1) $lineWidth = '66.67%';
                    if ($currentStepIndex === 2) $lineWidth = '100%';
                @endphp
                <div class="h-full bg-blue-600 dark:bg-blue-500 rounded-full transition-all duration-500" style="width: {{ $lineWidth }};"></div>
            </div>

            <!-- Steps Dots -->
            <div class="flex items-center justify-between w-full z-10">
                <!-- Placed -->
                <div class="flex flex-col items-center gap-1.5 bg-white dark:bg-slate-900 px-2">
                    <div class="w-8 h-8 rounded-full bg-blue-600 dark:bg-blue-500 text-white flex items-center justify-center text-xs font-bold shadow-md shadow-blue-500/20">
                        <i class="fa-solid fa-check text-[10px]"></i>
                    </div>
                    <span class="text-[10px] font-bold text-slate-800 dark:text-slate-200">Placed</span>
                </div>

                <!-- Processing -->
                <div class="flex flex-col items-center gap-1.5 bg-white dark:bg-slate-900 px-2">
                    @php
                        $processingActive = $currentStepIndex >= 0;
                    @endphp
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all border 
                        {{ $processingActive ? 'bg-blue-600 dark:bg-blue-500 text-white border-transparent shadow-md' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 border-slate-200 dark:border-slate-700' }}">
                        @if($currentStepIndex > 0)
                            <i class="fa-solid fa-check text-[10px]"></i>
                        @else
                            2
                        @endif
                    </div>
                    <span class="text-[10px] font-bold {{ $processingActive ? 'text-slate-800 dark:text-slate-200' : 'text-slate-400' }}">Processing</span>
                </div>

                <!-- Shipped -->
                <div class="flex flex-col items-center gap-1.5 bg-white dark:bg-slate-900 px-2">
                    @php
                        $shippedActive = $currentStepIndex >= 1;
                    @endphp
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all border 
                        {{ $shippedActive ? 'bg-blue-600 dark:bg-blue-500 text-white border-transparent shadow-md' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 border-slate-200 dark:border-slate-700' }}">
                        @if($currentStepIndex > 1)
                            <i class="fa-solid fa-check text-[10px]"></i>
                        @else
                            3
                        @endif
                    </div>
                    <span class="text-[10px] font-bold {{ $shippedActive ? 'text-slate-800 dark:text-slate-200' : 'text-slate-400' }}">Shipped</span>
                </div>

                <!-- Delivered -->
                <div class="flex flex-col items-center gap-1.5 bg-white dark:bg-slate-900 px-2">
                    @php
                        $deliveredActive = $currentStepIndex >= 2;
                    @endphp
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all border 
                        {{ $deliveredActive ? 'bg-blue-600 dark:bg-blue-500 text-white border-transparent shadow-md' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 border-slate-200 dark:border-slate-700' }}">
                        @if($currentStepIndex >= 2)
                            <i class="fa-solid fa-check text-[10px]"></i>
                        @else
                            4
                        @endif
                    </div>
                    <span class="text-[10px] font-bold {{ $deliveredActive ? 'text-slate-800 dark:text-slate-200' : 'text-slate-400' }}">Delivered</span>
                </div>
            </div>
        </div>
        @else
        <div class="bg-rose-50 dark:bg-rose-950/15 border border-rose-200/40 rounded-xl p-4 flex items-center gap-3 text-rose-700 dark:text-rose-400 max-w-md w-full">
            <i class="fa-solid fa-triangle-exclamation text-lg"></i>
            <div class="text-xs leading-relaxed">
                <span class="font-bold block">Order Cancelled</span>
                This sales order has been marked as cancelled. System inventory returns and credit card refunds should be manually reviewed.
            </div>
        </div>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-[1fr_360px] gap-6 items-start" id="printArea">
    
    <!-- Left Column (Order Items, Shipping Details, Activity Timeline) -->
    <div class="flex flex-col gap-6">
        
        <!-- Order Items Card -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex justify-between items-center">
                <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Products Ordered</h2>
                <span class="text-[10px] font-mono text-slate-400">Order Ref: {{ $order->ulid }}</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-800/50">
                            <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Product</th>
                            <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">SKU</th>
                            <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Attributes</th>
                            <th class="px-5 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Price</th>
                            <th class="px-5 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Qty</th>
                            <th class="px-5 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($order->items as $item)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/15 transition-colors">
                            <td class="px-5 py-4 text-slate-800 dark:text-slate-100 text-sm">
                                <div class="flex items-center gap-3">
                                    @if($item->product && $item->product->image)
                                        <img src="{{ Storage::url($item->product->image) }}" class="w-10 h-10 object-cover rounded-lg border border-slate-200 bg-white no-print">
                                    @endif
                                    <div>
                                        <span class="font-semibold block">{{ $item->product_name }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-xs font-mono text-slate-500 dark:text-slate-400">
                                {{ $item->product_sku ?? '—' }}
                            </td>
                            <td class="px-5 py-4 text-xs text-slate-600 dark:text-slate-400 whitespace-nowrap">
                                @if($item->size)
                                    <span class="inline-flex px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-[10px] font-bold">Sz: {{ $item->size }}</span>
                                @endif
                                @if($item->color)
                                    <span class="inline-flex px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-[10px] font-bold">Col: {{ $item->color }}</span>
                                @endif
                                @if(!$item->size && !$item->color)
                                    —
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right text-xs font-mono text-slate-600 dark:text-slate-300">
                                ₹{{ number_format($item->price, 2) }}
                            </td>
                            <td class="px-5 py-4 text-center text-xs font-semibold text-slate-800 dark:text-slate-100">
                                {{ $item->quantity }}
                            </td>
                            <td class="px-5 py-4 text-right text-xs font-bold text-slate-900 dark:text-slate-100 font-mono">
                                ₹{{ number_format($item->price * $item->quantity, 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Shipping & Delivery Address Card -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Shipping Details</h2>
            </div>
            <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Recipient Info</h3>
                    <div class="space-y-1.5 text-sm text-slate-700 dark:text-slate-300">
                        <p class="font-semibold text-slate-800 dark:text-slate-100">{{ $order->first_name }} {{ $order->last_name }}</p>
                        <p class="flex items-center gap-2"><i class="fa-regular fa-envelope text-slate-400 text-xs"></i> {{ $order->email }}</p>
                    </div>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Delivery Address</h3>
                    <div class="space-y-1 text-sm text-slate-700 dark:text-slate-300">
                        <p>{{ $order->address }}</p>
                        <p>{{ $order->city }}, {{ $order->state ?? '—' }} {{ $order->zip }}</p>
                        <p class="font-semibold text-slate-800 dark:text-slate-100 mt-1"><i class="fa-solid fa-globe text-slate-400 text-xs mr-1"></i>{{ $order->country ?? '—' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Log History Timeline -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden no-print">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Order Activity Log</h2>
            </div>
            <div class="p-5">
                <ol class="relative border-l border-slate-200 dark:border-slate-800 space-y-5 ml-2.5">                  
                    <li class="mb-4 ml-6">
                        <span class="absolute flex items-center justify-center w-5 h-5 bg-blue-100 dark:bg-blue-900/30 rounded-full -left-2.5 ring-4 ring-white dark:ring-slate-900 text-blue-600">
                            <i class="fa-solid fa-circle-check text-[8px]"></i>
                        </span>
                        <div class="flex items-center justify-between gap-4">
                            <h4 class="text-xs font-semibold text-slate-800 dark:text-slate-200">Order Completed & Payment Authorized</h4>
                            <time class="text-[9px] font-semibold text-slate-400">{{ $order->created_at->format('M d, Y H:i') }}</time>
                        </div>
                        <p class="text-xs text-slate-400 mt-0.5">Order placed and payment charged via Card (masked reference: {{ $order->card_number_masked }}).</p>
                    </li>
                    <li class="ml-6">
                        <span class="absolute flex items-center justify-center w-5 h-5 bg-slate-100 dark:bg-slate-800 rounded-full -left-2.5 ring-4 ring-white dark:ring-slate-900 text-slate-400">
                            <i class="fa-solid fa-pen-nib text-[8px]"></i>
                        </span>
                        <div class="flex items-center justify-between gap-4">
                            <h4 class="text-xs font-semibold text-slate-800 dark:text-slate-200">Order record created</h4>
                            <time class="text-[9px] font-semibold text-slate-400">{{ $order->created_at->format('M d, Y H:i') }}</time>
                        </div>
                        <p class="text-xs text-slate-400 mt-0.5">Assigned Order Reference: {{ $order->order_number }}</p>
                    </li>
                </ol>
            </div>
        </div>
    </div>

    <!-- Right Column (Status widget, Payment Summary, Customer Insights) -->
    <div class="flex flex-col gap-6">
        
        <!-- Status Management Card -->
        <div class="bg-white dark:bg-slate-900 border border-[#e2e8f0] dark:border-slate-800 rounded-xl shadow-sm overflow-hidden no-print">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Update Order Status</h2>
            </div>
            <div class="p-5">
                <form action="{{ route('admin.orders.update', $order->ulid) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Order Status</label>
                        <select name="status" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2 px-3 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-slate-800 dark:text-slate-100 cursor-pointer">
                            <option value="Processing" {{ $statusVal === 'Processing' ? 'selected' : '' }}>Processing</option>
                            <option value="Shipped" {{ $statusVal === 'Shipped' ? 'selected' : '' }}>Shipped</option>
                            <option value="Delivered" {{ $statusVal === 'Delivered' ? 'selected' : '' }}>Delivered</option>
                            <option value="Cancelled" {{ $statusVal === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Payment Status</label>
                        <select name="payment_status" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2 px-3 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-slate-800 dark:text-slate-100 cursor-pointer">
                            <option value="Pending" {{ ($order->payment_status->value ?? $order->payment_status) === 'Pending' ? 'selected' : '' }}>Pending</option>
                            <option value="Paid" {{ ($order->payment_status->value ?? $order->payment_status) === 'Paid' ? 'selected' : '' }}>Paid</option>
                            <option value="Failed" {{ ($order->payment_status->value ?? $order->payment_status) === 'Failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs font-semibold py-2 px-4 rounded-lg shadow-md transition-all cursor-pointer">
                        Save Status Updates
                    </button>
                </form>
            </div>
        </div>

        <!-- Customer Insights Card -->
        @if($order->customer)
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden no-print">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Customer Insights</h2>
            </div>
            <div class="p-5 flex flex-col gap-3.5 text-xs text-slate-600 dark:text-slate-300">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-slate-950 text-white rounded-full flex items-center justify-center font-bold text-sm">
                        {{ strtoupper(substr($order->customer->name, 0, 2)) }}
                    </div>
                    <div>
                        <span class="font-bold text-slate-800 dark:text-slate-100 block text-xs">{{ $order->customer->name }}</span>
                        <span class="text-[10px] text-slate-400 block mt-0.5">{{ $order->customer->email }}</span>
                    </div>
                </div>

                <div class="border-t border-slate-100 dark:border-slate-800 my-1"></div>

                <div class="flex justify-between">
                    <span>Account Created</span>
                    <span class="font-semibold text-slate-800 dark:text-slate-100">{{ $order->customer->created_at->format('M Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Lifetime Orders</span>
                    <span class="font-semibold text-slate-800 dark:text-slate-100">{{ $order->customer->orders()->count() }} orders</span>
                </div>
                <div class="flex justify-between">
                    <span>Lifetime LTV spend</span>
                    <span class="font-bold text-slate-950 dark:text-white font-mono">₹{{ number_format($order->customer->orders()->where('payment_status', 'Paid')->sum('total'), 2) }}</span>
                </div>
            </div>
        </div>
        @endif

        <!-- Payment & Financial Summary Card -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Payment Summary</h2>
            </div>
            <div class="p-5 flex flex-col gap-3.5 text-xs text-slate-600 dark:text-slate-300">
                <div class="flex justify-between">
                    <span>Payment Method</span>
                    <span class="font-semibold text-slate-800 dark:text-slate-100">{{ $order->payment_method }}</span>
                </div>
                @if($order->card_number_masked)
                <div class="flex justify-between">
                    <span>Card Number</span>
                    <span class="font-mono text-slate-800 dark:text-slate-100">{{ $order->card_number_masked }}</span>
                </div>
                @endif
                @if($order->card_name)
                <div class="flex justify-between">
                    <span>Name on Card</span>
                    <span class="font-semibold text-slate-800 dark:text-slate-100">{{ $order->card_name }}</span>
                </div>
                @endif

                <div class="border-t border-slate-100 dark:border-slate-800 my-1"></div>

                <div class="flex justify-between">
                    <span>Subtotal</span>
                    <span class="font-mono">₹{{ number_format($order->subtotal, 2) }}</span>
                </div>
                @if($order->discount > 0)
                <div class="flex justify-between text-emerald-600 font-semibold">
                    <span>Discount</span>
                    <span class="font-mono">-₹{{ number_format($order->discount, 2) }}</span>
                </div>
                @endif
                <div class="flex justify-between">
                    <span>Tax (8%)</span>
                    <span class="font-mono">₹{{ number_format($order->tax, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Shipping @if($order->shipping_method) ({{ $order->shipping_method }}) @endif</span>
                    <span class="font-mono {{ $order->shipping_charge > 0 ? 'text-slate-800 dark:text-slate-200' : 'text-emerald-600 font-semibold' }}">
                        {{ $order->shipping_charge > 0 ? '₹' . number_format($order->shipping_charge, 2) : 'Free' }}
                    </span>
                </div>

                <div class="border-t border-slate-100 dark:border-slate-800 my-1"></div>

                <div class="flex justify-between font-bold text-slate-900 dark:text-slate-100 text-sm">
                    <span>Total Amount</span>
                    <span class="font-mono">₹{{ number_format($order->total, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
