@extends('layouts.store')

@section('title', 'My Account — sgcart')

@section('content')


<div class="storefront-container">

    <!-- Account Wrap -->
    <div class="grid grid-cols-1 lg:grid-cols-[280px_1fr] gap-8 items-start">
        
        <!-- Tab Selectors (Left Sidebar / Top Navigation Card) -->
        <div class="bg-white dark:bg-[#151411] border border-[#e8e4df] dark:border-[#2e2c28] rounded-2xl p-4 lg:p-6">
            <!-- User Info Summary Header -->
            <div class="flex items-center justify-between gap-4 mb-4 pb-4 lg:mb-6 lg:pb-6 border-b border-[#e8e4df] dark:border-[#2e2c28]">
                <div class="flex items-center gap-3 lg:gap-4 min-w-0">
                    <div class="relative w-10 h-10 lg:w-14 lg:h-14 shrink-0">
                        <div id="profile-picture-container" class="w-10 h-10 lg:w-14 lg:h-14 bg-slate-950 dark:bg-accent/20 rounded-full flex items-center justify-center font-display text-base lg:text-xl font-extrabold text-white dark:text-accent shadow-md border dark:border-accent/30 overflow-hidden relative">
                            @if(auth('customer')->user()?->profile_picture)
                                <img id="profile-picture-img" src="{{ Storage::url(auth('customer')->user()->profile_picture) }}" alt="Profile Picture" class="w-full h-full object-cover">
                            @else
                                <span id="profile-picture-initials">{{ strtoupper(substr(auth('customer')->user()?->name ?? 'John Doe', 0, 2)) }}</span>
                            @endif
                            <!-- Loading spinner overlay -->
                            <div id="profile-picture-loader" class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 pointer-events-none transition-opacity duration-200">
                                <i class="fa-solid fa-spinner fa-spin text-white text-base lg:text-lg"></i>
                            </div>
                        </div>
                        <input type="file" id="profile-picture-input" class="hidden" accept="image/*">
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-display font-extrabold text-xs lg:text-sm text-slate-800 dark:text-slate-200 truncate">{{ auth('customer')->user()?->name ?? 'John Doe' }}</h3>
                        <p class="text-[10px] lg:text-xs text-slate-400 dark:text-slate-500 truncate mt-0.5 mb-1.5" title="{{ auth('customer')->user()?->email }}">{{ auth('customer')->user()?->email ?? auth('customer')->user()?->phone_no }}</p>
                        <div class="flex items-center gap-2">
                            <label for="profile-picture-input" class="text-[10px] font-bold text-accent hover:opacity-80 cursor-pointer transition-opacity" title="Change Photo">Change Photo</label>
                            <span id="profile-picture-divider" class="text-slate-300 dark:text-slate-700 text-[10px] {{ auth('customer')->user()?->profile_picture ? '' : 'hidden' }}">|</span>
                            <button type="button" id="profile-picture-delete-btn" class="text-[10px] font-bold text-rose-500 hover:opacity-80 transition-opacity bg-transparent border-none p-0 cursor-pointer {{ auth('customer')->user()?->profile_picture ? '' : 'hidden' }}" title="Remove Photo">Remove Photo</button>
                        </div>
                    </div>
                </div>

                <!-- Mobile Logout Button -->
                <div class="lg:hidden">
                    <form action="{{ route('store.logout') }}" method="POST" id="storeLogoutFormMobile" class="contents">
                        @csrf
                        <button type="button" onclick="showConfirm('Are you sure you want to log out?', () => document.getElementById('storeLogoutFormMobile').submit());" class="px-3 py-2 rounded-lg text-xs font-semibold text-rose-500 hover:bg-rose-50/50 hover:text-rose-600 transition-all border-none bg-transparent flex items-center gap-1.5 cursor-pointer">
                            <i class="fa-solid fa-right-from-bracket text-sm"></i> Logout
                        </button>
                    </form>
                </div>
            </div>

            <!-- Tab Links Navigation -->
            <nav class="flex flex-row overflow-x-auto whitespace-nowrap gap-2 pb-1 scrollbar-none w-full lg:flex-col lg:gap-1.5 lg:overflow-x-visible lg:pb-0">
                <a href="{{ route('store.account', 'orders') }}" class="acc-nav-item px-3.5 py-2 lg:px-4 lg:py-3 rounded-lg text-xs lg:text-sm font-semibold text-slate-500 hover:bg-[#f8f7f5] hover:text-slate-900 transition-all flex items-center gap-2 lg:gap-3 shrink-0 {{ $activeTab === 'orders' ? 'active' : '' }}" style="text-decoration:none" id="btn-orders">
                    <i class="fa-solid fa-box-open text-center w-4 text-xs lg:text-sm"></i> My Orders
                </a>
                <a href="{{ route('store.account', 'profile') }}" class="acc-nav-item px-3.5 py-2 lg:px-4 lg:py-3 rounded-lg text-xs lg:text-sm font-semibold text-slate-500 hover:bg-[#f8f7f5] hover:text-slate-900 transition-all flex items-center gap-2 lg:gap-3 shrink-0 {{ $activeTab === 'profile' ? 'active' : '' }}" style="text-decoration:none" id="btn-profile">
                    <i class="fa-regular fa-user text-center w-4 text-xs lg:text-sm"></i> Profile Details
                </a>
                <a href="{{ route('store.account', 'address') }}" class="acc-nav-item px-3.5 py-2 lg:px-4 lg:py-3 rounded-lg text-xs lg:text-sm font-semibold text-slate-500 hover:bg-[#f8f7f5] hover:text-slate-900 transition-all flex items-center gap-2 lg:gap-3 shrink-0 {{ $activeTab === 'address' ? 'active' : '' }}" style="text-decoration:none" id="btn-address">
                    <i class="fa-solid fa-map-location-dot text-center w-4 text-xs lg:text-sm"></i> Addresses
                </a>
                <a href="{{ route('store.account', 'wishlist') }}" class="acc-nav-item px-3.5 py-2 lg:px-4 lg:py-3 rounded-lg text-xs lg:text-sm font-semibold text-slate-500 hover:bg-[#f8f7f5] hover:text-slate-900 transition-all flex items-center gap-2 lg:gap-3 shrink-0 {{ $activeTab === 'wishlist' ? 'active' : '' }}" style="text-decoration:none" id="btn-wishlist">
                    <i class="fa-regular fa-heart text-center w-4 text-xs lg:text-sm"></i> Wishlist
                </a>
                <a href="{{ route('store.account', 'social-share') }}" class="acc-nav-item px-3.5 py-2 lg:px-4 lg:py-3 rounded-lg text-xs lg:text-sm font-semibold text-slate-500 hover:bg-[#f8f7f5] hover:text-slate-900 transition-all flex items-center gap-2 lg:gap-3 shrink-0 {{ $activeTab === 'social-share' ? 'active' : '' }}" style="text-decoration:none" id="btn-social-share">
                    <i class="fa-solid fa-share-nodes text-center w-4 text-xs lg:text-sm"></i> Social Share
                </a>
                <!-- Desktop Logout Button -->
                <div class="hidden lg:block">
                    <form action="{{ route('store.logout') }}" method="POST" id="storeLogoutForm" class="contents">
                        @csrf
                        <button type="button" onclick="showConfirm('Are you sure you want to log out?', () => document.getElementById('storeLogoutForm').submit());" class="px-4 py-3 rounded-lg text-sm font-semibold text-rose-500 hover:bg-rose-50/50 hover:text-rose-600 transition-all w-full text-left border-none bg-transparent flex items-center gap-3 cursor-pointer">
                            <i class="fa-solid fa-right-from-bracket text-center w-4 text-sm"></i> Logout
                        </button>
                    </form>
                </div>
            </nav>
        </div>

        <!-- Tab Content Box (Right Card) -->
        <div class="bg-white border border-[#e8e4df] rounded-2xl p-5 md:p-6">
            
            <!-- Orders List Tab -->
            <div id="tab-orders" class="acc-content {{ $activeTab === 'orders' ? 'active' : '' }}">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5 pb-4 border-b border-slate-100">
                    <h2 class="font-display font-bold text-base text-slate-900 flex items-center gap-2.5 mb-0" style="margin-bottom:0">
                        <i class="fa-solid fa-clock-rotate-left text-accent text-sm"></i> Order History <span class="bg-slate-100 dark:bg-[#1a1916] text-slate-600 dark:text-slate-400 px-2 py-0.5 rounded-full font-sans font-bold border border-slate-200 dark:border-slate-800 text-[11px]">{{ $orders->total() }}</span>
                    </h2>

                    <!-- Search Bar Form -->
                    <form action="{{ route('store.account', 'orders') }}" method="GET" class="flex items-center gap-2 w-full sm:w-auto">
                        <div class="relative w-full sm:w-64">
                            <input type="text" name="order_search" value="{{ request('order_search') }}" placeholder="Search by Order ID..." class="w-full px-3.5 py-2 border border-[#e8e4df] rounded-xl text-xs text-slate-800 bg-white outline-none focus:border-slate-900 focus:ring-3 focus:ring-slate-900/5 transition-all shadow-[inset_0_1px_2px_rgba(0,0,0,0.01)]" style="padding-right: 32px;"/>
                            @if(request('order_search'))
                                <a href="{{ route('store.account', 'orders') }}" class="absolute right-3 text-slate-400 hover:text-slate-600 transition-colors" style="top: 50%; transform: translateY(-50%); text-decoration: none;">
                                    <i class="fa-solid fa-circle-xmark text-xs"></i>
                                </a>
                            @endif
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm px-4" style="height: 33px; font-size: 11px; display: inline-flex; items-center; justify-content: center; border-radius: 10px;">
                            Search
                        </button>
                    </form>
                </div>

                <div class="flex flex-col gap-4">
                    @forelse($orders as $order)
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between border border-[#e8e4df] dark:border-[#2e2c28] rounded-xl p-5 bg-white dark:bg-[#151411] transition-all hover:shadow-[0_4px_15px_rgba(0,0,0,0.03)] gap-4">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-[#f8f7f5] dark:bg-[#1d1b18] rounded-xl flex items-center justify-center shrink-0 border border-[#e8e4df] dark:border-[#2e2c28]">
                                    @if($order['status'] === 'Delivered')
                                        <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                                    @elseif($order['status'] === 'Cancelled')
                                        <i class="fa-solid fa-circle-xmark text-rose-600 text-lg"></i>
                                    @elseif($order['status'] === 'New Order' || $order['status'] === 'Processing')
                                        <i class="fa-solid fa-spinner fa-spin text-amber-600 text-lg"></i>
                                    @elseif($order['status'] === 'Processed')
                                        <i class="fa-solid fa-box text-blue-600 text-lg"></i>
                                    @elseif($order['status'] === 'Out for Delivery')
                                        <i class="fa-solid fa-truck-ramp-box text-purple-650 text-lg"></i>
                                    @else
                                        <i class="fa-solid fa-truck-fast text-indigo-650 text-lg"></i>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ $order['id'] }}</p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">{{ $order['items_count'] }} items · Purchased on {{ $order['date'] }}</p>
                                    @if($order['status'] === 'Cancelled')
                                        <p class="text-[10px] text-rose-600 dark:text-rose-400 font-medium mt-0.5">Order Cancelled</p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-5 w-full sm:w-auto justify-between sm:justify-end">
                                <div class="text-left sm:text-right">
                                    <p class="text-sm font-extrabold text-slate-900 dark:text-slate-100">₹{{ number_format($order['amount'], 2) }}</p>
                                    @if($order['status'] === 'Delivered')
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50 mt-1">Delivered</span>
                                    @elseif($order['status'] === 'Cancelled')
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider bg-rose-50 dark:bg-rose-950/20 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800/50 mt-1">Cancelled</span>
                                    @elseif($order['status'] === 'New Order' || $order['status'] === 'Processing')
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider bg-amber-50 dark:bg-amber-950/20 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/50 mt-1">New Order</span>
                                    @elseif($order['status'] === 'Processed')
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider bg-blue-50 dark:bg-blue-950/20 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-800/50 mt-1">Processed</span>
                                    @elseif($order['status'] === 'Out for Delivery')
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider bg-purple-50 dark:bg-purple-950/20 text-purple-700 dark:text-purple-400 border border-purple-200 dark:border-purple-800/50 mt-1">Out for Delivery</span>
                                    @else
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider bg-indigo-50 dark:bg-indigo-950/20 text-indigo-700 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/50 mt-1">Shipped</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('store.account.order.view', $order['ulid']) }}" class="w-9 h-9 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-[#1d1b18] flex items-center justify-center transition-colors text-slate-400 hover:text-slate-800 dark:hover:text-slate-200" title="View Order Details">
                                        <i class="fa-solid fa-eye text-sm"></i>
                                    </a>
                                    <a href="{{ route('store.account.order.invoice', $order['ulid']) }}" class="w-9 h-9 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-[#1d1b18] flex items-center justify-center transition-colors text-slate-400 hover:text-slate-800 dark:hover:text-slate-200" title="Download Invoice">
                                        <i class="fa-solid fa-download text-sm"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12 text-slate-400">
                            <i class="fa-solid fa-store-slash text-4xl mb-3 opacity-20 block"></i>
                            <p class="text-sm">No orders placed yet.</p>
                        </div>
                    @endforelse
                </div>

                <x-custom_pagination :paginator="$orders" :show-info="true" label="orders" size="sm" class="mt-8 pt-4 border-t border-border" />
            </div>

            <!-- Profile Details Tab -->
            <div id="tab-profile" class="acc-content {{ $activeTab === 'profile' ? 'active' : '' }}">
                <h2 class="font-display font-bold text-base text-slate-900 mb-4 border-b border-slate-100 pb-2 flex items-center gap-2.5"><i class="fa-regular fa-user text-accent text-sm"></i> Profile Details</h2>
                
                <form action="{{ route('store.account.profile.update') }}" method="POST" class="w-full flex flex-col gap-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1">
                            <label class="font-sans text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">First Name <span class="text-rose-600">*</span></label>
                            <input name="first_name" required class="w-full px-3.5 py-2.5 border border-[#e8e4df] dark:border-[#2e2c28] rounded-lg text-sm text-slate-900 dark:text-slate-100 bg-white dark:bg-[#1a1916] outline-none transition-all shadow-[inset_0_1px_2px_rgba(0,0,0,0.01)] focus:border-slate-900 focus:dark:border-accent focus:ring-3 focus:ring-slate-900/5 focus:dark:ring-accent/10" value="{{ auth('customer')->user() ? explode(' ', auth('customer')->user()->name)[0] : 'John' }}"/>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="font-sans text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Last Name <span class="text-rose-600">*</span></label>
                            <input name="last_name" required class="w-full px-3.5 py-2.5 border border-[#e8e4df] dark:border-[#2e2c28] rounded-lg text-sm text-slate-900 dark:text-slate-100 bg-white dark:bg-[#1a1916] outline-none transition-all shadow-[inset_0_1px_2px_rgba(0,0,0,0.01)] focus:border-slate-900 focus:dark:border-accent focus:ring-3 focus:ring-slate-900/5 focus:dark:ring-accent/10" value="{{ auth('customer')->user() ? (explode(' ', auth('customer')->user()->name)[1] ?? '') : 'Doe' }}"/>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1">
                            <label class="font-sans text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Email Address</label>
                            @if(auth('customer')->user()?->email)
                                <input class="w-full px-3.5 py-2.5 border border-[#e8e4df] dark:border-[#2e2c28] rounded-lg text-sm text-slate-900 dark:text-slate-100 outline-none transition-all shadow-[inset_0_1px_2px_rgba(0,0,0,0.01)] focus:border-slate-900 focus:dark:border-accent focus:ring-3 focus:ring-slate-900/5 focus:dark:ring-accent/10 disabled:bg-[#f8f7f5] disabled:dark:bg-[#151411]/50 disabled:text-slate-400 disabled:dark:text-slate-500 disabled:cursor-not-allowed" value="{{ auth('customer')->user()->email }}" readonly disabled/>
                            @else
                                <input name="email" id="email" type="email" class="w-full px-3.5 py-2.5 border border-[#e8e4df] dark:border-[#2e2c28] rounded-lg text-sm text-slate-900 dark:text-slate-100 bg-white dark:bg-[#1a1916] outline-none transition-all shadow-[inset_0_1px_2px_rgba(0,0,0,0.01)] focus:border-slate-900 focus:dark:border-accent focus:ring-3 focus:ring-slate-900/5 focus:dark:ring-accent/10" placeholder="Add email address" value="{{ old('email') }}"/>
                                <p class="error-email text-rose-500 text-xs mt-1 hidden"></p>
                                @error('email') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                            @endif
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="font-sans text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Registered Phone Number</label>
                            @if(auth('customer')->user()?->phone_no)
                                <input class="w-full px-3.5 py-2.5 border border-[#e8e4df] dark:border-[#2e2c28] rounded-lg text-sm text-slate-900 dark:text-slate-100 outline-none transition-all shadow-[inset_0_1px_2px_rgba(0,0,0,0.01)] focus:border-slate-900 focus:dark:border-accent focus:ring-3 focus:ring-slate-900/5 focus:dark:ring-accent/10 disabled:bg-[#f8f7f5] disabled:dark:bg-[#151411]/50 disabled:text-slate-400 disabled:dark:text-slate-500 disabled:cursor-not-allowed" value="{{ auth('customer')->user()->phone_no }}" readonly disabled/>
                            @else
                                <input name="phone_no" id="phone_no" type="text" class="w-full px-3.5 py-2.5 border border-[#e8e4df] dark:border-[#2e2c28] rounded-lg text-sm text-slate-900 dark:text-slate-100 bg-white dark:bg-[#1a1916] outline-none transition-all shadow-[inset_0_1px_2px_rgba(0,0,0,0.01)] focus:border-slate-900 focus:dark:border-accent focus:ring-3 focus:ring-slate-900/5 focus:dark:ring-accent/10" placeholder="Add phone number (e.g. +1234567890)" value="{{ old('phone_no') }}"/>
                                <p class="error-phone text-rose-500 text-xs mt-1 hidden"></p>
                                @error('phone_no') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                            @endif
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="btn btn-primary btn-sm px-6">Save Changes</button>
                    </div>
                </form>
            </div>

            <!-- Addresses Tab -->
            <div id="tab-address" class="acc-content {{ $activeTab === 'address' ? 'active' : '' }}">
                <h2 class="font-display font-bold text-base text-slate-900 mb-4 border-b border-slate-100 pb-2 flex items-center gap-2.5"><i class="fa-solid fa-map-location-dot text-accent text-sm"></i> Manage Addresses</h2>
                
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    @foreach($addresses as $addr)
                        <div class="border border-[#e8e4df] dark:border-[#2e2c28] rounded-xl p-5 relative transition-all hover:border-slate-400 dark:hover:border-slate-600 {{ $addr->is_default ? 'border-slate-900 dark:border-accent bg-slate-50/10 dark:bg-[#c8a97e]/5' : '' }}">
                            @if($addr->is_default)
                                <span class="absolute -top-px right-4 bg-slate-950 dark:bg-accent text-white dark:text-slate-950 text-[9px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-b-lg">Default Shipping</span>
                            @endif
                            <div class="text-sm font-bold mb-1.5 flex items-center gap-1.5"><i class="fa-regular fa-address-book text-slate-400 dark:text-slate-500"></i> {{ $addr->first_name }} {{ $addr->last_name }}</div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">{{ $addr->address }}<br/>{{ $addr->city }}, {{ $addr->state }} {{ $addr->zip }}<br/>{{ $addr->country }}</div>
                            <div class="flex gap-2.5 mt-3 items-center justify-end">
                                <span class="text-[10px] font-bold text-accent cursor-pointer hover:text-slate-900 dark:hover:text-slate-100 transition-colors select-none" onclick="openEditAddressModal({{ json_encode($addr) }})"><i class="fa-regular fa-pen-to-square mr-1"></i>Edit</span>
                                <span class="text-slate-200 dark:text-slate-800 select-none">•</span>
                                <a href="{{ route('store.account.address.delete', $addr->id) }}" class="text-[10px] font-bold text-rose-500 hover:text-rose-700" style="text-decoration:none" onclick="return confirm('Are you sure you want to delete this address?')"><i class="fa-regular fa-trash-can mr-1"></i>Delete</a>
                            </div>
                        </div>
                    @endforeach

                    <!-- Add Address Button Card -->
                    <div class="border-2 border-dashed border-[#e8e4df] dark:border-[#2e2c28] hover:border-slate-400 dark:hover:border-slate-600 hover:bg-slate-50/50 dark:hover:bg-[#1a1916]/50 rounded-xl p-5 flex flex-col items-center justify-center cursor-pointer gap-2 min-h-[150px] transition-all {{ $addresses->isEmpty() ? 'col-span-3' : '' }}" onclick="openAddressModal()">
                        <i class="fa-solid fa-plus text-2xl text-slate-300 dark:text-slate-700"></i>
                        <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Add New Address</span>
                    </div>
                </div>

                <!-- Add Address Modal Markup -->
                <x-address-modal
                    id="addressModal"
                    formId="addressForm"
                    onClose="closeAddressModal()"
                    submitBtnId="saveAddressSubmitBtn"
                    action="{{ route('store.account.address.add') }}"
                />
            </div>

            <!-- Wishlist Tab -->
            <div id="tab-wishlist" class="acc-content {{ $activeTab === 'wishlist' ? 'active' : '' }}">
                <h2 class="font-display font-bold text-base text-slate-900 mb-4 border-b border-slate-100 pb-2 flex items-center gap-2.5"><i class="fa-regular fa-heart text-accent text-sm"></i> My Wishlist</h2>
                
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                    @forelse($wishlist as $wl)
                        <div class="product-card" onclick="window.location.href='{{ route('store.product', $wl['slug']) }}'">
                            <div class="product-card-img">
                                <img src="{{ $wl['img'] }}" alt="{{ $wl['name'] }}"/>
                                <button type="button" class="wishlist-btn active"
                                    data-product-id="{{ $wl['id'] }}"
                                    onclick="event.stopPropagation(); toggleWishlist(this)"
                                    title="Remove from Wishlist">
                                    <i class="fa-solid fa-heart"></i>
                                </button>
                            </div>
                            <div class="product-card-body" style="padding:10px">
                                <h3 class="font-display font-bold text-xs text-slate-800 line-clamp-1">{{ $wl['name'] }}</h3>
                                <p class="font-bold text-xs text-slate-950 mt-1">₹{{ number_format($wl['price'], 2) }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-2 sm:col-span-3 md:col-span-4 py-12 text-center text-slate-400">
                            <i class="fa-regular fa-heart text-4xl mb-3 opacity-20 block"></i>
                            <p class="text-sm">Your wishlist is empty.</p>
                            <a href="{{ route('store.shop') }}" class="btn btn-primary btn-sm mt-4">Discover Products</a>
                        </div>
                    @endforelse
                </div>
            </div>
            
                <!-- Social Share / Influencer Hub Tab -->
            <div id="tab-social-share" class="acc-content {{ $activeTab === 'social-share' ? 'active' : '' }}">
                
                <!-- Instagram-style Influencer Profile Header -->
                <div class="bg-[#fcfbf9] dark:bg-[#191815] border border-[#e8e4df] dark:border-[#2e2c28] rounded-3xl p-6 md:p-8 mb-6 flex flex-col md:flex-row items-center md:items-start gap-6 md:gap-8 relative overflow-hidden">
                    <div class="absolute w-64 h-64 rounded-full bg-rose-500/5 blur-3xl -top-10 -right-10 pointer-events-none"></div>
                    
                    <!-- Avatar with Instagram Story-style border -->
                    <div class="relative shrink-0 select-none">
                        <div class="w-20 h-20 md:w-24 md:h-24 rounded-full p-[3px] bg-gradient-to-tr from-yellow-500 via-rose-500 to-purple-650 dark:from-yellow-400 dark:via-rose-500 dark:to-purple-500 shadow-md">
                            <div class="w-full h-full rounded-full bg-white dark:bg-[#191815] p-[2px]">
                                <div class="w-full h-full rounded-full bg-slate-900 dark:bg-slate-800 flex items-center justify-center font-display text-2xl font-extrabold text-white overflow-hidden relative">
                                    @if(auth('customer')->user()?->profile_picture)
                                        <img src="{{ Storage::url(auth('customer')->user()->profile_picture) }}" alt="Profile Picture" class="w-full h-full object-cover">
                                    @else
                                        <span>{{ strtoupper(substr(auth('customer')->user()?->name ?? 'IP', 0, 2)) }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Info and Stats -->
                    <div class="flex-1 text-center md:text-left min-w-0">
                        <div class="flex flex-col md:flex-row md:items-center gap-3.5 mb-3.5">
                            <h2 class="font-display font-extrabold text-lg md:text-xl text-slate-850 dark:text-slate-100 m-0 leading-tight truncate">
                                {{ auth('customer')->user()?->name ?? 'Style Partner' }}
                            </h2>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[9px] font-extrabold uppercase bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-955/20 dark:text-rose-400 dark:border-rose-900/30 self-center">
                                <i class="fa-solid fa-circle-check text-[8px]"></i> Style Partner
                            </span>
                        </div>

                        <!-- Stats Row (Instagram style, clickable followers/following) -->
                        <div class="flex items-center justify-center md:justify-start gap-8 mb-4 border-y md:border-y-0 py-2.5 md:py-0 border-slate-100 dark:border-slate-800/65">
                            <div class="text-center md:text-left select-none">
                                <span class="block md:inline font-extrabold text-slate-850 dark:text-slate-100 text-sm md:text-base">{{ count($socialPosts) }}</span>
                                <span class="text-xs text-slate-450 dark:text-slate-500 font-semibold tracking-wide">posts</span>
                            </div>
                            <button type="button" onclick="openFollowersModal()" class="text-center md:text-left bg-transparent border-none p-0 cursor-pointer hover:opacity-85 transition-opacity focus:outline-none select-none">
                                <span class="block md:inline font-extrabold text-slate-850 dark:text-slate-100 text-sm md:text-base" id="stat-followers-count">{{ count($followers) }}</span>
                                <span class="text-xs text-slate-450 dark:text-slate-500 font-semibold tracking-wide">followers</span>
                            </button>
                            <button type="button" onclick="openFollowingModal()" class="text-center md:text-left bg-transparent border-none p-0 cursor-pointer hover:opacity-85 transition-opacity focus:outline-none select-none">
                                <span class="block md:inline font-extrabold text-slate-850 dark:text-slate-100 text-sm md:text-base" id="stat-following-count">{{ count($following) }}</span>
                                <span class="text-xs text-slate-450 dark:text-slate-500 font-semibold tracking-wide">following</span>
                            </button>
                        </div>

                        <!-- Bio info -->
                        <div class="text-xs text-slate-500 dark:text-slate-400 font-medium leading-relaxed max-w-lg select-none">
                            <p class="font-bold text-slate-850 dark:text-slate-350">Fashion & lifestyle inspiration hub.</p>
                            <p class="mt-0.5 opacity-90">Sharing my curated looks, reels, and trends. Discover the linked products and shop directly from my gallery!</p>
                        </div>
                    </div>
                </div>

                @php
                    $hasSocialErrors = $errors->has('media') || $errors->has('order_number') || $errors->has('product_sku') || $errors->has('shop_link') || $errors->has('caption');
                @endphp

                <!-- Sub Tab Navigation -->
                <div class="flex gap-2 mb-6 border-b border-slate-100 dark:border-slate-800 pb-3">
                    <button type="button" onclick="switchSocialSubTab('social-posts')" id="subbtn-social-posts" class="social-sub-tab-btn {{ !$hasSocialErrors ? 'active bg-slate-900 text-white dark:bg-accent dark:text-slate-950' : 'bg-[#f8f7f5] dark:bg-[#1a1916] text-slate-655 hover:text-slate-900' }} px-4 py-2 rounded-xl text-xs font-bold transition-all border-none cursor-pointer">
                        <i class="fa-solid fa-images mr-1.5"></i> My Gallery ({{ count($socialPosts) }})
                    </button>
                    <button type="button" onclick="switchSocialSubTab('social-upload')" id="subbtn-social-upload" class="social-sub-tab-btn {{ $hasSocialErrors ? 'active bg-slate-900 text-white dark:bg-accent dark:text-slate-950' : 'bg-[#f8f7f5] dark:bg-[#1a1916] text-slate-655 hover:text-slate-900' }} px-4 py-2 rounded-xl text-xs font-semibold transition-all border-none cursor-pointer">
                        <i class="fa-solid fa-cloud-arrow-up mr-1.5"></i> Share a Look
                    </button>
                </div>

                <!-- Sub Tab 1: My Gallery (Square Grid) -->
                <div id="subtab-social-posts" class="social-sub-content {{ !$hasSocialErrors ? 'active' : 'hidden' }}">
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 md:gap-4">
                        @forelse($socialPosts as $post)
                            <div class="group relative aspect-square rounded-2xl overflow-hidden bg-slate-950 border border-slate-100 dark:border-slate-800 cursor-pointer" onclick="openCustomerPostModal({{ json_encode($post) }}, '{{ $post->product ? addslashes($post->product->name) : '' }}')">
                                @if($post->media_type === 'video')
                                    <video src="{{ Storage::url($post->media_path) }}" class="w-full h-full object-cover"></video>
                                    <div class="absolute inset-0 flex items-center justify-center bg-black/10">
                                        <span class="w-8 h-8 rounded-full bg-white/20 backdrop-blur-sm border border-white/30 flex items-center justify-center text-white text-xs">
                                            <i class="fa-solid fa-play ml-0.5"></i>
                                        </span>
                                    </div>
                                @else
                                    <img src="{{ Storage::url($post->media_path) }}" alt="Look" class="w-full h-full object-cover">
                                @endif
                                
                                <!-- Hover status overlay -->
                                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex flex-col justify-between p-3.5 text-white">
                                    <div class="flex justify-between items-start">
                                        @if($post->status === 'approved')
                                            <span class="px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase bg-emerald-500/95 border border-emerald-400/35 text-white">Approved</span>
                                        @elseif($post->status === 'rejected')
                                            <span class="px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase bg-rose-500/95 border border-rose-400/35 text-white">Rejected</span>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase bg-amber-500/95 border border-amber-400/35 text-white">Pending</span>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-semibold truncate leading-tight mb-0.5">{{ $post->caption ?? 'No caption' }}</p>
                                        @if($post->product)
                                            <p class="text-[9px] text-slate-300 truncate"><i class="fa-solid fa-bag-shopping text-[8px] mr-1"></i>{{ $post->product->name }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full py-16 text-center text-slate-400 dark:text-slate-500">
                                <i class="fa-solid fa-photo-film text-4xl mb-3 opacity-20 block"></i>
                                <p class="text-sm">You haven't submitted any looks yet.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Sub Tab 2: Share a Look Form -->
                <div id="subtab-social-upload" class="social-sub-content {{ $hasSocialErrors ? 'active' : 'hidden' }}">
                    <form action="{{ route('store.social-share.store') }}" method="POST" enctype="multipart/form-data" class="w-full flex flex-col gap-5 max-w-xl">
                        @csrf
                        
                        <!-- Drag-and-drop media input -->
                        <div class="flex flex-col gap-1.5">
                            <label class="font-sans text-[11px] font-bold text-slate-500 dark:text-slate-450 uppercase tracking-wider">Upload Video Reel or Image <span class="text-rose-600">*</span></label>
                            <div class="border-2 border-dashed border-[#e8e4df] dark:border-[#2e2c28] hover:border-slate-455 dark:hover:border-slate-655 rounded-2xl p-6 flex flex-col items-center justify-center cursor-pointer gap-2 transition-all relative min-h-[140px]" onclick="document.getElementById('social_media_input').click()">
                                <i class="fa-solid fa-photo-film text-3xl text-slate-300 dark:text-slate-700"></i>
                                <span class="text-xs font-bold text-slate-500">Choose Image or MP4 Video Reel</span>
                                <span class="text-[10px] text-slate-400">Files up to 100 MB supported</span>
                                <input type="file" name="media" id="social_media_input" required class="hidden" accept="image/*,video/mp4,video/x-m4v,video/*" onchange="previewSocialMedia(this)"/>
                                
                                <!-- Preview container -->
                                <div id="social_media_preview" class="absolute inset-0 bg-white dark:bg-[#151411] rounded-2xl hidden items-center justify-center p-2 border border-slate-350 dark:border-slate-800">
                                    <!-- Injected media -->
                                </div>
                            </div>
                            @error('media') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Order ID OR Product SKU -->
                        <div class="bg-[#f8f7f5]/80 dark:bg-[#1a1916]/40 border border-[#e8e4df] dark:border-[#2e2c28] rounded-2xl p-4 mt-2">
                            <h4 class="text-xs font-extrabold text-slate-800 dark:text-slate-200 mb-1 flex items-center gap-1.5" style="margin-bottom: 4px;"><i class="fa-solid fa-circle-info text-accent"></i> Linking Verification</h4>
                            <p class="text-[10px] text-slate-455 dark:text-slate-500 leading-normal mb-4">To share your style post, you must provide either a valid Order ID/Number (visible in My Orders) or a Product SKU code (visible on the product page).</p>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="flex flex-col gap-1">
                                    <label class="font-sans text-[10px] font-bold text-slate-500 dark:text-slate-455 uppercase tracking-wider">Order ID / Number</label>
                                    <input name="order_number" id="social_order_number" class="w-full px-3 py-2 border border-[#e8e4df] dark:border-[#2e2c28] rounded-lg text-xs text-slate-900 dark:text-slate-100 bg-white dark:bg-[#1a1916] outline-none transition-all focus:border-slate-800 focus:dark:border-accent" placeholder="e.g. ORD-1001" value="{{ old('order_number') }}"/>
                                    @error('order_number') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                
                            </div>
                        </div>

                        <!-- Caption -->
                        <div class="flex flex-col gap-1">
                            <label class="font-sans text-[11px] font-bold text-slate-500 dark:text-slate-455 uppercase tracking-wider">Caption / Style Notes</label>
                            <textarea name="caption" rows="3" class="w-full px-3.5 py-2.5 border border-[#e8e4df] dark:border-[#2e2c28] rounded-lg text-sm text-slate-900 dark:text-slate-100 bg-white dark:bg-[#1a1916] outline-none transition-all shadow-[inset_0_1px_2px_rgba(0,0,0,0.01)] focus:border-slate-900 focus:dark:border-accent" placeholder="Write a short description to inspire your followers...">{{ old('caption') }}</textarea>
                            @error('caption') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Shop Link -->
                        <div class="flex flex-col gap-1">
                            <label class="font-sans text-[11px] font-bold text-slate-500 dark:text-slate-455 uppercase tracking-wider">Shop Link (Optional)</label>
                            <input name="shop_link" type="url" class="w-full px-3.5 py-2.5 border border-[#e8e4df] dark:border-[#2e2c28] rounded-lg text-sm text-slate-900 dark:text-slate-100 bg-white dark:bg-[#1a1916] outline-none transition-all focus:border-slate-900 focus:dark:border-accent" placeholder="e.g. {{ url('/product/relaxed-fit-shirt') }}" value="{{ old('shop_link') }}"/>
                            <span class="text-[10px] text-slate-400">Must be a URL from this website (base URL must match).</span>
                            @error('shop_link') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="pt-2 flex gap-3">
                            <button type="submit" class="btn btn-primary btn-sm px-6">Publish Look</button>
                            <button type="button" onclick="resetSocialUploadForm()" class="btn btn-secondary btn-sm px-6">Reset</button>
                        </div>
                    </form>
                </div>

                <!-- Modals Area -->
                <!-- Followers Modal Overlay -->
                <div id="followersModal" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 opacity-0 pointer-events-none transition-all duration-300">
                    <div id="followersModalContent" class="bg-white dark:bg-[#12110e] border border-slate-200 dark:border-slate-800 rounded-3xl max-w-sm w-full max-h-[60vh] overflow-hidden shadow-2xl flex flex-col transition-all duration-300 scale-95">
                        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                            <h3 class="font-display font-extrabold text-sm text-slate-900 dark:text-white mb-0">Followers</h3>
                            <button onclick="closeFollowersModal()" class="text-slate-400 hover:text-slate-655 bg-transparent border-none p-0 cursor-pointer">
                                <i class="fa-solid fa-xmark text-sm"></i>
                            </button>
                        </div>
                        <div class="p-4 overflow-y-auto flex-1 flex flex-col gap-3.5">
                            @forelse($followers as $f)
                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-slate-200 dark:bg-slate-850 flex items-center justify-center font-bold text-xs text-slate-650 overflow-hidden border border-slate-350 dark:border-slate-700 shrink-0">
                                            @if($f->profile_picture)
                                                <img src="{{ Storage::url($f->profile_picture) }}" alt="Avatar" class="w-full h-full object-cover">
                                            @else
                                                {{ strtoupper(substr($f->name, 0, 2)) }}
                                            @endif
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-xs text-slate-850 dark:text-slate-200 leading-snug">{{ $f->name }}</h4>
                                            <p class="text-[9px] text-slate-400 dark:text-slate-500 mt-0.5">follower</p>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-10 text-slate-400 dark:text-slate-500">
                                    <i class="fa-solid fa-user-group text-3xl mb-2 opacity-20 block"></i>
                                    <p class="text-xs">No followers yet.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Following Modal Overlay -->
                <div id="followingModal" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 opacity-0 pointer-events-none transition-all duration-300">
                    <div id="followingModalContent" class="bg-white dark:bg-[#12110e] border border-slate-200 dark:border-slate-800 rounded-3xl max-w-sm w-full max-h-[60vh] overflow-hidden shadow-2xl flex flex-col transition-all duration-300 scale-95">
                        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                            <h3 class="font-display font-extrabold text-sm text-slate-900 dark:text-white mb-0">Following</h3>
                            <button onclick="closeFollowingModal()" class="text-slate-400 hover:text-slate-655 bg-transparent border-none p-0 cursor-pointer">
                                <i class="fa-solid fa-xmark text-sm"></i>
                            </button>
                        </div>
                        <div class="p-4 overflow-y-auto flex-1 flex flex-col gap-3.5" id="following-list-container">
                            @forelse($following as $f)
                                <div class="flex items-center justify-between gap-3 following-row-{{ $f->id }}">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-slate-200 dark:bg-slate-850 flex items-center justify-center font-bold text-xs text-slate-655 overflow-hidden border border-slate-355 dark:border-slate-700 shrink-0">
                                            @if($f->profile_picture)
                                                <img src="{{ Storage::url($f->profile_picture) }}" alt="Avatar" class="w-full h-full object-cover">
                                            @else
                                                {{ strtoupper(substr($f->name, 0, 2)) }}
                                            @endif
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-xs text-slate-850 dark:text-slate-200 leading-snug">{{ $f->name }}</h4>
                                            <p class="text-[9px] text-slate-400 dark:text-slate-500 mt-0.5">following</p>
                                        </div>
                                    </div>
                                    <button type="button" onclick="unfollowFromModal(this, '{{ $f->id }}')" class="px-3 py-1 border border-slate-200 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-900 text-slate-600 dark:text-slate-305 rounded-xl text-[10px] font-bold uppercase transition-colors cursor-pointer select-none">
                                        Unfollow
                                    </button>
                                </div>
                            @empty
                                <div class="text-center py-10 text-slate-400 dark:text-slate-500" id="following-empty-state">
                                    <i class="fa-solid fa-user-plus text-3xl mb-2 opacity-20 block"></i>
                                    <p class="text-xs">You aren't following anyone yet.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Customer Post Details Modal Overlay -->
                <div id="customerPostModal" class="fixed inset-0 z-50 bg-black/90 flex items-center justify-center p-4 opacity-0 pointer-events-none transition-all duration-300">
                    <div class="relative bg-slate-900 dark:bg-slate-950 rounded-3xl max-w-3xl w-full max-h-[85vh] overflow-hidden shadow-2xl flex flex-col md:flex-row border border-slate-800">
                        <button type="button" onclick="closeCustomerPostModal()" class="absolute right-4 top-4 z-20 w-9 h-9 bg-black/60 hover:bg-black/80 text-white rounded-full flex items-center justify-center border-none cursor-pointer">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                        <div class="w-full md:w-3/5 bg-black flex items-center justify-center aspect-square md:aspect-auto md:h-[70vh]">
                            <div id="custPostModalMedia" class="w-full h-full flex items-center justify-center relative"></div>
                        </div>
                        <div class="w-full md:w-2/5 p-6 flex flex-col justify-between bg-white dark:bg-[#12110e] border-t md:border-t-0 md:border-l border-slate-100 dark:border-slate-800 overflow-y-auto">
                            <div>
                                <div class="pb-4 border-b border-slate-100 dark:border-slate-800">
                                    <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">Status</p>
                                    <span id="custPostModalStatus" class="inline-flex px-3 py-1 rounded-full text-xs font-bold uppercase border"></span>
                                </div>
                                <div class="py-4 border-b border-slate-100 dark:border-slate-800">
                                    <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Caption</p>
                                    <p id="custPostModalCaption" class="text-xs text-slate-750 dark:text-slate-350 leading-relaxed font-medium"></p>
                                </div>
                                <div class="py-4">
                                    <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2.5">Verification Details</p>
                                    <div class="flex flex-col gap-2">
                                        <div class="flex justify-between items-center text-[11px]">
                                            <span class="text-slate-455 dark:text-slate-500 font-semibold">Order Number</span>
                                            <span id="custPostModalOrder" class="font-mono text-slate-800 dark:text-slate-200 font-bold"></span>
                                        </div>
                                        <div class="flex justify-between items-center text-[11px]">
                                            <span class="text-slate-455 dark:text-slate-500 font-semibold">Product SKU</span>
                                            <span id="custPostModalSku" class="font-mono text-slate-800 dark:text-slate-200 font-bold"></span>
                                        </div>
                                        <div id="custPostModalProductRow" class="flex flex-col gap-1 mt-2 p-2.5 border border-slate-100 dark:border-slate-800 rounded-xl bg-slate-50/50 dark:bg-slate-900/10">
                                            <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wider">Linked Product</span>
                                            <span id="custPostModalProductName" class="text-xs font-bold text-slate-800 dark:text-slate-200"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="custPostModalRejectionCard" class="mt-4 p-3 bg-rose-50 dark:bg-rose-955/15 border border-rose-100 dark:border-rose-900/30 rounded-2xl">
                                <span class="text-[10px] font-extrabold uppercase text-rose-600 tracking-wider block mb-1">Moderator Note</span>
                                <p id="custPostModalRejectionReason" class="text-xs text-rose-700 dark:text-rose-455 leading-normal"></p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>         </div>
            </div>
        </div>

    </div>

</div>

@endsection

@section('scripts')
<script>
    // Tab switching is now routed via URL to preserve state.

    function openAddressModal() {
        const modal = document.getElementById('addressModal');
        const content = document.getElementById('addressModalContent');
        modal.classList.remove('opacity-0', 'pointer-events-none');
        modal.classList.add('opacity-100');
        content.classList.remove('scale-95');
        content.classList.add('scale-100');
    }

    function openEditAddressModal(address) {
        const modal = document.getElementById('addressModal');
        const content = document.getElementById('addressModalContent');
        const title = document.getElementById('addressModalTitle');
        const form = document.getElementById('addressForm');

        // Set Edit title
        title.innerHTML = '<i class="fa-solid fa-map-location-dot text-accent"></i> Edit Address';

        // Update Form Action
        form.action = "{{ route('store.account.address.update', ':id') }}".replace(':id', address.id);

        // Populate inputs
        form.querySelector('input[name="first_name"]').value = address.first_name || '';
        form.querySelector('input[name="last_name"]').value = address.last_name || '';
        form.querySelector('input[name="address"]').value = address.address || '';
        form.querySelector('input[name="city"]').value = address.city || '';
        form.querySelector('input[name="state"]').value = address.state || '';
        form.querySelector('input[name="zip"]').value = address.zip || '';
        form.querySelector('input[name="country"]').value = address.country || '';

        // New fields
        form.querySelector('input[name="phone"]').value = address.phone || '';
        form.querySelector('input[name="alternate_phone"]').value = address.alternate_phone || '';
        form.querySelector('input[name="landmark"]').value = address.landmark || '';

        // Address type radio selection
        const addrTypeRadio = form.querySelector(`input[name="address_type"][value="${address.address_type || 'work'}"]`);
        if (addrTypeRadio) addrTypeRadio.checked = true;

        // Same as shipping checkbox & billing details
        const isSame = address.shipping_and_billing_same === undefined ? true : !!address.shipping_and_billing_same;
        const sameCheckbox = document.getElementById('addressModalSameAsShipping');
        if (sameCheckbox) {
            sameCheckbox.checked = isSame;
            sameCheckbox.dispatchEvent(new Event('change'));
        }

        form.querySelector('input[name="billing_first_name"]').value = address.billing_first_name || '';
        form.querySelector('input[name="billing_last_name"]').value = address.billing_last_name || '';
        form.querySelector('input[name="billing_phone"]').value = address.billing_phone || '';
        form.querySelector('input[name="billing_address"]').value = address.billing_address || '';
        form.querySelector('input[name="billing_city"]').value = address.billing_city || '';
        form.querySelector('input[name="billing_state"]').value = address.billing_state || '';
        form.querySelector('input[name="billing_zip"]').value = address.billing_zip || '';
        form.querySelector('input[name="billing_country"]').value = address.billing_country || '';

        // Open modal
        modal.classList.remove('opacity-0', 'pointer-events-none');
        modal.classList.add('opacity-100');
        content.classList.remove('scale-95');
        content.classList.add('scale-100');
    }

    function closeAddressModal() {
        const modal = document.getElementById('addressModal');
        const content = document.getElementById('addressModalContent');
        const title = document.getElementById('addressModalTitle');
        const form = document.getElementById('addressForm');

        modal.classList.remove('opacity-100');
        modal.classList.add('opacity-0', 'pointer-events-none');
        content.classList.remove('scale-100');
        content.classList.add('scale-95');

        // Reset after animation
        setTimeout(() => {
            title.innerHTML = '<i class="fa-solid fa-map-location-dot text-accent"></i> Add New Address';
            form.action = "{{ route('store.account.address.add') }}";
            form.reset();

            // Trigger checkbox change event to reset billing section visibility
            const sameCheckbox = document.getElementById('addressModalSameAsShipping');
            if (sameCheckbox) {
                sameCheckbox.checked = true;
                sameCheckbox.dispatchEvent(new Event('change'));
            }
            // Clear any error styles
            form.querySelectorAll('.error-text').forEach(el => el.remove());
            form.querySelectorAll('.border-rose-500').forEach(el => el.classList.remove('border-rose-500'));
        }, 300);
    }

    // Close modals when clicking on the backdrop and auto-open on validation errors
    document.addEventListener('DOMContentLoaded', function() {
        const addressModal = document.getElementById('addressModal');
        if (addressModal) {
            addressModal.addEventListener('click', function(e) {
                if (e.target === addressModal) {
                    closeAddressModal();
                }
            });
        }

        // Validate profile details form (missing fields)
        const profileForm = document.querySelector('#tab-profile form');
        if (profileForm) {
            profileForm.addEventListener('submit', function(e) {
                const emailInput = document.getElementById('email');
                const phoneInput = document.getElementById('phone_no');
                let isValid = true;

                if (emailInput && emailInput.value.trim()) {
                    const val = emailInput.value.trim();
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    const errEmail = document.querySelector('.error-email');
                    if (!emailRegex.test(val)) {
                        if (errEmail) {
                            errEmail.textContent = 'Please enter a valid email address.';
                            errEmail.classList.remove('hidden');
                        }
                        emailInput.classList.add('border-rose-500');
                        isValid = false;
                    } else {
                        if (errEmail) errEmail.classList.add('hidden');
                        emailInput.classList.remove('border-rose-500');
                    }
                }

                if (phoneInput && phoneInput.value.trim()) {
                    const val = phoneInput.value.trim();
                    const phoneRegex = /^\+\d{7,15}$/;
                    const errPhone = document.querySelector('.error-phone');
                    if (!phoneRegex.test(val)) {
                        if (errPhone) {
                            errPhone.textContent = 'The phone number must include a country code starting with + followed by the number (e.g. +1234567890).';
                            errPhone.classList.remove('hidden');
                        }
                        phoneInput.classList.add('border-rose-500');
                        isValid = false;
                    } else {
                        if (errPhone) errPhone.classList.add('hidden');
                        phoneInput.classList.remove('border-rose-500');
                    }
                }

                if (!isValid) {
                    e.preventDefault();
                }
            });
        }

        // Profile Picture Upload AJAX
        const profilePicInput = document.getElementById('profile-picture-input');
        if (profilePicInput) {
            profilePicInput.addEventListener('change', function(e) {
                if (e.target.files.length === 0) return;

                const file = e.target.files[0];

                // Client-side quick size validation (2 MB)
                if (file.size > 2 * 1024 * 1024) {
                    showToast('The profile picture size must not exceed 2 MB.', 'error');
                    profilePicInput.value = '';
                    return;
                }

                const loader = document.getElementById('profile-picture-loader');
                loader.classList.remove('opacity-0', 'pointer-events-none');
                loader.classList.add('opacity-100');

                const formData = new FormData();
                formData.append('profile_picture', file);

                fetch("{{ route('store.account.profile-picture.update') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                })
                .then(res => {
                    if (!res.ok) {
                        return res.json().then(errData => {
                            throw new Error(errData.message || 'Server error occurred.');
                        });
                    }
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');

                        // Update container content: remove initials/old image and set new image
                        const container = document.getElementById('profile-picture-container');
                        let img = document.getElementById('profile-picture-img');
                        if (!img) {
                            // Remove initials element
                            const initials = document.getElementById('profile-picture-initials');
                            if (initials) initials.remove();

                            img = document.createElement('img');
                            img.id = 'profile-picture-img';
                            img.alt = 'Profile Picture';
                            img.className = 'w-full h-full object-cover';
                            container.insertBefore(img, loader);
                        }
                        img.src = data.url;

                        // Show the delete button and divider
                        const deleteBtn = document.getElementById('profile-picture-delete-btn');
                        const divider = document.getElementById('profile-picture-divider');
                        if (deleteBtn) deleteBtn.classList.remove('hidden');
                        if (divider) divider.classList.remove('hidden');

                        // Update desktop header avatar
                        const btn = document.getElementById('header-account-btn');
                        if (btn) btn.classList.add('has-avatar');
                        const headerAvatarContainer = document.getElementById('header-account-avatar-container');
                        if (headerAvatarContainer) {
                            headerAvatarContainer.innerHTML = `<img src="${data.url}" alt="Profile Picture">`;
                        }
                        
                        // Update mobile navigation avatar
                        const mobileAvatarContainer = document.getElementById('mobile-nav-avatar-container');
                        if (mobileAvatarContainer) {
                            mobileAvatarContainer.innerHTML = `<img src="${data.url}" class="w-10 h-10 rounded-full object-cover border border-accent/20" alt="Profile Picture">`;
                        }
                    } else {
                        showToast(data.message || 'Profile picture upload failed.', 'error');
                    }
                })
                .catch(err => {
                    showToast(err.message || 'Something went wrong.', 'error');
                })
                .finally(() => {
                    loader.classList.remove('opacity-100');
                    loader.classList.add('opacity-0', 'pointer-events-none');
                    profilePicInput.value = ''; // Reset input
                });
            });
        }

        // Profile Picture Delete AJAX
        const deleteBtn = document.getElementById('profile-picture-delete-btn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function() {
                showConfirm('Are you sure you want to remove your profile photo?', () => {
                    const loader = document.getElementById('profile-picture-loader');
                    loader.classList.remove('opacity-0', 'pointer-events-none');
                    loader.classList.add('opacity-100');

                    fetch("{{ route('store.account.profile-picture.destroy') }}", {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => {
                        if (!res.ok) {
                            return res.json().then(errData => {
                                throw new Error(errData.message || 'Server error occurred.');
                            });
                        }
                        return res.json();
                    })
                    .then(data => {
                        if (data.success) {
                            showToast(data.message, 'success');

                            // Remove image element
                            const img = document.getElementById('profile-picture-img');
                            if (img) img.remove();

                            // Add initials back
                            const container = document.getElementById('profile-picture-container');
                            let initials = document.getElementById('profile-picture-initials');
                            if (!initials) {
                                initials = document.createElement('span');
                                initials.id = 'profile-picture-initials';
                                
                                // Fetch the user's name from the header h3 element
                                const userNameEl = container.closest('.flex').querySelector('h3');
                                const userName = userNameEl ? userNameEl.textContent.trim() : 'Test User';
                                
                                // Get initials helper matching PHP strtoupper(substr($name, 0, 2))
                                const getInitials = (name) => {
                                    if (!name) return 'TE';
                                    return name.trim().substring(0, 2).toUpperCase();
                                };
                                initials.textContent = getInitials(userName);
                                container.insertBefore(initials, loader);
                            }

                            // Hide delete button and divider
                            deleteBtn.classList.add('hidden');
                            const divider = document.getElementById('profile-picture-divider');
                            if (divider) divider.classList.add('hidden');

                            // Reset header and mobile navigation avatars
                            const headerAvatarContainer = document.getElementById('header-account-avatar-container');
                            if (headerAvatarContainer) {
                                headerAvatarContainer.innerHTML = `<i class="fa-regular fa-circle-user header-account-icon"></i>`;
                            }
                            const btn = document.getElementById('header-account-btn');
                            if (btn) btn.classList.remove('has-avatar');

                            const mobileAvatarContainer = document.getElementById('mobile-nav-avatar-container');
                            if (mobileAvatarContainer) {
                                mobileAvatarContainer.innerHTML = `
                                    <div class="w-10 h-10 rounded-full bg-accent/10 border border-accent/20 flex items-center justify-center text-accent text-lg">
                                        <i class="fa-regular fa-user"></i>
                                    </div>
                                `;
                            }
                        } else {
                            showToast(data.message || 'Failed to remove profile photo.', 'error');
                        }
                    })
                    .catch(err => {
                        showToast(err.message || 'Something went wrong.', 'error');
                    })
                    .finally(() => {
                        loader.classList.remove('opacity-100');
                        loader.classList.add('opacity-0', 'pointer-events-none');
                    });
                }, 'Remove Photo');
            });
        }

        @if ($errors->any())
        if (typeof openAddressModal === 'function') {
            openAddressModal();
        }
        @endif

        // Redirect to active tab from errors if present
        @if ($errors->has('media') || $errors->has('order_number') || $errors->has('product_sku') || $errors->has('caption'))
            const socialBtn = document.getElementById('btn-social-share');
            if (socialBtn) {
                // Switch window location parameter to social-share or trigger tab manually if required
            }
        @endif
    });

    // Social Share Sub-Tab Switcher
    function switchSocialSubTab(subTabId) {
        document.querySelectorAll('.social-sub-content').forEach(el => {
            el.classList.add('hidden');
            el.classList.remove('active');
        });
        const targetTab = document.getElementById('subtab-' + subTabId);
        if (targetTab) {
            targetTab.classList.remove('hidden');
            targetTab.classList.add('active');
        }

        document.querySelectorAll('.social-sub-tab-btn').forEach(btn => {
            btn.classList.remove('active', 'bg-slate-900', 'text-white', 'dark:bg-accent', 'dark:text-slate-950');
            btn.classList.add('bg-[#f8f7f5]', 'dark:bg-[#1a1916]', 'text-slate-650');
        });
        const activeBtn = document.getElementById('subbtn-' + subTabId);
        if (activeBtn) {
            activeBtn.classList.add('active', 'bg-slate-900', 'text-white', 'dark:bg-accent', 'dark:text-slate-950');
            activeBtn.classList.remove('bg-[#f8f7f5]', 'dark:bg-[#1a1916]', 'text-slate-650');
        }
    }

    // Media Preview
    function previewSocialMedia(input) {
        const preview = document.getElementById('social_media_preview');
        if (!preview) return;
        preview.innerHTML = '';
        
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const reader = new FileReader();
            
            reader.onload = function(e) {
                let el;
                const extension = file.name.split('.').pop().toLowerCase();
                const videoExtensions = ['mp4', 'mov', 'avi', 'm4v', 'webm', '3gp'];
                
                if (file.type.startsWith('video/') || videoExtensions.includes(extension)) {
                    el = document.createElement('video');
                    el.src = e.target.result;
                    el.controls = true;
                    el.autoplay = true;
                    el.muted = true;
                    el.className = 'max-w-full max-h-[140px] rounded-xl';
                    el.style.outline = 'none';
                } else {
                    el = document.createElement('img');
                    el.src = e.target.result;
                    el.className = 'max-w-full max-h-[140px] rounded-xl object-contain';
                }
                
                // Add cancel button overlay
                const cancelBtn = document.createElement('span');
                cancelBtn.innerHTML = '<i class="fa-solid fa-circle-xmark"></i>';
                cancelBtn.className = 'absolute top-2 right-2 text-rose-500 bg-white dark:bg-[#151411] rounded-full hover:scale-110 cursor-pointer text-base shadow z-10';
                cancelBtn.onclick = function(event) {
                    event.stopPropagation();
                    resetSocialUploadForm();
                };
                
                preview.appendChild(el);
                preview.appendChild(cancelBtn);
                preview.className = 'absolute inset-0 bg-white dark:bg-[#151411] rounded-2xl flex items-center justify-center p-2 border border-slate-350 dark:border-slate-800 z-10';
            }
            
            reader.readAsDataURL(file);
        }
    }

    // Reset Upload form
    function resetSocialUploadForm() {
        const input = document.getElementById('social_media_input');
        if (input) input.value = '';
        
        const preview = document.getElementById('social_media_preview');
        if (preview) {
            preview.innerHTML = '';
            preview.className = 'absolute inset-0 bg-white dark:bg-[#151411] rounded-2xl hidden';
        }
        
        const orderNumInput = document.getElementById('social_order_number');
        if (orderNumInput) orderNumInput.value = '';
        
        const skuInput = document.getElementById('social_product_sku');
        if (skuInput) skuInput.value = '';
        
        const capArea = document.querySelector('#tab-social-upload textarea');
        if (capArea) capArea.value = '';

        const shopLinkInput = document.querySelector('input[name="shop_link"]');
        if (shopLinkInput) shopLinkInput.value = '';
    }

    // Follow/Unfollow Helper inside account page
    function toggleFollow(btn, influencerId) {
        if (!btn) return;
        btn.disabled = true;

        fetch("{{ route('store.social-share.follow', ':id') }}".replace(':id', influencerId), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(res => {
            if (!res.ok) throw new Error('Network response not ok');
            return res.json();
        })
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            } else {
                showToast(data.message || 'Follow toggle failed.', 'error');
            }
        })
        .catch(err => {
            showToast('Something went wrong.', 'error');
        })
        .finally(() => {
            btn.disabled = false;
        });
    }

    // Unfollow from the following modal
    function unfollowFromModal(btn, influencerId) {
        if (!btn) return;
        btn.disabled = true;

        fetch("{{ route('store.social-share.follow', ':id') }}".replace(':id', influencerId), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(res => {
            if (!res.ok) throw new Error('Network response not ok');
            return res.json();
        })
        .then(data => {
            if (data.success && !data.is_following) {
                showToast(data.message, 'success');
                
                // Remove row from modal
                const row = document.querySelector(`.following-row-${influencerId}`);
                if (row) {
                    row.remove();
                }
                
                // Update following count stat
                const followingStat = document.getElementById('stat-following-count');
                if (followingStat) {
                    let currentCount = parseInt(followingStat.textContent) || 0;
                    followingStat.textContent = Math.max(0, currentCount - 1);
                }
                
                // Check if list is now empty
                const container = document.getElementById('following-list-container');
                const remainingRows = container.querySelectorAll('[class^="following-row-"]');
                if (remainingRows.length === 0) {
                    container.innerHTML = `
                        <div class="text-center py-10 text-slate-400 dark:text-slate-500" id="following-empty-state">
                            <i class="fa-solid fa-user-plus text-3xl mb-2 opacity-20 block"></i>
                            <p class="text-xs">You aren't following anyone yet.</p>
                        </div>
                    `;
                }
            } else {
                showToast(data.message || 'Action failed.', 'error');
            }
        })
        .catch(err => {
            showToast('Something went wrong.', 'error');
        })
        .finally(() => {
            btn.disabled = false;
        });
    }

    // Modal helpers for followers / following
    function openFollowersModal() {
        const modal = document.getElementById('followersModal');
        const content = document.getElementById('followersModalContent');
        if (!modal || !content) return;
        modal.classList.remove('opacity-0', 'pointer-events-none');
        modal.classList.add('opacity-100');
        content.classList.remove('scale-95');
        content.classList.add('scale-100');
    }

    function closeFollowersModal() {
        const modal = document.getElementById('followersModal');
        const content = document.getElementById('followersModalContent');
        if (!modal || !content) return;
        content.classList.add('scale-95');
        content.classList.remove('scale-100');
        modal.classList.add('opacity-0', 'pointer-events-none');
        modal.classList.remove('opacity-100');
    }

    // Modal helpers for following
    function openFollowingModal() {
        const modal = document.getElementById('followingModal');
        const content = document.getElementById('followingModalContent');
        if (!modal || !content) return;
        modal.classList.remove('opacity-0', 'pointer-events-none');
        modal.classList.add('opacity-100');
        content.classList.remove('scale-95');
        content.classList.add('scale-100');
    }

    function closeFollowingModal() {
        const modal = document.getElementById('followingModal');
        const content = document.getElementById('followingModalContent');
        if (!modal || !content) return;
        content.classList.add('scale-95');
        content.classList.remove('scale-100');
        modal.classList.add('opacity-0', 'pointer-events-none');
        modal.classList.remove('opacity-100');
    }

    // Customer Post modal helpers
    function openCustomerPostModal(post, productName) {
        const modal = document.getElementById('customerPostModal');
        const mediaContainer = document.getElementById('custPostModalMedia');
        const statusSpan = document.getElementById('custPostModalStatus');
        const captionP = document.getElementById('custPostModalCaption');
        const orderSpan = document.getElementById('custPostModalOrder');
        const skuSpan = document.getElementById('custPostModalSku');
        const productCard = document.getElementById('custPostModalProductRow');
        const productNameSpan = document.getElementById('custPostModalProductName');
        const rejectionCard = document.getElementById('custPostModalRejectionCard');
        const rejectionReasonP = document.getElementById('custPostModalRejectionReason');

        // Set media
        const mediaUrl = "{{ Storage::url(':path') }}".replace(':path', post.media_path);
        if (post.media_type === 'video') {
            mediaContainer.innerHTML = `<video src="${mediaUrl}" controls autoplay loop class="max-w-full max-h-[68vh] rounded-2xl" style="outline:none;"></video>`;
        } else {
            mediaContainer.innerHTML = `<img src="${mediaUrl}" alt="Look" class="max-w-full max-h-[68vh] rounded-2xl object-contain" />`;
        }

        // Set Status
        statusSpan.textContent = post.status.toUpperCase();
        if (post.status === 'approved') {
            statusSpan.className = 'inline-flex px-3 py-1 rounded-full text-xs font-bold uppercase bg-emerald-555 text-emerald-700 border-emerald-250 dark:bg-emerald-950/20 dark:text-emerald-400 dark:border-emerald-900/30';
        } else if (post.status === 'rejected') {
            statusSpan.className = 'inline-flex px-3 py-1 rounded-full text-xs font-bold uppercase bg-rose-50 text-rose-700 border-rose-250 dark:bg-rose-955/20 dark:text-rose-400 dark:border-rose-900/30';
        } else {
            statusSpan.className = 'inline-flex px-3 py-1 rounded-full text-xs font-bold uppercase bg-amber-50 text-amber-700 border-amber-250 dark:bg-amber-955/20 dark:text-amber-400 dark:border-amber-900/30';
        }

        // Caption
        captionP.textContent = post.caption || 'No caption provided.';

        // Order & SKU
        orderSpan.textContent = post.order_number || 'N/A';
        skuSpan.textContent = post.product_sku || 'N/A';

        // Product
        if (post.product) {
            productCard.style.display = 'block';
            productNameSpan.textContent = productName || post.product.name;
        } else {
            productCard.style.display = 'none';
        }

        // Rejection card
        if (post.status === 'rejected' && post.rejection_reason) {
            rejectionCard.style.display = 'block';
            rejectionReasonP.textContent = post.rejection_reason;
        } else {
            rejectionCard.style.display = 'none';
        }

        // Show Modal
        modal.classList.remove('opacity-0', 'pointer-events-none');
        modal.classList.add('opacity-100');
    }

    // Close customer post details modal
    function closeCustomerPostModal() {
        const modal = document.getElementById('customerPostModal');
        const mediaContainer = document.getElementById('custPostModalMedia');
        if (mediaContainer) mediaContainer.innerHTML = ''; // stop playback
        if (modal) {
            modal.classList.remove('opacity-100');
            modal.classList.add('opacity-0', 'pointer-events-none');
        }
    }

    // Document events for modal close/backdrop
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeCustomerPostModal();
            closeFollowersModal();
            closeFollowingModal();
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        const custModal = document.getElementById('customerPostModal');
        if (custModal) {
            custModal.addEventListener('click', function(e) {
                if (e.target === this) closeCustomerPostModal();
            });
        }

        const followersModal = document.getElementById('followersModal');
        if (followersModal) {
            followersModal.addEventListener('click', function(e) {
                if (e.target === this) closeFollowersModal();
            });
        }

        const followingModal = document.getElementById('followingModal');
        if (followingModal) {
            followingModal.addEventListener('click', function(e) {
                if (e.target === this) closeFollowingModal();
            });
        }
    });
</script>
@endsection
