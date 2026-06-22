@extends('layouts.store')

@section('title', 'Checkout — sgcart')

@section('content')
<div class="max-w-[1400px] mx-auto px-6 pt-8 pb-12 md:pt-12 md:pb-20">
    <h1 class="font-display font-extrabold text-3xl md:text-4xl tracking-tight mb-8">Checkout</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-[1fr_360px] gap-6 items-start">
        
        <!-- Address & Payment Entry Form (Left) -->
        <form action="{{ route('store.checkout.order') }}" method="POST" class="contents">
            @csrf
            
            <div class="flex flex-col gap-4">
                <!-- Shipping Address Card -->
                <div class="bg-white border border-[#e8e4df] rounded-2xl p-5 md:p-6">
                    <div class="font-display font-bold text-base mb-4 flex items-center gap-2.5">
                        <i class="fa-solid fa-location-dot text-accent text-sm"></i> Shipping Address
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="label">First Name <span class="text-rose-600">*</span></label>
                            <input type="text" name="first_name" required value="{{ old('first_name') }}" class="inp" placeholder="John"/>
                            @error('first_name') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="label">Last Name <span class="text-rose-600">*</span></label>
                            <input type="text" name="last_name" required value="{{ old('last_name') }}" class="inp" placeholder="Doe"/>
                            @error('last_name') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="label">Email Address <span class="text-rose-600">*</span></label>
                        <input type="email" name="email" required value="{{ old('email') }}" class="inp" placeholder="john.doe@example.com"/>
                        @error('email') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="label">Street Address <span class="text-rose-600">*</span></label>
                        <input type="text" name="address" required value="{{ old('address') }}" class="inp" placeholder="123 Main Street, Apt 4B"/>
                        @error('address') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="label">City <span class="text-rose-600">*</span></label>
                            <input type="text" name="city" required value="{{ old('city') }}" class="inp" placeholder="New York"/>
                            @error('city') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="label">Zip Code <span class="text-rose-600">*</span></label>
                            <input type="text" name="zip" required value="{{ old('zip') }}" class="inp" placeholder="10001"/>
                            @error('zip') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Payment Method Card -->
                <div class="bg-white border border-[#e8e4df] rounded-2xl p-5 md:p-6">
                    <div class="font-display font-bold text-base mb-4 flex items-center gap-2.5">
                        <i class="fa-solid fa-credit-card text-accent text-sm"></i> Payment Method
                    </div>
                    
                    <div class="mb-4">
                        <label class="label">Name on Card <span class="text-rose-600">*</span></label>
                        <input type="text" name="card_name" required value="{{ old('card_name') }}" class="inp" placeholder="John Doe"/>
                        @error('card_name') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="label">Credit Card Number <span class="text-rose-600">*</span></label>
                        <input type="text" name="card_num" required value="{{ old('card_num') }}" class="inp" placeholder="4111 2222 3333 4444"/>
                        @error('card_num') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="label">Expiry Date <span class="text-rose-600">*</span></label>
                            <input type="text" name="card_expiry" required value="{{ old('card_expiry') }}" class="inp" placeholder="MM/YY"/>
                            @error('card_expiry') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="label">CVV Code <span class="text-rose-600">*</span></label>
                            <input type="password" name="card_cvv" required value="{{ old('card_cvv') }}" class="inp" placeholder="123"/>
                            @error('card_cvv') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Place Order Button -->
                <button type="submit" class="btn btn-primary w-full py-4 mt-2" style="height: 50px;">
                    <i class="fa-solid fa-lock"></i> Authorize &amp; Place Order
                </button>
            </div>
        </form>

        <!-- Order Summary & Items Checklist (Right) -->
        <div class="bg-white border border-[#e8e4df] rounded-2xl p-5 md:p-6">
            <h2 class="font-display font-bold text-base mb-4 border-b border-slate-100 pb-2">Summary</h2>
            
            <!-- Items list -->
            <div class="flex flex-col mb-5">
                @foreach($cart as $item)
                    <div class="flex items-center gap-3 py-3 border-b border-[#e8e4df] last:border-b-0">
                        <div class="w-[50px] h-[66px] rounded-lg overflow-hidden bg-[#f0ece7] shrink-0 border border-[#e8e4df]">
                            <img src="{{ $item['img'] }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover"/>
                        </div>
                        <div class="flex-1 min-w-0 text-xs">
                            <p class="font-semibold text-slate-800 truncate">{{ $item['name'] }}</p>
                            <p class="text-slate-400 mt-0.5">Qty: {{ $item['quantity'] }} @if($item['size']) · Sz: {{ $item['size'] }} @endif</p>
                        </div>
                        <span class="font-semibold text-xs text-slate-900 ml-2">₹{{ number_format($item['price'] * $item['quantity'], 2) }}</span>
                    </div>
                @endforeach
            </div>

            <div class="flex flex-col gap-2.5 text-xs border-t border-slate-100 pt-4">
                <div class="flex justify-between text-slate-500"><span>Bag Subtotal</span><span>₹{{ number_format($subtotal, 2) }}</span></div>
                @if($discount > 0)
                    <div class="flex justify-between text-emerald-600 font-semibold"><span>Promo Discount</span><span>-₹{{ number_format($discount, 2) }}</span></div>
                @endif
                <div class="flex justify-between text-slate-500"><span>Tax (8%)</span><span>₹{{ number_format($tax, 2) }}</span></div>
                <div class="flex justify-between text-slate-500"><span>Shipping</span><span class="text-emerald-600 font-semibold">Free</span></div>
                <div class="flex justify-between font-bold text-slate-900 text-sm border-t border-slate-100 pt-3 mt-1">
                    <span>Order Total</span><span>₹{{ number_format($total, 2) }}</span>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
