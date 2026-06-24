@extends('layouts.store')

@section('title', 'Order Details — sgcart')

@section('content')
<div class="storefront-container py-8">
    <!-- Back to Account Link -->
    <div class="mb-6">
        <a href="{{ route('store.account', 'orders') }}" class="text-xs font-bold text-slate-500 hover:text-slate-900 transition-colors uppercase tracking-wider flex items-center gap-1.5" style="text-decoration:none">
            <i class="fa-solid fa-arrow-left-long"></i> Back to My Account
        </a>
    </div>

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border border-[#e8e4df] rounded-2xl bg-white p-6 mb-6 gap-4">
        <div>
            <h1 class="font-display font-extrabold text-lg text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-box-open text-accent"></i> Order Details — {{ $order->order_number }}
            </h1>
            <p class="text-xs text-slate-400 mt-1 flex items-center gap-2">
                Placed on {{ $order->created_at->format('M d, Y h:i A') }}
                <span class="text-slate-300">|</span>
                <a href="{{ route('store.account.order.invoice', $order->ulid) }}" class="text-accent hover:text-slate-900 transition-colors font-semibold flex items-center gap-1" style="text-decoration:none">
                    <i class="fa-solid fa-download text-xs"></i> Download Invoice PDF
                </a>
            </p>
        </div>
        
        <div class="flex flex-wrap gap-2 sm:self-center">
            @php
                $status = $order->status->value ?? $order->status;
                $paymentStatus = $order->payment_status->value ?? $order->payment_status;
            @endphp
            
            <span class="inline-flex px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider 
                @if($status === 'Processing') bg-blue-50 text-blue-700 border border-blue-200
                @elseif($status === 'Shipped') bg-amber-50 text-amber-700 border border-amber-200
                @elseif($status === 'Delivered') bg-emerald-50 text-emerald-700 border border-emerald-200
                @else bg-rose-50 text-rose-700 border border-rose-200 @endif">
                Status: {{ $status }}
            </span>
            
            <span class="inline-flex px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider 
                @if($paymentStatus === 'Paid') bg-emerald-50 text-emerald-700 border border-emerald-200
                @elseif($paymentStatus === 'Pending') bg-amber-50 text-amber-700 border border-amber-200
                @else bg-rose-50 text-rose-700 border border-rose-200 @endif">
                Payment: {{ $paymentStatus }}
            </span>
        </div>
    </div>

    <!-- Content Split Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left: Items Details (col-span-2) -->
        <div class="lg:col-span-2 flex flex-col gap-6">
            
            <!-- Stepper Progress Tracker -->
            <div class="bg-white border border-[#e8e4df] rounded-2xl p-6">
                <h4 class="font-display font-extrabold text-xs text-slate-400 uppercase tracking-wider mb-5 pb-2 border-b border-slate-100 flex items-center gap-1.5">
                    <i class="fa-solid fa-truck-fast"></i> Order Progress
                </h4>
                
                @if($status === 'Cancelled')
                    <div class="flex items-center gap-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl p-4">
                        <i class="fa-solid fa-circle-xmark text-xl text-rose-600"></i>
                        <div>
                            <h5 class="text-xs font-bold uppercase tracking-wider">Order Cancelled</h5>
                            <p class="text-xs text-rose-600/80 mt-0.5">This order has been cancelled and cannot be tracked further.</p>
                        </div>
                    </div>
                @else
                    <div class="relative mt-8 mb-4">
                        <!-- Progress Track Wrapper -->
                        <div class="absolute top-5 left-0 right-0 mx-5 h-1 bg-slate-200 -translate-y-1/2 z-0 rounded-full">
                            <!-- Active Line -->
                            <div class="h-full bg-slate-900 rounded-full transition-all duration-500" 
                                 style="width: @if($status === 'Processing') 0% @elseif($status === 'Shipped') 50% @elseif($status === 'Delivered') 100% @else 0% @endif;">
                            </div>
                        </div>
                        
                        <div class="relative z-10 flex justify-between">
                            <!-- Step 1: Processing -->
                            <div class="flex flex-col items-center">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300 border-2 
                                    @if($status === 'Processing') bg-slate-900 text-white border-slate-900 
                                    @else bg-emerald-600 text-white border-emerald-600 @endif">
                                    @if($status === 'Processing') <i class="fa-solid fa-spinner animate-spin"></i> @else <i class="fa-solid fa-check"></i> @endif
                                </div>
                                <span class="text-[11px] font-bold mt-2 @if($status === 'Processing') text-slate-900 @else text-slate-500 @endif">Processing</span>
                            </div>
                            
                            <!-- Step 2: Shipped -->
                            <div class="flex flex-col items-center">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300 border-2
                                    @if($status === 'Processing') bg-white text-slate-400 border-slate-200
                                    @elseif($status === 'Shipped') bg-slate-900 text-white border-slate-900
                                    @else bg-emerald-600 text-white border-emerald-600 @endif">
                                    @if($status === 'Delivered') <i class="fa-solid fa-check"></i> @else <i class="fa-solid fa-truck-fast"></i> @endif
                                </div>
                                <span class="text-[11px] font-bold mt-2 @if($status === 'Shipped') text-slate-900 @else text-slate-500 @endif">Shipped</span>
                            </div>
                            
                            <!-- Step 3: Delivered -->
                            <div class="flex flex-col items-center">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300 border-2
                                    @if($status === 'Delivered') bg-emerald-600 text-white border-emerald-600
                                    @else bg-white text-slate-400 border-slate-200 @endif">
                                    @if($status === 'Delivered') <i class="fa-solid fa-check"></i> @else <i class="fa-solid fa-circle-check"></i> @endif
                                </div>
                                <span class="text-[11px] font-bold mt-2 @if($status === 'Delivered') text-slate-900 @else text-slate-500 @endif">Delivered</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Items Card -->
            <div class="bg-white border border-[#e8e4df] rounded-2xl p-6">
                <h4 class="font-display font-extrabold text-xs text-slate-400 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                    <i class="fa-solid fa-list-ul"></i> Items Ordered
                </h4>
                
                <div class="flex flex-col gap-4">
                    @foreach($order->items as $item)
                        @php
                            $imgUrl = $item->product && $item->product->image 
                                ? \Illuminate\Support\Facades\Storage::url($item->product->image) 
                                : asset('images/no-image.svg');
                        @endphp
                        <div class="flex items-center gap-4 border border-[#e8e4df] rounded-xl p-4 bg-white hover:bg-slate-50/20 transition-colors">
                            <img src="{{ $imgUrl }}" alt="{{ $item->product_name }}" class="w-16 h-16 object-cover rounded-lg border border-[#e8e4df] shrink-0" />
                            <div class="min-w-0 flex-1">
                                <h5 class="text-sm font-bold text-slate-800 truncate">{{ $item->product_name }}</h5>
                                <div class="flex flex-wrap gap-x-3 gap-y-1 mt-1">
                                    <span class="text-xs text-slate-400">SKU: <span class="text-slate-600 font-medium">{{ $item->product_sku ?? 'N/A' }}</span></span>
                                    @if($item->size)
                                        <span class="text-xs text-slate-400">Size: <span class="text-slate-600 font-medium">{{ $item->size }}</span></span>
                                    @endif
                                    @if($item->color)
                                        <span class="text-xs text-slate-400">Color: <span class="text-slate-600 font-medium">{{ $item->color }}</span></span>
                                    @endif
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-xs text-slate-400">{{ $item->quantity }} × ₹{{ number_format($item->price, 2) }}</p>
                                <p class="text-sm font-extrabold text-slate-900 mt-0.5">₹{{ number_format($item->price * $item->quantity, 2) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            
        </div>

        <!-- Right Side: Shipping & Cost details -->
        <div class="flex flex-col gap-6">
            
            <!-- Shipping Card -->
            <div class="bg-white border border-[#e8e4df] rounded-2xl p-6">
                <h4 class="font-display font-extrabold text-xs text-slate-400 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100 flex items-center gap-1.5">
                    <i class="fa-solid fa-map-location-dot"></i> Shipping Details
                </h4>
                <div class="text-sm font-bold text-slate-800">{{ $order->first_name }} {{ $order->last_name }}</div>
                <div class="text-xs text-slate-500 leading-relaxed mt-1.5">
                    {{ $order->address }}<br/>
                    @if($order->city) {{ $order->city }}, @endif
                    @if($order->state) {{ $order->state }} @endif
                    @if($order->zip) {{ $order->zip }} @endif
                    @if($order->country) <br/>{{ $order->country }} @endif
                </div>
            </div>

            <!-- Payment & Summary Card -->
            <div class="bg-white border border-[#e8e4df] rounded-2xl p-6 flex flex-col gap-4">
                <h4 class="font-display font-extrabold text-xs text-slate-400 uppercase tracking-wider pb-2 border-b border-slate-100 flex items-center gap-1.5">
                    <i class="fa-solid fa-credit-card"></i> Payment & Summary
                </h4>
                
                <div class="text-xs text-slate-500 leading-relaxed">
                    <span class="font-bold text-slate-700">Payment Details:</span>
                    <div class="mt-0.5 font-medium text-slate-800">
                        {{ $order->payment_method ?? 'N/A' }} 
                        @if($order->card_number_masked) ({{ $order->card_number_masked }}) @endif
                    </div>
                </div>

                <div class="flex flex-col gap-2.5 text-xs border-t border-slate-100 pt-4">
                    <div class="flex justify-between text-slate-500">
                        <span>Subtotal</span>
                        <span>₹{{ number_format($order->subtotal, 2) }}</span>
                    </div>
                    @if($order->discount > 0)
                        <div class="flex justify-between text-slate-500">
                            <span>Discount</span>
                            <span class="text-emerald-600 font-bold">-₹{{ number_format($order->discount, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-slate-500">
                        <span>Tax</span>
                        <span>₹{{ number_format($order->tax, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-slate-800 font-extrabold text-sm border-t border-slate-100 pt-3 mt-1.5">
                        <span>Total</span>
                        <span class="text-slate-950">₹{{ number_format($order->total, 2) }}</span>
                    </div>
                </div>
            </div>
            
        </div>
        
    </div>
</div>
@endsection
