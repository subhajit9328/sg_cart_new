@extends('layouts.store')

@section('title', 'Shop Catalogue — sgcart')

@section('content')

@php
    $activeFilterCount = 0;
    if (!empty($selectedCategories)) {
        $activeFilterCount += count($selectedCategories);
    }
    if (request()->filled('price_max') && request('price_max') < 10000) {
        $activeFilterCount += 1;
    }
@endphp

<div class="section-inner pt-8 md:pt-12">
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleMobileFilters()"></div>

    <div class="shop-layout">
        
        <!-- SIDEBAR FILTERS -->
        <aside class="sidebar" id="shopSidebar">
            <div class="sidebar-header lg:hidden">
                <h3>Filters</h3>
                <button type="button" class="sidebar-close-btn" onclick="toggleMobileFilters()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form action="{{ route('store.shop') }}" method="GET" id="filterForm">
                <!-- Keep search query if preset -->
                @if($searchQuery)
                    <input type="hidden" name="search" value="{{ $searchQuery }}"/>
                @endif
                
                <!-- Category Filter -->
                <div class="sidebar-section">
                    <h4 class="sidebar-title">Categories</h4>
                    @foreach($allCategories as $cat)
                        <label class="cb-label">
                            <div class="custom-cb">
                                <input type="checkbox" name="category[]" value="{{ $cat }}" 
                                    {{ in_array($cat, $selectedCategories) ? 'checked' : '' }}
                                    onchange="document.getElementById('filterForm').submit()"/>
                                <span class="cb-box"><i class="fa-solid fa-check text-[9px] text-white opacity-0 transition-opacity"></i></span>
                            </div>
                            <span class="cb-text">{{ $cat }}</span>
                            <span class="cb-count">{{ collect(App\Http\Controllers\StoreController::getProducts())->where('cat', $cat)->count() }}</span>
                        </label>
                    @endforeach
                </div>

                <!-- Price Range Filter -->
                <div class="sidebar-section">
                    <h4 class="sidebar-title">Max Price</h4>
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                        <span class="text-xs text-slate-400">₹0</span>
                        <span class="text-xs font-bold" id="priceLabel">₹{{ $selectedPriceMax }}</span>
                    </div>
                    <input type="range" name="price_max" min="100" max="10000" step="100" value="{{ $selectedPriceMax }}"
                        class="w-full price-slider cursor-pointer"
                        onchange="document.getElementById('filterForm').submit()"
                        oninput="document.getElementById('priceLabel').textContent = '₹' + this.value"/>
                </div>


                
                <a href="{{ route('store.shop') }}" class="btn btn-outline btn-sm w-full text-center mt-4 h-10 flex items-center justify-center uppercase tracking-wider font-bold text-[11px]">Reset Filters</a>
            </form>
        </aside>

        <!-- PRODUCTS AREA -->
        <div>
            <!-- FILTER BAR -->
            <div class="filter-bar">
                <div class="flex flex-wrap items-center gap-2 sm:gap-4">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                        Showing <span class="text-slate-800 font-bold">{{ $products->count() }}</span> Products
                    </p>
                    @if($searchQuery)
                        <span class="text-xs bg-slate-100 border border-slate-200 px-2.5 py-1 rounded-lg inline-flex items-center">
                            Search: <strong class="ml-1">"{{ $searchQuery }}"</strong>
                            <a href="{{ route('store.shop', request()->except('search')) }}" class="text-stone hover:text-ink ml-2 transition-colors flex items-center justify-center text-[10px]" title="Clear Search">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        </span>
                    @endif
                </div>

                <div class="filter-actions">
                    <form action="{{ route('store.shop') }}" method="GET" class="search-bar">

                        @foreach($selectedCategories as $cat) <input type="hidden" name="category[]" value="{{ $cat }}"/> @endforeach
                        <input type="hidden" name="price_max" value="{{ $selectedPriceMax }}"/>
                        <input type="text" name="search" placeholder="Search catalogue…" value="{{ $searchQuery }}"/>
                        <button type="submit" class="search-submit-btn" aria-label="Search"><i class="fa-solid fa-magnifying-glass text-xs"></i></button>
                    </form>

                    <div class="filter-controls">
                        <button type="button" class="btn btn-outline btn-sm filter-toggle-btn lg:hidden" onclick="toggleMobileFilters()">
                            <i class="fa-solid fa-sliders"></i> Filters
                            @if($activeFilterCount > 0)
                                <span class="filter-indicator-dot"></span>
                            @endif
                        </button>

                        <!-- Sorting Dropdown -->
                        <select class="filter-select" onchange="applySort(this.value)">
                            <option value="default" {{ $selectedSort == 'default' ? 'selected' : '' }}>Featured</option>
                            <option value="price_asc" {{ $selectedSort == 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="price_desc" {{ $selectedSort == 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- PRODUCTS GRID -->
            <div class="grid-4">
                @forelse($products as $product)
                    <div class="product-card" onclick="window.location.href='{{ route('store.product', $product['id']) }}'">
                        <div class="product-card-img">
                            @if($product['badge'])
                                <span class="product-badge badge-{{ strtolower($product['badge']) }}">{{ $product['badge'] }}</span>
                            @endif
                            <img src="{{ $product['img'] }}" alt="{{ $product['name'] }}"/>
                            <div class="product-card-overlay">
                                <button onclick="event.stopPropagation(); window.location.href='{{ route('store.product', $product['id']) }}'" class="btn btn-primary btn-sm w-full"><i class="fa-solid fa-cart-shopping"></i> Quick Buy</button>
                            </div>
                        </div>
                        <div class="product-card-body">
                            <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">{{ $product['cat'] }}</p>
                            <h3 class="font-display font-bold text-sm mt-1 text-slate-800 line-clamp-1">{{ $product['name'] }}</h3>
                            <div class="flex items-center gap-1.5 mt-2">
                                <span class="font-bold text-sm text-slate-900">₹{{ number_format($product['price'], 2) }}</span>
                                @if($product['old'])
                                    <span class="text-xs text-slate-400 line-through">₹{{ number_format($product['old'], 2) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div style="grid-column: span 3; padding: 80px 20px;" class="text-center text-slate-400">
                        <i class="fa-solid fa-store-slash text-5xl mb-3 opacity-25"></i>
                        <p class="text-sm">No products found matching your search or filters.</p>
                        <a href="{{ route('store.shop') }}" class="btn btn-primary btn-sm mt-4">Clear All Filters</a>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>


    function applySort(val) {
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('sort', val);
        window.location.search = urlParams.toString();
    }

    function toggleMobileFilters() {
        const sidebar = document.getElementById('shopSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        if (sidebar && overlay) {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('open');
        }
    }
</script>
@endsection
