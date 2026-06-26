@extends('layouts.store')

@section('title', 'Checkout — sgcart')

@section('content')
<div class="storefront-container">
    <!-- Back to Cart Link -->
    <div class="mb-5">
        <a href="{{ route('store.cart') }}" class="group text-xs font-bold text-slate-500 hover:text-slate-900 transition-colors uppercase tracking-wider flex items-center gap-1.5" style="text-decoration:none">
            <i class="fa-solid fa-arrow-left-long transition-transform group-hover:-translate-x-1"></i> Back to Shopping Cart
        </a>
    </div>

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
                    
                    <!-- Address Cards Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-2.5" id="addressCardsGrid">
                        @foreach($addresses as $addr)
                            <div class="border rounded-xl p-5 relative cursor-pointer transition-all hover:border-slate-400 address-card-item {{ $addr->is_default ? 'border-slate-900 bg-slate-50/10' : 'border-[#e8e4df]' }}" 
                                 onclick="selectAddress(this, {{ $addr->id }})" id="address-card-{{ $addr->id }}">
                                
                                <span class="select-check-icon absolute top-4 right-4 text-accent text-lg {{ $addr->is_default ? 'block' : 'hidden' }}">
                                    <i class="fa-solid fa-circle-check"></i>
                                </span>
                                
                                <div class="text-sm font-bold mb-1.5 flex items-center gap-1.5 card-names">
                                    <i class="fa-regular fa-address-book text-slate-400"></i> {{ $addr->first_name }} {{ $addr->last_name }}
                                </div>
                                <div class="text-xs text-slate-500 leading-relaxed address-details-text">{{ $addr->address }}<br/>{{ $addr->city }}, {{ $addr->state }} {{ $addr->zip }}<br/>{{ $addr->country }}</div>
                                
                                <input type="radio" name="address_id" value="{{ $addr->id }}" class="hidden" {{ $addr->is_default ? 'checked' : '' }}/>

                                <div class="flex gap-2.5 mt-3 items-center justify-end">
                                    <span class="text-[10px] font-bold text-accent cursor-pointer hover:text-slate-900 transition-colors select-none edit-link" onclick="event.stopPropagation(); openCheckoutEditAddressModal({{ json_encode($addr) }})"><i class="fa-regular fa-pen-to-square mr-1"></i>Edit</span>
                                    <span class="text-slate-200 select-none">•</span>
                                    <span class="text-[10px] font-bold text-rose-500 hover:text-rose-700 cursor-pointer select-none delete-link" onclick="event.stopPropagation(); deleteCheckoutAddress({{ $addr->id }})"><i class="fa-regular fa-trash-can mr-1"></i>Delete</span>
                                </div>
                            </div>
                        @endforeach

                        <!-- Add Address Button Card -->
                        <div class="border-2 border-dashed border-[#e8e4df] hover:border-slate-400 hover:bg-slate-50/50 rounded-xl p-5 flex flex-col items-center justify-center cursor-pointer gap-2 min-h-[140px] transition-all {{ $addresses->isEmpty() ? 'col-span-3' : '' }}" onclick="openCheckoutAddressModal()">
                            <i class="fa-solid fa-plus text-xl text-slate-300"></i>
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Add New Address</span>
                        </div>
                    </div>

                    @error('address_id') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                @if($hasShippingPackage)
                <!-- Shipping Method Card -->
                <div class="bg-white border border-[#e8e4df] rounded-2xl p-5 md:p-6">
                    <div class="font-display font-bold text-base mb-4 flex items-center gap-2.5">
                        <i class="fa-solid fa-truck text-accent text-sm"></i> Shipping Method
                    </div>
                    
                    @if(($selectionMode ?? 'user_choice') === 'user_choice')
                        <div class="flex flex-col gap-3">
                            @forelse($shippingRates as $rate)
                                <div class="border rounded-xl p-4 flex items-center justify-between cursor-pointer hover:border-slate-400 transition-all shipping-rate-item {{ $loop->first ? 'border-slate-900 bg-slate-50/10' : 'border-[#e8e4df]' }}" onclick="selectShippingRate(this, {{ $rate->id }}, {{ $rate->calculated_cost }})">
                                    <div class="flex items-center gap-3">
                                        <input type="radio" name="shipping_rate_id" value="{{ $rate->id }}" {{ $loop->first ? 'checked' : '' }} class="text-slate-900 focus:ring-slate-900 border-slate-300">
                                        <div>
                                            <p class="text-sm font-semibold text-slate-800">{{ $rate->name }}@if(($rate->type ?? 'flat') === 'percent') ({{ number_format($rate->cost, 1) }}%) @endif</p>
                                            @if($rate->min_order_amount > 0)
                                                <p class="text-[10px] text-slate-400">Min. order value: ₹{{ number_format($rate->min_order_amount, 2) }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <span class="text-sm font-bold text-slate-900">₹{{ number_format($rate->calculated_cost, 2) }}</span>
                                </div>
                            @empty
                                <div class="text-xs text-slate-400 py-2">
                                    <i class="fa-solid fa-circle-info mr-1 text-slate-400"></i> No shipping charges registered. Standard free shipping is applied.
                                </div>
                            @endforelse
                        </div>
                    @else
                        <p class="text-xs text-slate-500 py-1 flex items-center gap-2">
                            <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
                            <span>Cheapest shipping rate automatically applied based on cart: <strong>{{ $shippingRates->first() ? $shippingRates->first()->name : 'Free Shipping' }}</strong> (₹{{ number_format($shippingCost, 2) }})</span>
                        </p>
                    @endif
                </div>
                @endif

                <!-- Payment Method Card -->
                <div class="bg-white border border-[#e8e4df] rounded-2xl p-5 md:p-6">
                    <div class="font-display font-bold text-base mb-4 flex items-center gap-2.5">
                        <i class="fa-solid fa-credit-card text-accent text-sm"></i> Payment Method
                    </div>
                    
                    @if(app()->bound('payment.manager'))
                        @php
                            $enabledGateways = app('payment.manager')->getEnabledGateways();
                        @endphp
                        
                        <div class="flex flex-col gap-4">
                            <!-- Compact Industry-Standard Options Grid -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                @forelse($enabledGateways as $gateway)
                                    @php
                                        $isFirst = $loop->first;
                                        $activeClass = '';
                                        if ($gateway->getIdentifier() === 'cod') {
                                            $activeClass = 'border-emerald-600 ring-2 ring-emerald-500/10 bg-slate-50/30';
                                        } elseif ($gateway->getIdentifier() === 'razorpay') {
                                            $activeClass = 'border-blue-600 ring-2 ring-blue-500/10 bg-slate-50/30';
                                        } else {
                                            $activeClass = 'border-slate-900 ring-2 ring-slate-800/10 bg-slate-50/30';
                                        }
                                    @endphp
                                    <div class="border border-slate-200 rounded-xl px-4 py-3 flex items-center justify-between cursor-pointer hover:border-slate-350 transition-all payment-method-item h-14 {{ $isFirst ? $activeClass : 'bg-white' }}" 
                                         data-gateway="{{ $gateway->getIdentifier() }}"
                                         data-active-class="@if($gateway->getIdentifier() === 'cod') border-emerald-600 ring-2 ring-emerald-500/10 bg-slate-50/30 @elseif($gateway->getIdentifier() === 'razorpay') border-blue-600 ring-2 ring-blue-500/10 bg-slate-50/30 @else border-slate-900 ring-2 ring-slate-800/10 bg-slate-50/30 @endif"
                                         onclick="selectPaymentGateway(this, '{{ $gateway->getIdentifier() }}')">
                                        
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <input type="radio" name="payment_method" value="{{ $gateway->getIdentifier() }}" {{ $isFirst ? 'checked' : '' }} class="text-slate-900 focus:ring-slate-900 border-slate-300 shrink-0">
                                            <span class="text-xs font-semibold text-slate-700 truncate">
                                                @if($gateway->getIdentifier() === 'cod')
                                                    Cash on Delivery
                                                @elseif($gateway->getIdentifier() === 'razorpay')
                                                    Razorpay
                                                @elseif($gateway->getIdentifier() === 'authorizenet')
                                                    Credit Card
                                                @else
                                                    {{ $gateway->getTitle() }}
                                                @endif
                                            </span>
                                        </div>
                                        
                                        <!-- Brand Graphic on Right -->
                                        <div class="shrink-0 flex items-center">
                                            @if($gateway->getIdentifier() === 'cod')
                                                <svg class="h-4.5 text-slate-450" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect x="1" y="3" width="15" height="13" />
                                                    <polygon points="16 8 20 8 23 11 23 16 16 16 16 8" />
                                                    <circle cx="5.5" cy="18.5" r="2.5" />
                                                    <circle cx="18.5" cy="18.5" r="2.5" />
                                                </svg>
                                            @elseif($gateway->getIdentifier() === 'razorpay')
                                                <svg class="h-4.5 text-[#0c66ff]" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M18 2L4 16h8l-1 6 12-14H15l1-6z" fill="currentColor"/>
                                                </svg>
                                            @elseif($gateway->getIdentifier() === 'authorizenet')
                                                <div class="flex items-center gap-1 opacity-90">
                                                    <!-- Visa -->
                                                    <svg class="h-3.5 w-6 rounded-xs" viewBox="0 0 36 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <rect width="36" height="24" rx="2" fill="#1A1F71"/>
                                                        <path d="M13.2 16.5l1.6-4.9h2l-1.3 4.9h-2.3zm8.3-4.8c-.3-.2-.8-.4-1.4-.4-1.5 0-2.6.8-2.6 1.9 0 1.2 1.1 1.3 1.8 1.7.5.3.7.5.7.8 0 .5-.6.7-1.1.7-.8 0-1.3-.2-1.7-.4l-.3-.1-.3 1.7c.5.2 1.3.4 2.1.4 2 0 3.3-1 3.3-2.5 0-1.7-1.5-1.9-2.1-2.2-.4-.2-.7-.4-.7-.6 0-.3.4-.6 1-.6.5 0 .9.1 1.2.3l.1.1.5-1.8zm4.7-.1h-1.8c-.6 0-.9.3-1.1.8l-3.3 7.6h2.4l.5-1.3h2.9l.3 1.3h2.1l-2-8.4zm-2.4 5.2l1.4-3.8.8 3.8h-2.2zm-12.8-5.2l-2.2 5.9-.2-1.1c-.4-1.3-1.6-2.7-3-3.4l2 7h2.4l3.6-8.4h-2.6z" fill="white"/>
                                                    </svg>
                                                    <!-- Mastercard -->
                                                    <svg class="h-3.5 w-6 rounded-xs" viewBox="0 0 36 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <rect width="36" height="24" rx="2" fill="#222"/>
                                                        <circle cx="14.5" cy="12" r="7" fill="#EB001B"/>
                                                        <circle cx="21.5" cy="12" r="7" fill="#F79E1B" fill-opacity="0.8"/>
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-span-full text-xs text-slate-400 py-2">
                                        <i class="fa-solid fa-circle-info mr-1 text-slate-400"></i> No payment methods enabled. Please contact support.
                                    </div>
                                @endforelse
                            </div>

                            <!-- Single unified fields wrapper below the options -->
                            <div class="mt-2 flex flex-col gap-3">
                                @foreach($enabledGateways as $gateway)
                                    <div class="gateway-fields-wrapper {{ $loop->first ? '' : 'hidden' }}" id="fields-{{ $gateway->getIdentifier() }}">
                                        {!! $gateway->renderPaymentFields() !!}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <!-- Fallback card input -->
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
                    @endif
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
                <div class="flex justify-between text-slate-500"><span>Cart Subtotal</span><span>₹{{ number_format($subtotal, 2) }}</span></div>
                @if($discount > 0)
                    <div class="flex justify-between text-emerald-600 font-semibold"><span>Promo Discount</span><span>-₹{{ number_format($discount, 2) }}</span></div>
                @endif
                @if($taxLabel)
                <div class="flex justify-between text-slate-500"><span>{{ $taxLabel }}</span><span>₹{{ number_format($tax, 2) }}</span></div>
                @endif
                @if($hasShippingPackage)
                <div class="flex justify-between text-slate-500">
                    <span>Shipping</span>
                    <span id="shipping-charge-display" class="{{ $shippingCost > 0 ? 'text-slate-900 font-semibold' : 'text-emerald-600 font-semibold' }}">
                        {{ $shippingCost > 0 ? '₹' . number_format($shippingCost, 2) : 'Free' }}
                    </span>
                </div>
                @endif
                <div class="flex justify-between font-bold text-slate-900 text-sm border-t border-slate-100 pt-3 mt-1">
                    <span>Order Total</span><span id="order-total-display">₹{{ number_format($total, 2) }}</span>
                </div>
            </div>
        </div>

    </div>

    <x-address-modal 
        id="checkoutAddressModal" 
        formId="checkoutAddressForm" 
        onSubmit="saveCheckoutAddress(event)" 
        onClose="closeCheckoutAddressModal()" 
        submitBtnId="saveAddressSubmitBtn" 
    />
</div>
@endsection

@section('scripts')
<script>
    let isEditing = false;
    let editAddressId = null;

    function selectAddress(element, addressId) {
        // Unselect all cards
        document.querySelectorAll('.address-card-item').forEach(card => {
            card.classList.remove('border-slate-900', 'bg-slate-50/10');
            card.classList.add('border-[#e8e4df]');
            const checkIcon = card.querySelector('.select-check-icon');
            if (checkIcon) checkIcon.classList.add('hidden');
            const radio = card.querySelector('input[type="radio"]');
            if (radio) radio.checked = false;
        });

        // Select clicked card
        element.classList.remove('border-[#e8e4df]');
        element.classList.add('border-slate-900', 'bg-slate-50/10');
        const checkIcon = element.querySelector('.select-check-icon');
        if (checkIcon) checkIcon.classList.remove('hidden');
        const radio = element.querySelector('input[type="radio"]');
        if (radio) radio.checked = true;
    }

    function openCheckoutAddressModal() {
        isEditing = false;
        editAddressId = null;
        
        const title = document.getElementById('checkoutAddressModalTitle');
        if (title) title.innerHTML = '<i class="fa-solid fa-map-location-dot text-accent"></i> Add New Address';
        
        const modal = document.getElementById('checkoutAddressModal');
        const content = document.getElementById('checkoutAddressModalContent');
        modal.classList.remove('opacity-0', 'pointer-events-none');
        modal.classList.add('opacity-100');
        content.classList.remove('scale-95');
        content.classList.add('scale-100');
    }

    function openCheckoutEditAddressModal(address) {
        isEditing = true;
        editAddressId = address.id;
        
        const title = document.getElementById('checkoutAddressModalTitle');
        if (title) title.innerHTML = '<i class="fa-solid fa-map-location-dot text-accent"></i> Edit Address';
        
        const form = document.getElementById('checkoutAddressForm');
        // Populate inputs
        form.querySelector('input[name="first_name"]').value = address.first_name;
        form.querySelector('input[name="last_name"]').value = address.last_name;
        form.querySelector('input[name="address"]').value = address.address;
        form.querySelector('input[name="city"]').value = address.city;
        form.querySelector('input[name="state"]').value = address.state || '';
        form.querySelector('input[name="zip"]').value = address.zip;
        form.querySelector('input[name="country"]').value = address.country || '';
        
        const modal = document.getElementById('checkoutAddressModal');
        const content = document.getElementById('checkoutAddressModalContent');
        modal.classList.remove('opacity-0', 'pointer-events-none');
        modal.classList.add('opacity-100');
        content.classList.remove('scale-95');
        content.classList.add('scale-100');
    }

    function closeCheckoutAddressModal() {
        const modal = document.getElementById('checkoutAddressModal');
        const content = document.getElementById('checkoutAddressModalContent');
        modal.classList.remove('opacity-100');
        modal.classList.add('opacity-0', 'pointer-events-none');
        content.classList.remove('scale-100');
        content.classList.add('scale-95');
        
        // Reset after animation
        setTimeout(() => {
            document.getElementById('checkoutAddressForm').reset();
            isEditing = false;
            editAddressId = null;
            const title = document.getElementById('checkoutAddressModalTitle');
            if (title) title.innerHTML = '<i class="fa-solid fa-map-location-dot text-accent"></i> Add New Address';
        }, 300);
    }

    function saveCheckoutAddress(e) {
        e.preventDefault();
        const form = document.getElementById('checkoutAddressForm');
        const submitBtn = document.getElementById('saveAddressSubmitBtn');
        submitBtn.disabled = true;
        submitBtn.innerText = 'Saving...';

        const formData = new FormData(form);
        
        let url = "{{ route('store.account.address.add') }}";
        if (isEditing && editAddressId) {
            url = "{{ route('store.account.address.update', ':id') }}".replace(':id', editAddressId);
        }

        fetch(url, {
            method: "POST",
            body: formData,
            headers: {
                "X-Requested-With": "XMLHttpRequest"
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const addr = data.address;
                const grid = document.getElementById('addressCardsGrid');
                
                if (isEditing) {
                    // Update existing card in UI
                    const card = document.getElementById(`address-card-${addr.id}`);
                    if (card) {
                        card.querySelector('.address-details-text').innerHTML = `${addr.address}<br/>${addr.city}, ${addr.state} ${addr.zip}<br/>${addr.country}`;
                        card.querySelector('.card-names').innerHTML = `<i class="fa-regular fa-address-book text-slate-400"></i> ${addr.first_name} ${addr.last_name}`;
                        
                        const editSpan = card.querySelector('.edit-link');
                        if (editSpan) {
                            const addrJson = JSON.stringify(addr).replace(/"/g, '&quot;');
                            editSpan.setAttribute('onclick', `event.stopPropagation(); openCheckoutEditAddressModal(${addrJson})`);
                        }
                    }
                    showToast('Shipping address updated successfully!', 'success');
                } else {
                    // Create new card element
                    const card = document.createElement('div');
                    card.id = `address-card-${addr.id}`;
                    card.className = "border rounded-xl p-5 relative cursor-pointer transition-all hover:border-slate-400 address-card-item border-slate-900 bg-slate-50/10";
                    card.onclick = function() { selectAddress(this, addr.id); };
                    
                    const addrJson = JSON.stringify(addr).replace(/"/g, '&quot;');
                    card.innerHTML = `
                        <span class="select-check-icon absolute top-4 right-4 text-accent text-lg">
                            <i class="fa-solid fa-circle-check"></i>
                        </span>
                        <div class="text-sm font-bold mb-1.5 flex items-center gap-1.5 card-names">
                            <i class="fa-regular fa-address-book text-slate-400"></i> ${addr.first_name} ${addr.last_name}
                        </div>
                        <div class="text-xs text-slate-500 leading-relaxed address-details-text">${addr.address}<br/>${addr.city}, ${addr.state} ${addr.zip}<br/>${addr.country}</div>
                        <input type="radio" name="address_id" value="${addr.id}" class="hidden" checked />
                        <div class="flex gap-2.5 mt-3 items-center justify-end">
                            <span class="text-[10px] font-bold text-accent cursor-pointer hover:text-slate-900 transition-colors select-none edit-link" onclick="event.stopPropagation(); openCheckoutEditAddressModal(${addrJson})"><i class="fa-regular fa-pen-to-square mr-1"></i>Edit</span>
                            <span class="text-slate-200 select-none">•</span>
                            <span class="text-[10px] font-bold text-rose-500 hover:text-rose-700 cursor-pointer select-none delete-link" onclick="event.stopPropagation(); deleteCheckoutAddress(${addr.id})"><i class="fa-regular fa-trash-can mr-1"></i>Delete</span>
                        </div>
                    `;

                    // Unselect all other cards
                    document.querySelectorAll('.address-card-item').forEach(c => {
                        c.classList.remove('border-slate-900', 'bg-slate-50/10');
                        c.classList.add('border-[#e8e4df]');
                        const checkIcon = c.querySelector('.select-check-icon');
                        if (checkIcon) checkIcon.classList.add('hidden');
                        const radio = c.querySelector('input[type="radio"]');
                        if (radio) radio.checked = false;
                    });

                    // Insert the new card as the first item in the grid
                    grid.insertBefore(card, grid.firstChild);

                    // If grid add address card was full span (because address book was empty), reduce it
                    const addCard = grid.querySelector('.border-2.border-dashed');
                    if (addCard) addCard.classList.remove('col-span-3');
                    
                    showToast('Shipping address added successfully!', 'success');
                }

                // Close modal
                closeCheckoutAddressModal();
            } else {
                showToast(data.message || 'Error saving address. Please try again.', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Something went wrong.', 'error');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerText = 'Save Address';
        });
    }

    function deleteCheckoutAddress(id) {
        showConfirm('Are you sure you want to delete this address?', () => {
            const url = "{{ route('store.account.address.delete', ':id') }}".replace(':id', id);
            fetch(url, {
                method: "GET",
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const card = document.getElementById(`address-card-${id}`);
                    if (card) {
                        const radioBtn = card.querySelector('input[type="radio"]');
                        const wasChecked = radioBtn ? radioBtn.checked : false;
                        card.remove();
                        
                        // Select another card if the deleted one was checked
                        const remainingCards = document.querySelectorAll('.address-card-item');
                        if (wasChecked && remainingCards.length > 0) {
                            const nextCard = remainingCards[0];
                            const nextRadio = nextCard.querySelector('input[type="radio"]');
                            if (nextRadio) {
                                selectAddress(nextCard, nextRadio.value);
                            }
                        }
                        
                        // If no addresses left, make the "Add New Address" card full width
                        if (remainingCards.length === 0) {
                            const grid = document.getElementById('addressCardsGrid');
                            const addCard = grid.querySelector('.border-2.border-dashed');
                            if (addCard) addCard.classList.add('col-span-3');
                        }
                    }
                    showToast('Address deleted successfully!', 'success');
                } else {
                    showToast(data.message || 'Error deleting address.', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                showToast('Something went wrong.', 'error');
            });
        });
    }

    // Shipping Rate selection logic
    function selectShippingRate(element, rateId, cost) {
        document.querySelectorAll('.shipping-rate-item').forEach(item => {
            item.classList.remove('border-slate-900', 'bg-slate-50/10');
            item.classList.add('border-[#e8e4df]');
            const radio = item.querySelector('input[type="radio"]');
            if (radio) radio.checked = false;
        });

        element.classList.remove('border-[#e8e4df]');
        element.classList.add('border-slate-900', 'bg-slate-50/10');
        const radio = element.querySelector('input[type="radio"]');
        if (radio) radio.checked = true;

        updateOrderSummary(cost);
    }

    const baseSubtotal = {{ $subtotal }};
    const baseTax = {{ $tax }};
    const baseDiscount = {{ $discount }};

    function updateOrderSummary(shippingCost) {
        const shippingDisplay = document.getElementById('shipping-charge-display');
        const totalDisplay = document.getElementById('order-total-display');
        
        if (shippingDisplay && totalDisplay) {
            if (shippingCost > 0) {
                shippingDisplay.textContent = '₹' + shippingCost.toFixed(2);
                shippingDisplay.className = 'text-slate-900 font-semibold';
            } else {
                shippingDisplay.textContent = 'Free';
                shippingDisplay.className = 'text-emerald-600 font-semibold';
            }

            const newTotal = Math.max(0, baseSubtotal + baseTax - baseDiscount + shippingCost);
            totalDisplay.textContent = '₹' + newTotal.toFixed(2);
        }
    }

    // Close modal when clicking backdrop
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('checkoutAddressModal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeCheckoutAddressModal();
                }
            });
        }

        // Initialize the active payment gateway inputs on page load
        const checkedRadio = document.querySelector('input[name="payment_method"]:checked');
        if (checkedRadio) {
            const item = checkedRadio.closest('.payment-method-item');
            if (item) {
                selectPaymentGateway(item, checkedRadio.value);
            }
        }
    });

    function selectPaymentGateway(element, gatewayId) {
        // Unselect all payment method items
        document.querySelectorAll('.payment-method-item').forEach(item => {
            const activeClass = item.getAttribute('data-active-class');
            if (activeClass) {
                activeClass.split(' ').forEach(cls => {
                    if (cls.trim()) item.classList.remove(cls);
                });
            }
            item.classList.add('border-[#e8e4df]');
            const radio = item.querySelector('input[type="radio"]');
            if (radio) radio.checked = false;
        });

        // Select clicked payment item
        element.classList.remove('border-[#e8e4df]');
        const activeClass = element.getAttribute('data-active-class');
        if (activeClass) {
            activeClass.split(' ').forEach(cls => {
                if (cls.trim()) element.classList.add(cls);
            });
        }
        const radio = element.querySelector('input[type="radio"]');
        if (radio) radio.checked = true;

        // Hide all fields wrappers and disable inputs
        document.querySelectorAll('.gateway-fields-wrapper').forEach(wrapper => {
            wrapper.classList.add('hidden');
            wrapper.querySelectorAll('input, select, textarea').forEach(input => {
                input.disabled = true;
            });
        });

        // Show active fields wrapper and enable inputs
        const fieldsWrapper = document.getElementById('fields-' + gatewayId);
        if (fieldsWrapper) {
            fieldsWrapper.classList.remove('hidden');
            fieldsWrapper.querySelectorAll('input, select, textarea').forEach(input => {
                input.disabled = false;
            });
        }
    }
</script>
@endsection
