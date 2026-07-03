@extends('marketplace::layouts.seller')

@section('title', 'Shop Onboarding — Seller Portal')

@section('content')
<div class="w-full">
    <!-- Grid container using full width -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 w-full items-stretch">
        
        <!-- Left Side: Theme-Oriented Info Card -->
        <div class="lg:col-span-4 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl p-8 text-white flex flex-col justify-between shadow-lg relative overflow-hidden min-h-[350px] lg:min-h-full">
            <!-- Background Decorative Circles -->
            <div class="absolute w-64 h-64 rounded-full bg-white/5 -top-16 -left-16 blur-2xl"></div>
            <div class="absolute w-80 h-80 rounded-full bg-white/5 -bottom-20 -right-20 blur-3xl"></div>

            <div class="relative z-10 space-y-6">
                <!-- Brand Badge -->
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold bg-white/10 border border-white/20 backdrop-blur-sm">
                    <i class="fa-solid fa-rocket text-blue-200"></i>
                    Seller Setup
                </div>

                <div>
                    <h2 class="font-display text-2xl lg:text-3xl font-extrabold tracking-tight">Let's build your shop profile</h2>
                    <p class="text-blue-100 text-sm mt-2 leading-relaxed">
                        You're just one step away from launching your business on SGCart. Complete these quick details so we can verify your shop.
                    </p>
                </div>

                <!-- Step Progress List -->
                <div class="space-y-4 pt-4">
                    <!-- Step 1 -->
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center text-emerald-300 text-xs">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-300">Step 1</p>
                            <p class="text-sm font-semibold">Account Created</p>
                        </div>
                    </div>
                    <!-- Step 2 -->
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center text-emerald-300 text-xs">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-300">Step 2</p>
                            <p class="text-sm font-semibold">Contact Verified</p>
                        </div>
                    </div>
                    <!-- Step 3 -->
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-white text-blue-600 flex items-center justify-center text-xs font-bold shadow-md animate-pulse">
                            3
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-blue-200">Step 3</p>
                            <p class="text-sm font-bold text-white">Shop Onboarding</p>
                        </div>
                    </div>
                    <!-- Step 4 -->
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-white/10 border border-white/20 flex items-center justify-center text-white/50 text-xs font-semibold">
                            4
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-blue-300">Step 4</p>
                            <p class="text-sm font-semibold text-white/50">Admin Approval</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Message -->
            <div class="relative z-10 pt-6 border-t border-white/10 text-xs text-blue-100 flex items-center gap-2">
                <i class="fa-solid fa-shield-halved text-blue-300 text-sm"></i>
                Your business details are protected & encrypted.
            </div>
        </div>

        <!-- Right Side: Onboarding Form Card -->
        <div class="lg:col-span-8 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 md:p-8 shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="font-display text-xl font-bold text-slate-900 dark:text-white mb-1">Shop Registration Details</h3>
                <p class="text-slate-500 dark:text-slate-400 text-xs mb-6">Complete the fields below to submit your vendor profile for administrator approval.</p>

                <!-- Validation Errors -->
                @if ($errors->any())
                    <div class="mb-6 p-4 rounded-xl border border-rose-500/30 bg-rose-500/10 text-rose-400 text-xs flex gap-3 items-start">
                        <i class="fa-solid fa-circle-exclamation mt-0.5 text-rose-500"></i>
                        <div class="flex-1">
                            <p class="font-semibold">Please correct the errors below</p>
                            <ul class="list-disc pl-4 mt-1 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <form action="{{ route('seller.onboarding.submit') }}" method="POST" class="space-y-6">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Shop Name -->
                        <div>
                            <label for="shop_name" class="block text-slate-700 dark:text-slate-300 text-xs font-semibold uppercase tracking-wider mb-2">Shop / Business Name</label>
                            <div class="relative flex items-center">
                                <i class="fa-solid fa-store absolute left-4 text-slate-400 dark:text-slate-500 text-base"></i>
                                <input type="text" name="shop_name" id="shop_name" value="{{ old('shop_name', $seller->shop_name) }}" required autofocus
                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-xl py-3 pl-12 pr-4 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-600 outline-none transition-all"
                                    placeholder="e.g. Apex Electronics">
                            </div>
                            <p class="error-shop_name text-rose-500 text-xs mt-1.5 font-medium hidden"></p>
                        </div>

                        <!-- Contact Number -->
                        <div>
                            <label for="phone_no" class="block text-slate-700 dark:text-slate-300 text-xs font-semibold uppercase tracking-wider mb-2">Contact Number</label>
                            <div class="relative flex items-center">
                                <i class="fa-solid fa-phone absolute left-4 text-slate-400 dark:text-slate-500 text-base"></i>
                                <input type="text" name="phone_no" id="phone_no" value="{{ old('phone_no', $seller->phone_no) }}" required
                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-xl py-3 pl-12 pr-4 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-600 outline-none transition-all"
                                    placeholder="e.g. +1234567890">
                            </div>
                            <p class="error-phone_no text-rose-500 text-xs mt-1.5 font-medium hidden"></p>
                        </div>

                        <!-- Shop Description -->
                        <div class="md:col-span-2">
                            <label for="shop_description" class="block text-slate-700 dark:text-slate-300 text-xs font-semibold uppercase tracking-wider mb-2">Shop Description (Optional)</label>
                            <textarea name="shop_description" id="shop_description" rows="3"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-xl py-3 px-4 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-600 outline-none transition-all resize-none font-sans"
                                placeholder="Describe what items you plan to list...">{{ old('shop_description', $seller->shop_description) }}</textarea>
                            <p class="error-shop_description text-rose-500 text-xs mt-1.5 font-medium hidden"></p>
                        </div>

                        <!-- Address -->
                        <div class="md:col-span-2">
                            <label for="address" class="block text-slate-700 dark:text-slate-300 text-xs font-semibold uppercase tracking-wider mb-2">Business Address</label>
                            <textarea name="address" id="address" rows="3" required
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-xl py-3 px-4 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-600 outline-none transition-all resize-none font-sans"
                                placeholder="Enter your physical store or warehouse address...">{{ old('address', $seller->address) }}</textarea>
                            <p class="error-address text-rose-500 text-xs mt-1.5 font-medium hidden"></p>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" 
                        class="w-full bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-semibold py-3 rounded-xl transition-colors text-sm shadow-md shadow-blue-600/10 flex items-center justify-center gap-2 mt-4 hover:shadow-lg transition-all duration-150">
                        Complete Onboarding & Submit Shop
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </button>
                </form>
            </div>
        </div>
        
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const $form = $('form');
        const $shopName = $('#shop_name');
        const $phoneNo = $('#phone_no');
        const $shopDescription = $('#shop_description');
        const $address = $('#address');

        $form.attr('novalidate', 'novalidate');

        function getErrorElement($input) {
            const name = $input.attr('name');
            return $(`.error-${name}`);
        }

        function showError($input, message) {
            const $err = getErrorElement($input);
            $err.text(message).removeClass('hidden');
            $input.addClass('border-rose-500 focus:border-rose-500 focus:ring-rose-500');
            $input.removeClass('border-slate-200 dark:border-slate-800 focus:border-blue-500 focus:ring-blue-500');
        }

        function clearError($input) {
            const $err = getErrorElement($input);
            $err.text('').addClass('hidden');
            $input.removeClass('border-rose-500 focus:border-rose-500 focus:ring-rose-500');
            $input.addClass('border-slate-200 dark:border-slate-800 focus:border-blue-500 focus:ring-blue-500');
        }

        function validateShopName() {
            if (!$shopName.val().trim()) {
                showError($shopName, 'Shop name is required.');
                return false;
            }
            clearError($shopName);
            return true;
        }

        function validatePhoneNo() {
            if (!$phoneNo.val().trim()) {
                showError($phoneNo, 'Contact number is required.');
                return false;
            }
            clearError($phoneNo);
            return true;
        }

        function validateShopDescription() {
            clearError($shopDescription);
            return true;
        }

        function validateAddress() {
            if (!$address.val().trim()) {
                showError($address, 'Address is required.');
                return false;
            }
            clearError($address);
            return true;
        }

        $shopName.on('input blur', validateShopName);
        $phoneNo.on('input blur', validatePhoneNo);
        $shopDescription.on('input blur', validateShopDescription);
        $address.on('input blur', validateAddress);

        $form.on('submit', function(e) {
            const sn = validateShopName();
            const pn = validatePhoneNo();
            const sd = validateShopDescription();
            const ad = validateAddress();

            if (!sn || !pn || !sd || !ad) {
                e.preventDefault();
                e.stopImmediatePropagation();
            }
        });
    });

    // Loader on submit
    document.addEventListener('submit', (e) => {
        if (e.defaultPrevented) return;
        const form = e.target;
        const btn = form.querySelector('button[type="submit"]');
        if (btn) {
            if (!btn.querySelector('.fa-spinner')) {
                const spinner = document.createElement('i');
                spinner.className = 'fa-solid fa-spinner fa-spin mr-2';
                btn.insertBefore(spinner, btn.firstChild);
            }
            btn.disabled = true;
            btn.style.pointerEvents = 'none';
            btn.style.opacity = '0.8';
        }
    });
</script>
@endpush
