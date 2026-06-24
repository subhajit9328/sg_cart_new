@extends('layouts.store')

@section('title', 'My Account — sgcart')

@section('content')


<div class="storefront-container">
    
    <!-- Account Wrap -->
    <div class="grid grid-cols-1 lg:grid-cols-[280px_1fr] gap-8 items-start">
        
        <!-- Tab Selectors (Left Sidebar Card) -->
        <div class="bg-white border border-[#e8e4df] rounded-2xl p-5 md:p-6">
            <!-- User Info Summary Header -->
            <div class="flex items-center gap-4 mb-6 pb-6 border-b border-[#e8e4df]">
                <div class="w-14 h-14 bg-slate-950 rounded-full flex items-center justify-center font-display text-xl font-extrabold text-white shadow-md">
                    {{ strtoupper(substr(auth('customer')->user()?->name ?? 'John Doe', 0, 2)) }}
                </div>
                <div class="min-w-0">
                    <h3 class="font-display font-extrabold text-sm text-slate-800 truncate">{{ auth('customer')->user()?->name ?? 'John Doe' }}</h3>
                    <p class="text-xs text-slate-400 truncate mt-0.5">{{ auth('customer')->user()?->email ?? 'john.doe@example.com' }}</p>
                </div>
            </div>

            <nav class="flex flex-col gap-1.5">
                <a href="{{ route('store.account', 'orders') }}" class="acc-nav-item px-4 py-3 rounded-lg text-sm font-semibold text-slate-500 hover:bg-[#f8f7f5] hover:text-slate-900 transition-all w-full text-left flex items-center gap-3 {{ $activeTab === 'orders' ? 'active' : '' }}" style="text-decoration:none" id="btn-orders">
                    <i class="fa-solid fa-box-open text-center w-4 text-sm"></i> My Orders
                </a>
                <a href="{{ route('store.account', 'profile') }}" class="acc-nav-item px-4 py-3 rounded-lg text-sm font-semibold text-slate-500 hover:bg-[#f8f7f5] hover:text-slate-900 transition-all w-full text-left flex items-center gap-3 {{ $activeTab === 'profile' ? 'active' : '' }}" style="text-decoration:none" id="btn-profile">
                    <i class="fa-regular fa-user text-center w-4 text-sm"></i> Profile Details
                </a>
                <a href="{{ route('store.account', 'address') }}" class="acc-nav-item px-4 py-3 rounded-lg text-sm font-semibold text-slate-500 hover:bg-[#f8f7f5] hover:text-slate-900 transition-all w-full text-left flex items-center gap-3 {{ $activeTab === 'address' ? 'active' : '' }}" style="text-decoration:none" id="btn-address">
                    <i class="fa-solid fa-map-location-dot text-center w-4 text-sm"></i> Addresses
                </a>
                <a href="{{ route('store.account', 'wishlist') }}" class="acc-nav-item px-4 py-3 rounded-lg text-sm font-semibold text-slate-500 hover:bg-[#f8f7f5] hover:text-slate-900 transition-all w-full text-left flex items-center gap-3 {{ $activeTab === 'wishlist' ? 'active' : '' }}" style="text-decoration:none" id="btn-wishlist">
                    <i class="fa-regular fa-heart text-center w-4 text-sm"></i> Wishlist
                </a>
                <form action="{{ route('store.logout') }}" method="POST" id="storeLogoutForm" class="contents">
                    @csrf
                    <button type="button" onclick="showConfirm('Are you sure you want to log out?', () => document.getElementById('storeLogoutForm').submit());" class="px-4 py-3 rounded-lg text-sm font-semibold text-rose-500 hover:bg-rose-50/50 hover:text-rose-600 transition-all w-full text-left border-none bg-transparent flex items-center gap-3 cursor-pointer">
                        <i class="fa-solid fa-right-from-bracket text-center w-4 text-sm"></i> Logout
                    </button>
                </form>
            </nav>
        </div>

        <!-- Tab Content Box (Right Card) -->
        <div class="bg-white border border-[#e8e4df] rounded-2xl p-5 md:p-6">
            
            <!-- Orders List Tab -->
            <div id="tab-orders" class="acc-content {{ $activeTab === 'orders' ? 'active' : '' }}">
                <h2 class="font-display font-bold text-base text-slate-900 mb-4 border-b border-slate-100 pb-2 flex items-center gap-2.5"><i class="fa-solid fa-clock-rotate-left text-accent text-sm"></i> Order History</h2>
                
                <div class="flex flex-col gap-4">
                    @forelse($orders as $order)
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between border border-[#e8e4df] rounded-xl p-5 bg-white transition-all hover:shadow-[0_4px_15px_rgba(0,0,0,0.03)] gap-4">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-[#f8f7f5] rounded-xl flex items-center justify-center shrink-0 border border-[#e8e4df]">
                                    @if($order['status'] === 'Delivered')
                                        <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                                    @else
                                        <i class="fa-solid fa-truck-fast text-amber-600 text-lg"></i>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-800">{{ $order['id'] }}</p>
                                    <p class="text-xs text-slate-400 mt-1">{{ $order['items_count'] }} items · Purchased on {{ $order['date'] }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-5 w-full sm:w-auto justify-between sm:justify-end">
                                <div class="text-left sm:text-right">
                                    <p class="text-sm font-extrabold text-slate-900">₹{{ number_format($order['amount'], 2) }}</p>
                                    @if($order['status'] === 'Delivered')
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200 mt-1">Delivered</span>
                                    @else
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider bg-amber-50 text-amber-700 border border-amber-200 mt-1">In Transit</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('store.account.order.view', $order['ulid']) }}" class="w-9 h-9 rounded-lg border border-slate-200 hover:bg-slate-50 flex items-center justify-center transition-colors text-slate-400 hover:text-slate-800" title="View Order Details">
                                        <i class="fa-solid fa-eye text-sm"></i>
                                    </a>
                                    <a href="{{ route('store.account.order.invoice', $order['ulid']) }}" class="w-9 h-9 rounded-lg border border-slate-200 hover:bg-slate-50 flex items-center justify-center transition-colors text-slate-400 hover:text-slate-800" title="Download Invoice">
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
            </div>

            <!-- Profile Details Tab -->
            <div id="tab-profile" class="acc-content {{ $activeTab === 'profile' ? 'active' : '' }}">
                <h2 class="font-display font-bold text-base text-slate-900 mb-4 border-b border-slate-100 pb-2 flex items-center gap-2.5"><i class="fa-regular fa-user text-accent text-sm"></i> Profile Details</h2>
                
                <form action="{{ route('store.account.profile.update') }}" method="POST" class="w-full flex flex-col gap-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1">
                            <label class="font-sans text-[11px] font-bold text-slate-500 uppercase tracking-wider">First Name <span class="text-rose-600">*</span></label>
                            <input name="first_name" required class="w-full px-3.5 py-2.5 border border-[#e8e4df] rounded-lg text-sm text-slate-900 bg-white outline-none transition-all shadow-[inset_0_1px_2px_rgba(0,0,0,0.01)] focus:border-slate-900 focus:ring-3 focus:ring-slate-900/5" value="{{ auth('customer')->user() ? explode(' ', auth('customer')->user()->name)[0] : 'John' }}"/>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="font-sans text-[11px] font-bold text-slate-500 uppercase tracking-wider">Last Name <span class="text-rose-600">*</span></label>
                            <input name="last_name" required class="w-full px-3.5 py-2.5 border border-[#e8e4df] rounded-lg text-sm text-slate-900 bg-white outline-none transition-all shadow-[inset_0_1px_2px_rgba(0,0,0,0.01)] focus:border-slate-900 focus:ring-3 focus:ring-slate-900/5" value="{{ auth('customer')->user() ? (explode(' ', auth('customer')->user()->name)[1] ?? '') : 'Doe' }}"/>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1">
                            <label class="font-sans text-[11px] font-bold text-slate-500 uppercase tracking-wider">Email Address</label>
                            <input class="w-full px-3.5 py-2.5 border border-[#e8e4df] rounded-lg text-sm text-slate-900 outline-none transition-all shadow-[inset_0_1px_2px_rgba(0,0,0,0.01)] focus:border-slate-900 focus:ring-3 focus:ring-slate-900/5 disabled:bg-[#f8f7f5] disabled:text-slate-400 disabled:cursor-not-allowed" value="{{ auth('customer')->user()?->email ?? 'john.doe@example.com' }}" readonly disabled/>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="font-sans text-[11px] font-bold text-slate-500 uppercase tracking-wider">Mobile Number</label>
                            <input name="mobile" class="w-full px-3.5 py-2.5 border border-[#e8e4df] rounded-lg text-sm text-slate-900 bg-white outline-none transition-all shadow-[inset_0_1px_2px_rgba(0,0,0,0.01)] focus:border-slate-900 focus:ring-3 focus:ring-slate-900/5" placeholder="+1 555 0199" value="+1 (555) 382-0199"/>
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
                        <div class="border border-[#e8e4df] rounded-xl p-5 relative transition-all hover:border-slate-400 {{ $addr->is_default ? 'border-slate-900 bg-slate-50/10' : '' }}">
                            @if($addr->is_default)
                                <span class="absolute -top-px right-4 bg-slate-950 text-white text-[9px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-b-lg">Default Shipping</span>
                            @endif
                            <div class="text-sm font-bold mb-1.5 flex items-center gap-1.5"><i class="fa-regular fa-address-book text-slate-400"></i> {{ $addr->first_name }} {{ $addr->last_name }}</div>
                            <div class="text-xs text-slate-500 leading-relaxed">{{ $addr->address }}<br/>{{ $addr->city }}, {{ $addr->state }} {{ $addr->zip }}<br/>{{ $addr->country }}</div>
                            <div class="flex gap-2.5 mt-3 items-center justify-end">
                                <span class="text-[10px] font-bold text-accent cursor-pointer hover:text-slate-900 transition-colors select-none" onclick="openEditAddressModal({{ json_encode($addr) }})"><i class="fa-regular fa-pen-to-square mr-1"></i>Edit</span>
                                <span class="text-slate-200 select-none">•</span>
                                <a href="{{ route('store.account.address.delete', $addr->id) }}" class="text-[10px] font-bold text-rose-500 hover:text-rose-700" style="text-decoration:none" onclick="return confirm('Are you sure you want to delete this address?')"><i class="fa-regular fa-trash-can mr-1"></i>Delete</a>
                            </div>
                        </div>
                    @endforeach
                    
                    <!-- Add Address Button Card -->
                    <div class="border-2 border-dashed border-[#e8e4df] hover:border-slate-400 hover:bg-slate-50/50 rounded-xl p-5 flex flex-col items-center justify-center cursor-pointer gap-2 min-h-[150px] transition-all {{ $addresses->isEmpty() ? 'col-span-3' : '' }}" onclick="openAddressModal()">
                        <i class="fa-solid fa-plus text-2xl text-slate-300"></i>
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Add New Address</span>
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
                
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-5">
                    @forelse($wishlist as $wl)
                        <div class="product-card" onclick="window.location.href='{{ route('store.product', $wl['slug']) }}'">
                            <div class="product-card-img">
                                <img src="{{ $wl['img'] }}" alt="{{ $wl['name'] }}"/>
                                <div class="product-card-overlay">
                                    <form action="{{ route('store.wishlist.toggle') }}" method="POST" class="w-full">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $wl['id'] }}"/>
                                        <button type="submit" class="btn btn-outline btn-sm w-full bg-white hover:bg-black" style="padding:10px 0" onclick="event.stopPropagation()"><i class="fa-solid fa-heart-crack"></i> Remove</button>
                                    </form>
                                </div>
                            </div>
                            <div class="product-card-body" style="padding:10px">
                                <h3 class="font-display font-bold text-xs text-slate-800 line-clamp-1">{{ $wl['name'] }}</h3>
                                <p class="font-bold text-xs text-slate-950 mt-1">₹{{ number_format($wl['price'], 2) }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-2 sm:col-span-3 py-12 text-center text-slate-400">
                            <i class="fa-regular fa-heart text-4xl mb-3 opacity-20 block"></i>
                            <p class="text-sm">Your wishlist is empty.</p>
                            <a href="{{ route('store.shop') }}" class="btn btn-primary btn-sm mt-4">Discover Products</a>
                        </div>
                    @endforelse
                </div>
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
        form.querySelector('input[name="first_name"]').value = address.first_name;
        form.querySelector('input[name="last_name"]').value = address.last_name;
        form.querySelector('input[name="address"]').value = address.address;
        form.querySelector('input[name="city"]').value = address.city;
        form.querySelector('input[name="state"]').value = address.state || '';
        form.querySelector('input[name="zip"]').value = address.zip;
        form.querySelector('input[name="country"]').value = address.country || '';

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
        }, 300);
    }

    // Close modals when clicking on the backdrop
    document.addEventListener('DOMContentLoaded', function() {
        const addressModal = document.getElementById('addressModal');
        if (addressModal) {
            addressModal.addEventListener('click', function(e) {
                if (e.target === addressModal) {
                    closeAddressModal();
                }
            });
        }
    });
</script>
@endsection
