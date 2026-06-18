@extends('layouts.store')

@section('title', 'Shop Catalogue — sgcart')

@section('content')


<div class="section-inner pt-8 md:pt-12">
    <div class="shop-layout">
        
        <!-- SIDEBAR FILTERS (Desktop) -->
        <aside class="sidebar">
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
                            <input type="checkbox" name="category[]" value="{{ $cat }}" 
                                {{ in_array($cat, $selectedCategories) ? 'checked' : '' }}
                                onchange="document.getElementById('filterForm').submit()"/>
                            <span>{{ $cat }}</span>
                        </label>
                    @endforeach
                </div>

                <!-- Price Range Filter -->
                <div class="sidebar-section">
                    <h4 class="sidebar-title">Max Price</h4>
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                        <span class="text-xs text-slate-400">$0</span>
                        <span class="text-xs font-bold" id="priceLabel">${{ $selectedPriceMax }}</span>
                    </div>
                    <input type="range" name="price_max" min="10" max="200" step="5" value="{{ $selectedPriceMax }}"
                        class="w-full accent-black cursor-pointer"
                        onchange="document.getElementById('filterForm').submit()"
                        oninput="document.getElementById('priceLabel').textContent = '$' + this.value"/>
                </div>

                <!-- Rating Filter -->
                <div class="sidebar-section">
                    <h4 class="sidebar-title">Rating</h4>
                    <input type="hidden" name="rating" id="ratingInput" value="{{ $selectedRating }}"/>
                    
                    <button type="button" class="stars-btn {{ $selectedRating == 4.5 ? 'active' : '' }}" onclick="setRatingFilter(4.5)">
                        <span>4.5 &amp; Up</span>
                        <span style="margin-left:auto"><i class="fa-solid fa-star"></i> 4.5</span>
                    </button>
                    <button type="button" class="stars-btn {{ $selectedRating == 4.0 ? 'active' : '' }}" onclick="setRatingFilter(4.0)">
                        <span>4.0 &amp; Up</span>
                        <span style="margin-left:auto"><i class="fa-solid fa-star"></i> 4.0</span>
                    </button>
                    <button type="button" class="stars-btn {{ !$selectedRating ? 'active' : '' }}" onclick="setRatingFilter('')">
                        <span>All Ratings</span>
                    </button>
                </div>
                
                <a href="{{ route('store.shop') }}" class="btn btn-outline btn-sm w-full text-center mt-3">Reset Filters</a>
            </form>
        </aside>

        <!-- PRODUCTS AREA -->
        <div>
            <!-- FILTER BAR -->
            <div class="filter-bar">
                <div class="flex items-center gap-4">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                        Showing <span class="text-slate-800 font-bold">{{ $products->count() }}</span> Products
                    </p>
                    @if($searchQuery)
                        <span class="text-xs bg-slate-100 border border-slate-200 px-2.5 py-1 rounded-lg">
                            Search: <strong>"{{ $searchQuery }}"</strong>
                        </span>
                    @endif
                </div>

                <div class="flex items-center gap-3">
                    <form action="{{ route('store.shop') }}" method="GET" class="search-bar">
                        @if($selectedRating) <input type="hidden" name="rating" value="{{ $selectedRating }}"/> @endif
                        @foreach($selectedCategories as $cat) <input type="hidden" name="category[]" value="{{ $cat }}"/> @endforeach
                        <input type="hidden" name="price_max" value="{{ $selectedPriceMax }}"/>
                        <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs"></i>
                        <input type="text" name="search" placeholder="Search catalogue…" value="{{ $searchQuery }}"/>
                    </form>

                    <!-- Sorting Dropdown -->
                    <select class="filter-select" onchange="applySort(this.value)">
                        <option value="default" {{ $selectedSort == 'default' ? 'selected' : '' }}>Featured</option>
                        <option value="price_asc" {{ $selectedSort == 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                        <option value="price_desc" {{ $selectedSort == 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                        <option value="rating" {{ $selectedSort == 'rating' ? 'selected' : '' }}>Top Rated</option>
                    </select>
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
                            <div style="display:flex;align-items:center;justify-content:space-between">
                                <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">{{ $product['cat'] }}</p>
                                <span class="text-xs text-amber-500 font-semibold"><i class="fa-solid fa-star text-[10px]"></i> {{ $product['rating'] }}</span>
                            </div>
                            <h3 class="font-display font-bold text-sm mt-1 text-slate-800 line-clamp-1">{{ $product['name'] }}</h3>
                            <div class="flex items-center gap-1.5 mt-2">
                                <span class="font-bold text-sm text-slate-900">${{ number_format($product['price'], 2) }}</span>
                                @if($product['old'])
                                    <span class="text-xs text-slate-400 line-through">${{ number_format($product['old'], 2) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div style="grid-column: span 3; padding: 80px 20px;" class="text-center text-slate-400">
                        <i class="fa-solid fa-store-slash text-5xl mb-3 opacity-25"></i>
                        <p class="text-sm">No products found matching your search or filters.</p>
                        <a href="{{ route('store.shop') }}" class="btn btn-primary btn-sm mt-4">Reset View</a>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
    function setRatingFilter(val) {
        document.getElementById('ratingInput').value = val;
        document.getElementById('filterForm').submit();
    }

    function applySort(val) {
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('sort', val);
        window.location.search = urlParams.toString();
    }
</script>
@endsection
