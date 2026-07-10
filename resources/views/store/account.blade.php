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
                <a href="{{ route('store.account', 'notifications') }}" class="acc-nav-item px-3.5 py-2 lg:px-4 lg:py-3 rounded-lg text-xs lg:text-sm font-semibold text-slate-500 hover:bg-[#f8f7f5] hover:text-slate-900 transition-all flex items-center gap-2 lg:gap-3 shrink-0 {{ $activeTab === 'notifications' ? 'active' : '' }}" style="text-decoration:none" id="btn-notifications">
                    <i class="fa-regular fa-bell text-center w-4 text-xs lg:text-sm"></i> Notifications
                    @php
                        $unreadNotificationsCount = auth('customer')->user()->unreadNotifications()->count();
                    @endphp
                    @if($unreadNotificationsCount > 0)
                        <span class="ml-auto min-w-[18px] h-[18px] bg-rose-500 text-white rounded-full font-bold text-[9px] flex items-center justify-center px-1.5 py-0.5 leading-none" id="notificationsUnreadBadge">{{ $unreadNotificationsCount }}</span>
                    @endif
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
                            <input name="first_name" id="first_name" required class="w-full px-3.5 py-2.5 border border-[#e8e4df] dark:border-[#2e2c28] rounded-lg text-sm text-slate-900 dark:text-slate-100 bg-white dark:bg-[#1a1916] outline-none transition-all shadow-[inset_0_1px_2px_rgba(0,0,0,0.01)] focus:border-slate-900 focus:dark:border-accent focus:ring-3 focus:ring-slate-900/5 focus:dark:ring-accent/10" value="{{ auth('customer')->user() ? explode(' ', auth('customer')->user()->name)[0] : 'John' }}"/>
                            <p class="error-first-name text-rose-500 text-xs mt-1 hidden"></p>
                            @error('first_name') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="font-sans text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Last Name <span class="text-rose-600">*</span></label>
                            <input name="last_name" id="last_name" required class="w-full px-3.5 py-2.5 border border-[#e8e4df] dark:border-[#2e2c28] rounded-lg text-sm text-slate-900 dark:text-slate-100 bg-white dark:bg-[#1a1916] outline-none transition-all shadow-[inset_0_1px_2px_rgba(0,0,0,0.01)] focus:border-slate-900 focus:dark:border-accent focus:ring-3 focus:ring-slate-900/5 focus:dark:ring-accent/10" value="{{ auth('customer')->user() ? (explode(' ', auth('customer')->user()->name)[1] ?? '') : 'Doe' }}"/>
                            <p class="error-last-name text-rose-500 text-xs mt-1 hidden"></p>
                            @error('last_name') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
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

            <!-- Notifications Tab -->
            <div id="tab-notifications" class="acc-content {{ $activeTab === 'notifications' ? 'active' : '' }}">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5 pb-4 border-b border-slate-100">
                    <h2 class="font-display font-bold text-base text-slate-900 flex items-center gap-2.5 mb-0" style="margin-bottom:0">
                        <i class="fa-regular fa-bell text-accent text-sm"></i> Notifications
                    </h2>
                    @if(auth('customer')->user()->unreadNotifications()->exists())
                        <form action="{{ route('store.account.notifications.read-all') }}" method="POST" id="markAllReadForm" class="inline">
                            @csrf
                            <button type="submit" class="btn btn-secondary btn-sm px-4" style="height: 33px; font-size: 11px; display: inline-flex; items-center; justify-content: center; border-radius: 10px;">
                                Mark all as read
                            </button>
                        </form>
                    @endif
                </div>

                <div class="flex flex-col gap-3">
                    @forelse($notifications as $notif)
                        @php
                            $isUnread = is_null($notif->read_at);
                            $notifData = $notif->data;
                            $notifUrl = $notifData['url'] ?? '#';
                            $notifIcon = $notifData['icon'] ?? 'fa-circle-info';
                            $notifType = $notifData['type'] ?? 'info';

                            $typeClasses = [
                                'success' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/20 dark:text-emerald-450 border-emerald-100 dark:border-emerald-900/30',
                                'danger' => 'bg-rose-50 text-rose-600 dark:bg-rose-955/20 dark:text-rose-455 border-rose-100 dark:border-rose-900/30',
                                'warning' => 'bg-amber-50 text-amber-600 dark:bg-amber-955/20 dark:text-amber-455 border-amber-100 dark:border-amber-900/30',
                                'info' => 'bg-blue-50 text-blue-600 dark:bg-blue-955/20 dark:text-blue-455 border-blue-100 dark:border-blue-900/30',
                            ];
                            $iconClass = $typeClasses[$notifType] ?? $typeClasses['info'];
                        @endphp
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between border border-[#e8e4df] dark:border-[#2e2c28] rounded-xl p-5 bg-white dark:bg-[#151411] transition-all hover:shadow-[0_4px_15px_rgba(0,0,0,0.03)] gap-4" id="notification-{{ $notif->id }}">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 border {{ $isUnread ? 'bg-accent/15 text-accent border-accent/30' : 'bg-[#f8f7f5] dark:bg-[#1d1b18] text-slate-400 dark:text-slate-500 border-[#e8e4df] dark:border-[#2e2c28]' }}">
                                    <i class="fa-solid {{ $notifIcon }} text-lg"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-800 dark:text-slate-200">
                                        @if($notifUrl && $notifUrl !== '#')
                                            <a href="{{ $notifUrl }}" class="hover:text-accent transition-colors" style="text-decoration:none;">{{ $notifData['title'] ?? 'Notification' }}</a>
                                        @else
                                            {{ $notifData['title'] ?? 'Notification' }}
                                        @endif
                                    </p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $notifData['message'] ?? '' }}</p>
                                    <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">{{ $notif->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-5 w-full sm:w-auto justify-between sm:justify-end">
                                <div class="text-left sm:text-right">
                                    @if($isUnread)
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider bg-accent/10 text-accent border border-accent/20">New</span>
                                    @else
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider bg-slate-50 dark:bg-slate-900/50 text-slate-400 dark:text-slate-500 border border-slate-200 dark:border-slate-800">Read</span>
                                    @endif
                                    <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1">{{ $notif->created_at->format('M d, Y · H:i') }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($notifUrl && $notifUrl !== '#')
                                        <a href="{{ $notifUrl }}" class="w-9 h-9 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-[#1d1b18] flex items-center justify-center transition-colors text-slate-400 hover:text-slate-800 dark:hover:text-slate-200" title="View Details">
                                            <i class="fa-solid fa-eye text-sm"></i>
                                        </a>
                                    @endif
                                    @if($isUnread)
                                        <form action="{{ route('store.account.notifications.read', $notif->id) }}" method="POST" class="mark-read-form inline">
                                            @csrf
                                            <button type="submit" class="w-9 h-9 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-[#1d1b18] flex items-center justify-center transition-colors text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 cursor-pointer" title="Mark as read">
                                                <i class="fa-solid fa-check text-sm"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12 text-slate-400">
                            <i class="fa-regular fa-bell text-4xl mb-3 opacity-20 block"></i>
                            <p class="text-sm">You have no notifications.</p>
                        </div>
                    @endforelse
                </div>

                @if($notifications instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <x-custom_pagination :paginator="$notifications" :show-info="true" label="notifications" size="sm" class="mt-8 pt-4 border-t border-border" />
                @endif
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
            const firstNameInput = document.getElementById('first_name');
            const lastNameInput = document.getElementById('last_name');
            const errFirstName = document.querySelector('.error-first-name');
            const errLastName = document.querySelector('.error-last-name');

            function validateFirstName() {
                if (!firstNameInput) return true;
                const val = firstNameInput.value.trim();
                if (!val) {
                    if (errFirstName) {
                        errFirstName.textContent = 'First name is required.';
                        errFirstName.classList.remove('hidden');
                    }
                    firstNameInput.classList.add('border-rose-500');
                    return false;
                } else {
                    if (errFirstName) errFirstName.classList.add('hidden');
                    firstNameInput.classList.remove('border-rose-500');
                    return true;
                }
            }

            function validateLastName() {
                if (!lastNameInput) return true;
                const val = lastNameInput.value.trim();
                if (!val) {
                    if (errLastName) {
                        errLastName.textContent = 'Last name is required.';
                        errLastName.classList.remove('hidden');
                    }
                    lastNameInput.classList.add('border-rose-500');
                    return false;
                } else {
                    if (errLastName) errLastName.classList.add('hidden');
                    lastNameInput.classList.remove('border-rose-500');
                    return true;
                }
            }

            if (firstNameInput) {
                firstNameInput.addEventListener('blur', validateFirstName);
                firstNameInput.addEventListener('input', validateFirstName);
            }

            if (lastNameInput) {
                lastNameInput.addEventListener('blur', validateLastName);
                lastNameInput.addEventListener('input', validateLastName);
            }

            profileForm.addEventListener('submit', function(e) {
                const emailInput = document.getElementById('email');
                const phoneInput = document.getElementById('phone_no');
                let isValid = true;

                const isFirstNameValid = validateFirstName();
                const isLastNameValid = validateLastName();
                if (!isFirstNameValid || !isLastNameValid) {
                    isValid = false;
                }

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

        // Notification Mark-Read Loader
        document.querySelectorAll('.mark-read-form').forEach(form => {
            form.addEventListener('submit', function() {
                const btn = this.querySelector('button[type="submit"]');
                if (btn) {
                    btn.disabled = true;
                    const tick = btn.querySelector('.fa-check');
                    if (tick) tick.classList.add('hidden!');
                }
            });
        });

        @if ($errors->any())
        if (typeof openAddressModal === 'function') {
            openAddressModal();
        }
        @endif
    });
</script>
@endsection
