@extends('layouts.store')

@section('title', 'Shopping Cart — sgcart')

@section('content')


<div class="storefront-container">
    <h1 class="font-display font-extrabold text-3xl md:text-4xl tracking-tight mb-8">Shopping Cart</h1>
    
    @if(empty($cart))
        <div class="text-center py-20 px-5">
            <i class="fa-solid fa-cart-shopping text-6xl text-slate-300 mb-5 block"></i>
            <h3 class="font-display font-bold text-2xl mb-2">Your cart is empty</h3>
            <p class="text-sm text-slate-400 mb-8">Looks like you haven't added anything yet.</p>
            <a href="{{ route('store.shop') }}" class="btn btn-primary"><i class="fa-solid fa-arrow-left"></i>Start Shopping</a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-[1fr_360px] gap-7 items-start">
            
            <!-- Items list -->
            <div class="flex flex-col gap-3">
                @foreach($cart as $key => $item)
                    <div class="bg-white border border-[#e8e4df] rounded-2xl p-5 flex flex-col sm:flex-row gap-4 items-start">
                        <div class="w-full sm:w-[90px] h-[200px] sm:h-[110px] rounded-lg overflow-hidden bg-[#f0ece7] shrink-0 border border-[#e8e4df] cursor-pointer" onclick="window.location.href='{{ route('store.product', $item['slug']) }}'">
                            <img src="{{ $item['img'] }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover"/>
                        </div>
                        
                        <div class="flex-1 min-w-0 w-full">
                            <div class="flex items-start justify-between gap-3 mb-1.5">
                                <div>
                                    <div class="text-[10px] font-bold tracking-widest uppercase text-slate-400">{{ $item['cat'] ?? 'Fashion' }}</div>
                                    <h3 class="font-display font-bold text-base mt-0.5 text-slate-900 hover:text-accent transition-colors">
                                        <a href="{{ route('store.product', $item['slug']) }}" style="color:inherit; text-decoration:none">{{ $item['name'] }}</a>
                                    </h3>
                                    @if(!empty($item['size']) || !empty($item['color']) || ($item['stock'] ?? 0) <= 5)
                                        <div class="flex flex-wrap gap-2 items-center mt-1.5 text-xs text-slate-400">
                                            @if(!empty($item['size']))
                                                <span>Size: <strong class="text-slate-700">{{ $item['size'] }}</strong></span>
                                            @endif
                                            @if(!empty($item['size']) && !empty($item['color']))
                                                <span class="text-slate-200">•</span>
                                            @endif
                                            @if(!empty($item['color']))
                                                <div class="flex items-center gap-1.5">
                                                    Color: <span class="w-3 h-3 rounded-full border border-slate-200 inline-block" style="background:{{ $item['color'] }}"></span>
                                                </div>
                                            @endif
                                            @if(($item['stock'] ?? 0) <= 5)
                                                @if(!empty($item['size']) || !empty($item['color']))
                                                    <span class="text-slate-200">•</span>
                                                @endif
                                                <span class="text-amber-600 font-bold">Only {{ $item['stock'] }} left!</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                <button type="button" onclick="confirmRemove('{{ $key }}')" class="text-slate-300 hover:text-rose-500 text-base p-1 shrink-0 transition-colors border-none bg-transparent cursor-pointer" style="text-decoration:none">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                             </div>
                             
                             <div class="flex items-center justify-between mt-4">
                                 <form action="{{ route('store.cart.update') }}" method="POST" id="updateForm-{{ $key }}" class="contents">
                                     @csrf
                                     <div class="flex items-center bg-[#f8f7f5] border border-[#e8e4df] rounded-lg overflow-hidden w-fit">
                                         <button type="button" class="w-9 h-9 text-base text-slate-400 hover:bg-[#e8e4df] hover:text-slate-800 transition-colors border-none background-none cursor-pointer disabled:opacity-30 disabled:cursor-not-allowed" onclick="updateQty('{{ $key }}', -1)" @if($item['quantity'] <= 1) disabled @endif>−</button>
                                         <input type="number" name="quantities[{{ $key }}]" id="qtyInput-{{ $key }}" value="{{ $item['quantity'] }}" min="1" max="{{ $item['stock'] ?? 9999 }}" class="w-10 h-9 text-xs font-semibold text-center bg-transparent border-none outline-none no-spinner p-0" onchange="this.form.submit()"/>
                                         <button type="button" class="w-9 h-9 text-base text-slate-400 hover:bg-[#e8e4df] hover:text-slate-800 transition-colors border-none background-none cursor-pointer disabled:opacity-30 disabled:cursor-not-allowed" onclick="updateQty('{{ $key }}', 1)" @if($item['quantity'] >= ($item['stock'] ?? 0)) disabled @endif>+</button>
                                     </div>
                                 </form>
                                 <span class="font-display font-bold text-base text-slate-900">₹{{ number_format($item['price'] * $item['quantity'], 2) }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- Summary Column -->
            <div class="bg-white border border-[#e8e4df] rounded-2xl p-6 sticky top-[84px] w-full">
                <div class="font-display font-bold text-base mb-5 text-slate-900">Order Summary</div>
                <div class="flex flex-col gap-2.5 mb-4 text-xs text-slate-500">
                    <div class="flex justify-between">
                        <span>Subtotal</span>
                        <span class="text-slate-900 font-semibold">₹{{ number_format($subtotal, 2) }}</span>
                    </div>
                    @if($hasShippingPackage)
                    <div class="flex justify-between">
                        <span>Shipping</span>
                        @if(($selectionMode ?? 'user_choice') === 'user_choice')
                            <span class="text-slate-500 italic">Calculated at checkout</span>
                        @else
                            <span class="{{ $shippingCost > 0 ? 'text-slate-900 font-semibold' : 'text-emerald-600 font-semibold' }}">
                                {{ $shippingCost > 0 ? '₹' . number_format($shippingCost, 2) : 'Free' }}
                            </span>
                        @endif
                    </div>
                    @endif
                    @if($taxLabel)
                    <div class="flex justify-between">
                        <span>{{ $taxLabel }}</span>
                        <span class="text-slate-900 font-semibold">₹{{ number_format($tax, 2) }}</span>
                    </div>
                    @endif
                    @if($discount > 0)
                        <div class="flex justify-between text-emerald-600 font-semibold">
                            <span>Discount</span>
                            <span>-₹{{ number_format($discount, 2) }}</span>
                        </div>
                    @endif
                </div>
                
                @includeIf('coupons::store-form')
                
                <div class="border-t border-slate-100 my-4"></div>
                <div class="flex justify-between items-baseline font-display font-bold text-slate-900 mb-5">
                    <span class="text-sm">Total</span>
                    <span class="text-xl font-extrabold">₹{{ number_format($total, 2) }}</span>
                </div>
                
                <a href="{{ route('store.checkout') }}" onclick="handleCheckoutSubmit(event, this)" class="btn btn-primary w-full py-3.5 flex justify-center gap-2 items-center" id="checkoutBtn">
                    <i class="fa-solid fa-lock" id="checkoutIcon"></i> <span id="checkoutText">Checkout Securely</span>
                </a>
                <a href="{{ route('store.shop') }}" class="block text-center mt-3.5 text-xs font-bold tracking-wider uppercase text-slate-400 hover:text-slate-900 transition-colors" style="text-decoration:none">
                    ← Continue Shopping
                </a>
            </div>
            
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    // Automatically strip any trailing hash fragment (like #374151) from the browser URL on page load
    if (window.location.hash) {
        history.replaceState("", document.title, window.location.pathname + window.location.search);
    }

    function updateQty(key, dir) {
        const inp = document.getElementById(`qtyInput-${key}`);
        let val = parseInt(inp.value) + dir;
        if (val < 1) val = 1;
        inp.value = val;
        document.getElementById(`updateForm-${key}`).submit();
    }

    function confirmRemove(key) {
        const url = "{{ route('store.cart.remove', ':key') }}".replace(':key', encodeURIComponent(key));
        showConfirm('Are you sure you want to remove this item from your shopping cart?', () => {
            window.location.href = url;
        }, 'Remove Item', 'danger');
    }

    let checkoutClicked = false;
    function handleCheckoutSubmit(e, el) {
        if (checkoutClicked) {
            e.preventDefault();
            return false;
        }
        checkoutClicked = true;
        
        el.classList.add('opacity-50', 'cursor-not-allowed');
        el.style.pointerEvents = 'none';
        
        const icon = document.getElementById('checkoutIcon');
        const text = document.getElementById('checkoutText');
        if (icon) {
            icon.className = 'fa-solid fa-spinner animate-spin text-sm';
        }
        if (text) {
            text.innerText = 'Redirecting to Checkout...';
        }
    }
</script>
@endsection
