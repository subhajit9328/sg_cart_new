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
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Products Ordered</h2>
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

        <!-- Shipping & Billing Address Card -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex items-center justify-between">
                <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Address & Contact Information</h2>
                @if($order->shipping_and_billing_same)
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200/20">
                        <i class="fa-solid fa-circle-check"></i> Billing same as Shipping
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 border border-blue-200/20">
                        <i class="fa-solid fa-receipt"></i> Separate Billing Address
                    </span>
                @endif
            </div>
            <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-6 divide-y md:divide-y-0 md:divide-x divide-slate-100 dark:divide-slate-800">
                <!-- Shipping Address Column -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fa-solid fa-truck text-slate-400"></i> Shipping Address
                        </h3>
                        @if($order->address_type)
                            @php
                                $typeIcons = [
                                    'office' => 'fa-briefcase',
                                    'work' => 'fa-laptop-code',
                                    'other' => 'fa-location-dot'
                                ];
                                $icon = $typeIcons[strtolower($order->address_type)] ?? 'fa-location-dot';
                            @endphp
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-[10px] font-bold text-slate-600 dark:text-slate-300 uppercase">
                                <i class="fa-solid {{ $icon }}"></i> {{ $order->address_type }}
                            </span>
                        @endif
                    </div>
                    
                    <div class="space-y-2 text-sm text-slate-700 dark:text-slate-300">
                        <p class="font-semibold text-slate-900 dark:text-white text-base">{{ $order->first_name }} {{ $order->last_name }}</p>
                        
                        <div class="space-y-1 text-slate-600 dark:text-slate-400">
                            <p class="flex items-center gap-2"><i class="fa-regular fa-envelope w-4 text-slate-400"></i> {{ $order->email }}</p>
                            <p class="flex items-center gap-2"><i class="fa-solid fa-phone w-4 text-slate-400"></i> {{ $order->phone ?? '—' }}</p>
                            @if($order->alternate_phone)
                                <p class="flex items-center gap-2"><i class="fa-solid fa-phone-volume w-4 text-slate-400"></i> {{ $order->alternate_phone }} (Alt)</p>
                            @endif
                        </div>

                        <div class="pt-2 border-t border-slate-50 dark:border-slate-800 space-y-1">
                            <p class="font-medium text-slate-850 dark:text-slate-200">{{ $order->address }}</p>
                            @if($order->landmark)
                                <p class="text-xs text-slate-500 dark:text-slate-400 italic">Landmark: {{ $order->landmark }}</p>
                            @endif
                            <p>{{ $order->city }}, {{ $order->state ?? '—' }} {{ $order->zip }}</p>
                            <p class="font-semibold text-slate-800 dark:text-slate-100 flex items-center gap-1.5"><i class="fa-solid fa-globe text-slate-400 text-xs"></i>{{ $order->country ?? '—' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Billing Address Column -->
                <div class="space-y-4 md:pl-6 pt-4 md:pt-0">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-receipt text-slate-400"></i> Billing Address
                    </h3>
                    
                    @php
                        $bFirstName = $order->shipping_and_billing_same ? $order->first_name : $order->billing_first_name;
                        $bLastName = $order->shipping_and_billing_same ? $order->last_name : $order->billing_last_name;
                        $bPhone = $order->shipping_and_billing_same ? $order->phone : $order->billing_phone;
                        $bAddress = $order->shipping_and_billing_same ? $order->address : $order->billing_address;
                        $bCity = $order->shipping_and_billing_same ? $order->city : $order->billing_city;
                        $bState = $order->shipping_and_billing_same ? $order->state : $order->billing_state;
                        $bZip = $order->shipping_and_billing_same ? $order->zip : $order->billing_zip;
                        $bCountry = $order->shipping_and_billing_same ? $order->country : $order->billing_country;
                    @endphp

                    <div class="space-y-2 text-sm text-slate-700 dark:text-slate-300">
                        <p class="font-semibold text-slate-900 dark:text-white text-base">{{ $bFirstName }} {{ $bLastName }}</p>
                        
                        @if($bPhone)
                        <div class="space-y-1 text-slate-600 dark:text-slate-400">
                            <p class="flex items-center gap-2"><i class="fa-solid fa-phone w-4 text-slate-400"></i> {{ $bPhone }}</p>
                        </div>
                        @endif

                        <div class="pt-2 border-t border-slate-50 dark:border-slate-800 space-y-1">
                            <p class="font-medium text-slate-850 dark:text-slate-200">{{ $bAddress }}</p>
                            <p>{{ $bCity }}, {{ $bState ?? '—' }} {{ $bZip }}</p>
                            <p class="font-semibold text-slate-800 dark:text-slate-100 flex items-center gap-1.5"><i class="fa-solid fa-globe text-slate-400 text-xs"></i>{{ $bCountry ?? '—' }}</p>
                        </div>
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
                    @foreach($order->payments as $payment)
                        @php
                            $pStatus = $payment->status->value ?? $payment->status;
                        @endphp
                        <li class="mb-4 ml-6">
                            @if($pStatus === 'Paid')
                                <span class="absolute flex items-center justify-center w-5 h-5 bg-emerald-100 dark:bg-emerald-950/30 rounded-full -left-2.5 ring-4 ring-white dark:ring-slate-900 text-emerald-600">
                                    <i class="fa-solid fa-circle-check text-[8px]"></i>
                                </span>
                            @elseif($pStatus === 'Pending')
                                <span class="absolute flex items-center justify-center w-5 h-5 bg-amber-100 dark:bg-amber-900/30 rounded-full -left-2.5 ring-4 ring-white dark:ring-slate-900 text-amber-600 animate-pulse">
                                    <i class="fa-solid fa-clock text-[8px]"></i>
                                </span>
                            @else
                                <span class="absolute flex items-center justify-center w-5 h-5 bg-rose-100 dark:bg-rose-950/30 rounded-full -left-2.5 ring-4 ring-white dark:ring-slate-900 text-rose-600">
                                    <i class="fa-solid fa-circle-xmark text-[8px]"></i>
                                </span>
                            @endif
                            <div class="flex items-center justify-between gap-4">
                                <h4 class="text-xs font-semibold text-slate-800 dark:text-slate-200">
                                    Payment {{ $pStatus }}: {{ $payment->payment_method }}
                                </h4>
                                <time class="text-[9px] font-semibold text-slate-400">{{ $payment->created_at->format('M d, Y H:i') }}</time>
                            </div>
                            <p class="text-xs text-slate-400 mt-0.5">
                                @if($pStatus === 'Paid')
                                    Payment of ₹{{ number_format($payment->amount, 2) }} was successfully processed.
                                    @if($payment->card_number_masked) (Card: {{ $payment->card_number_masked }}) @endif
                                @elseif($pStatus === 'Pending')
                                    Payment of ₹{{ number_format($payment->amount, 2) }} is pending customer action or manual clearance.
                                @else
                                    Payment attempt of ₹{{ number_format($payment->amount, 2) }} failed.
                                @endif
                                @if($payment->transaction_id)
                                    <span class="block text-[10px] text-slate-450 mt-0.5 font-mono">Txn ID: {{ $payment->transaction_id }}</span>
                                @endif
                            </p>
                        </li>
                    @endforeach

                    @if($statusVal === 'Delivered')
                    <li class="mb-4 ml-6">
                        <span class="absolute flex items-center justify-center w-5 h-5 bg-emerald-100 dark:bg-emerald-900/30 rounded-full -left-2.5 ring-4 ring-white dark:ring-slate-900 text-emerald-600">
                            <i class="fa-solid fa-circle-check text-[8px]"></i>
                        </span>
                        <div class="flex items-center justify-between gap-4">
                            <h4 class="text-xs font-semibold text-slate-800 dark:text-slate-200">Order Delivered</h4>
                            <time class="text-[9px] font-semibold text-slate-400">{{ $order->updated_at->format('M d, Y H:i') }}</time>
                        </div>
                        <p class="text-xs text-slate-400 mt-0.5">Package successfully delivered to the recipient.</p>
                    </li>
                    @endif

                    @if($statusVal === 'Shipped' || $statusVal === 'Delivered')
                    <li class="mb-4 ml-6">
                        <span class="absolute flex items-center justify-center w-5 h-5 bg-blue-100 dark:bg-blue-900/30 rounded-full -left-2.5 ring-4 ring-white dark:ring-slate-900 text-blue-600">
                            <i class="fa-solid fa-truck text-[8px]"></i>
                        </span>
                        <div class="flex items-center justify-between gap-4">
                            <h4 class="text-xs font-semibold text-slate-800 dark:text-slate-200">Order Shipped (Transit Started)</h4>
                            <time class="text-[9px] font-semibold text-slate-400">
                                @if($statusVal === 'Delivered')
                                    {{ $order->created_at->addDay()->format('M d, Y H:i') }}
                                @else
                                    {{ $order->updated_at->format('M d, Y H:i') }}
                                @endif
                            </time>
                        </div>
                        <p class="text-xs text-slate-400 mt-0.5">Dispatched via {{ $order->shipping_carrier ?? 'Delhivery Express' }} with Tracking ID: <span class="font-mono font-semibold">{{ $order->tracking_number }}</span>.</p>
                    </li>
                    @endif

                    @if($statusVal === 'Cancelled')
                    <li class="mb-4 ml-6">
                        <span class="absolute flex items-center justify-center w-5 h-5 bg-rose-100 dark:bg-rose-900/30 rounded-full -left-2.5 ring-4 ring-white dark:ring-slate-900 text-rose-600">
                            <i class="fa-solid fa-ban text-[8px]"></i>
                        </span>
                        <div class="flex items-center justify-between gap-4">
                            <h4 class="text-xs font-semibold text-slate-800 dark:text-slate-200">Order Cancelled</h4>
                            <time class="text-[9px] font-semibold text-slate-400">{{ $order->updated_at->format('M d, Y H:i') }}</time>
                        </div>
                        <p class="text-xs text-slate-400 mt-0.5">This order has been marked as Cancelled.</p>
                    </li>
                    @endif

                    <li class="mb-4 ml-6">
                        <span class="absolute flex items-center justify-center w-5 h-5 bg-slate-100 dark:bg-slate-800 rounded-full -left-2.5 ring-4 ring-white dark:ring-slate-900 text-slate-500">
                            <i class="fa-solid fa-circle-check text-[8px]"></i>
                        </span>
                        <div class="flex items-center justify-between gap-4">
                            <h4 class="text-xs font-semibold text-slate-800 dark:text-slate-200">Order Completed & Payment Authorized</h4>
                            <time class="text-[9px] font-semibold text-slate-400">{{ $order->created_at->format('M d, Y H:i') }}</time>
                        </div>
                        <p class="text-xs text-slate-400 mt-0.5">Order placed and payment charged via Card (masked reference: {{ $order->card_number_masked }}).</p>
                    </li>
                    <li class="ml-6">
                        <span class="absolute flex items-center justify-center w-5 h-5 bg-slate-100 dark:bg-slate-800 rounded-full -left-2.5 ring-4 ring-white dark:ring-slate-900 text-slate-450">
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

                    <div class="border-t border-slate-100 dark:border-slate-800 pt-3 space-y-3">
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Logistics & Tracking Info</span>
                        
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Tracking Number</label>
                            <input type="text" name="tracking_number" value="{{ old('tracking_number', $order->tracking_number) }}" placeholder="e.g., SG-TRK-8327943" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 px-3 text-xs outline-none focus:border-blue-500 text-slate-800 dark:text-slate-100">
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Shipping Carrier</label>
                            <input type="text" name="shipping_carrier" value="{{ old('shipping_carrier', $order->shipping_carrier) }}" placeholder="e.g., Delhivery Express" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 px-3 text-xs outline-none focus:border-blue-500 text-slate-800 dark:text-slate-100">
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Tracking URL</label>
                            <input type="url" name="tracking_url" value="{{ old('tracking_url', $order->tracking_url) }}" placeholder="https://..." class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 px-3 text-xs outline-none focus:border-blue-500 text-slate-800 dark:text-slate-100">
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Est. Delivery Date</label>
                            <input type="date" name="estimated_delivery_at" value="{{ old('estimated_delivery_at', $order->estimated_delivery_at ? $order->estimated_delivery_at->format('Y-m-d') : '') }}" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 px-3 text-xs outline-none focus:border-blue-500 text-slate-800 dark:text-slate-100">
                        </div>
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
                    <span class="font-bold text-slate-950 dark:text-white font-mono">₹{{ number_format($order->customer->orders()->whereHas('payments', fn($q) => $q->where('status', \App\Enums\PaymentStatus::PAID))->sum('total'), 2) }}</span>
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
                @if($order->transaction_id)
                <div class="flex justify-between">
                    <span>Transaction ID</span>
                    <span class="font-mono font-semibold text-slate-800 dark:text-slate-100">{{ $order->transaction_id }}</span>
                </div>
                @endif
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
                    <span>{{ $order->tax_method ?? 'Tax' }}</span>
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

        <!-- Decoupled Payment Transaction Ledger Card -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Transaction Ledger</h2>
            </div>
            <div class="p-5 flex flex-col gap-4">
                @forelse($order->payments as $payment)
                    <div class="flex flex-col gap-1.5 p-3 rounded-lg border border-slate-100 dark:border-slate-800 bg-slate-50/30 dark:bg-slate-900/40 text-xs">
                        <div class="flex justify-between items-center">
                            <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $payment->payment_method }}</span>
                            @php
                                $statusStr = $payment->status->value ?? $payment->status;
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold
                                @if($statusStr === 'Paid') bg-emerald-50 text-emerald-600 dark:bg-emerald-950/20 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/30
                                @elseif($statusStr === 'Pending') bg-amber-50 text-amber-600 dark:bg-amber-950/20 dark:text-amber-400 border border-amber-100 dark:border-amber-800/30
                                @else bg-rose-50 text-rose-600 dark:bg-rose-955/20 dark:text-rose-455 border border-rose-100 dark:border-rose-900/30 @endif">
                                {{ $statusStr }}
                            </span>
                        </div>
                        <div class="flex justify-between text-[11px] text-slate-400">
                            <span>Amount</span>
                            <span class="font-mono text-slate-700 dark:text-slate-350">₹{{ number_format($payment->amount, 2) }}</span>
                        </div>
                        @if($payment->transaction_id)
                        <div class="flex justify-between text-[11px] text-slate-400">
                            <span>Txn Ref</span>
                            <span class="font-mono text-slate-700 dark:text-slate-350 select-all">{{ $payment->transaction_id }}</span>
                        </div>
                        @endif
                        @if($payment->card_number_masked)
                        <div class="flex justify-between text-[11px] text-slate-400">
                            <span>Card</span>
                            <span class="font-mono text-slate-700 dark:text-slate-350">{{ $payment->card_number_masked }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between text-[10px] text-slate-400 mt-1 border-t border-slate-100/50 dark:border-slate-800/50 pt-1.5">
                            <span>Timestamp</span>
                            <span>{{ $payment->created_at->format('M d, Y h:i A') }}</span>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-slate-400 dark:text-slate-500 py-4">
                        <i class="fa-solid fa-credit-card text-xl mb-1.5 opacity-20 block"></i>
                        No recorded transactions.
                    </div>
                @endforelse
            </div>
        </div>
        <!-- Shipping Tracking Card -->
        @if($order->tracking_number)
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex items-center justify-between">
                <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Shipment Tracking</h2>
                <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-blue-50 dark:bg-blue-900/30 text-[9px] font-bold text-blue-700 dark:text-blue-400 uppercase">Active</span>
            </div>
            <div class="p-5 flex flex-col gap-3.5 text-xs text-slate-600 dark:text-slate-300">
                <div class="flex justify-between">
                    <span>Carrier</span>
                    <span class="font-semibold text-slate-800 dark:text-slate-100">{{ $order->shipping_carrier ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Tracking Number</span>
                    <span class="font-mono font-semibold text-slate-800 dark:text-slate-100">{{ $order->tracking_number }}</span>
                </div>
                @if($order->tracking_url)
                <div class="flex justify-between">
                    <span>Tracking Link</span>
                    <a href="{{ $order->tracking_url }}" target="_blank" class="text-blue-600 hover:text-blue-805 font-semibold flex items-center gap-1">Track Shipment <i class="fa-solid fa-up-right-from-square text-[9px]"></i></a>
                </div>
                @endif
                @if($order->estimated_delivery_at)
                <div class="flex justify-between">
                    <span>Est. Delivery</span>
                    <span class="font-semibold text-slate-800 dark:text-slate-100">{{ $order->estimated_delivery_at->format('M d, Y') }}</span>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
