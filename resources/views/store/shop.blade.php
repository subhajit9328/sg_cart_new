@extends('layouts.store')

@section('title', 'Shop Catalogue — sgcart')

@section('content')

@php
    $activeFilterCount = 0;
    if (!empty($selectedCategories)) {
        $activeFilterCount += count($selectedCategories);
    }
    if (!empty($selectedSubCategories)) {
        $activeFilterCount += count($selectedSubCategories);
    }
    if (request()->filled('price_max') && request('price_max') < 10000) {
        $activeFilterCount += 1;
    }
@endphp

<div class="section-inner pt-5 md:pt-6">
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
                    <div class="sidebar-category-accordion flex flex-col gap-2.5">
                        @forelse($sidebarCategories as $index => $parentCat)
                            @php
                                $parentName = $parentCat['name'];
                                $parentCount = $categoryCounts[$parentName] ?? 0;
                                $hasChildren = !empty($parentCat['children']);
                                $isParentSelected = in_array($parentName, $selectedCategories);
                                $hasChildSelected = false;
                                if ($hasChildren) {
                                    foreach ($parentCat['children'] as $child) {
                                        if (in_array($child['name'], $selectedSubCategories)) {
                                            $hasChildSelected = true;
                                            break;
                                        }
                                    }
                                }
                                $isOpen = $isParentSelected || $hasChildSelected;
                            @endphp
                            <div class="accordion-item group border border-slate-100 dark:border-slate-800 rounded-xl overflow-hidden {{ $isOpen ? 'is-open' : '' }}" id="accordion-{{ $index }}">
                                <div class="flex items-center justify-between py-2.5 px-3 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700/50 transition-colors duration-250 ease rounded-lg group-[.is-open]:rounded-b-none">
                                    <label class="cb-label flex items-center justify-between cursor-pointer py-0! select-none">
                                        <div class="flex items-center">
                                            <input type="checkbox" name="category[]" class="hidden parent-category-cb" value="{{ $parentName }}"
                                                {{ $isParentSelected ? 'checked' : '' }}
                                                onchange="onParentCategoryChange(this)"/>
                                            <span class="cb-box"><i @class(['fa-solid fa-check text-[9px] dark:text-white transition-opacity','opacity-0' => !$isParentSelected ])></i></span>
                                            <span class="cb-text font-bold text-xs text-slate-850 dark:text-slate-200 ml-2">{{ $parentName }}</span>
                                        </div>
                                        <span class="cb-count text-[10px] bg-slate-200 dark:bg-slate-800 px-2 py-0.5 rounded-full font-bold text-slate-500 dark:text-slate-400">{{ $parentCount }}</span>
                                    </label>

                                    @if($hasChildren)
                                        <button type="button" class="flex items-center justify-center w-6 h-6 border-none bg-transparent cursor-pointer outline-none text-slate-450 hover:text-slate-700 dark:text-slate-500 dark:hover:text-slate-350" onclick="toggleAccordion('accordion-{{ $index }}')">
                                            <i class="fa-solid fa-chevron-down text-[10px] transition-transform duration-250 ease {{ $isOpen ? 'rotate-180' : '' }}"></i>
                                        </button>
                                    @endif
                                </div>

                                @if($hasChildren)
                                    <div class="accordion-content pt-1.5 pb-2.5 px-3 pl-6 transition-all duration-250 ease bg-white dark:bg-slate-900 border-t border-slate-50 dark:border-slate-800 {{ $isOpen ? '' : 'hidden' }}">
                                        <div class="flex flex-col gap-2 pt-1.5">
                                            @foreach($parentCat['children'] as $child)
                                                @php
                                                    $childName = $child['name'];
                                                    $childCount = $categoryCounts[$childName] ?? 0;
                                                    $isChildChecked = $isParentSelected || in_array($childName, $selectedSubCategories);
                                                @endphp
                                                <label class="cb-label flex items-center justify-between cursor-pointer py-1 text-[13px] text-stone hover:text-ink transition-colors w-full select-none">
                                                    <div class="flex items-center">
                                                        <input type="checkbox" class="hidden child-category-cb" value="{{ $childName }}"
                                                            {{ $isChildChecked ? 'checked' : '' }}
                                                            @if(!$isParentSelected) name="sub_category[]" @endif
                                                            onchange="onChildCategoryChange(this)"/>
                                                        <span class="cb-box"><i @class(['fa-solid fa-check text-[9px] dark:text-white transition-opacity','opacity-0' => !$isChildChecked ])></i></span>
                                                        <span class="cb-text text-[13px] text-slate-650 dark:text-slate-300 ml-2">{{ $childName }}</span>
                                                    </div>
                                                    <span class="cb-count text-[11px] text-slate-400 font-medium">{{ $childCount }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="text-xs text-center text-slate-400">No Categories</div>
                        @endforelse
                    </div>
                </div>

                <!-- Price Range Filter -->
                <div class="sidebar-section">
                    <h4 class="sidebar-title">Max Price</h4>
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                        <span class="text-xs text-slate-400">₹0</span>
                        <span class="text-xs font-bold" id="priceLabel">₹{{ $selectedPriceMax }}</span>
                    </div>
                    <input type="range" name="price_max" min="100" max="100000" step="100" value="{{ $selectedPriceMax }}"
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
                        @if($products->total() > 0)
                            Showing <span class="text-slate-800 font-bold">{{ $products->firstItem() }}–{{ $products->lastItem() }}</span> of <span class="text-slate-800 font-bold">{{ $products->total() }}</span> Products
                        @else
                            Showing <span class="text-slate-800 font-bold">0</span> Products
                        @endif
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
                    <div class="filter-controls">
                        <button type="button" class="btn btn-outline border-border! btn-sm filter-toggle-btn lg:hidden" onclick="toggleMobileFilters()">
                            <i class="fa-solid fa-sliders"></i> Filters
                            @if($activeFilterCount > 0)
                                <span class="filter-indicator-dot"></span>
                            @endif
                        </button>

                        <!-- Sorting Dropdown -->
                        <select class="filter-select py-2.5!" onchange="applySort(this.value)">
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
                    <div class="product-card" onclick="window.location.href='{{ route('store.product', $product['slug']) }}'">
                        <div class="product-card-img">
                            @if($product['badge'])
                                <span class="product-badge badge-{{ strtolower($product['badge']) }}">{{ $product['badge'] }}</span>
                            @endif
                            <img src="{{ $product['img'] }}" alt="{{ $product['name'] }}"/>
                            @php
                                $inWishlist = in_array($product['id'], session('wishlist', []));
                            @endphp
                            <button type="button" class="wishlist-btn {{ $inWishlist ? 'active' : '' }}"
                                data-product-id="{{ $product['id'] }}"
                                onclick="event.stopPropagation(); toggleWishlist(this)"
                                title="{{ $inWishlist ? 'Remove from Wishlist' : 'Add to Wishlist' }}">
                                <i class="{{ $inWishlist ? 'fa-solid' : 'fa-regular' }} fa-heart"></i>
                            </button>
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
                    <div style="grid-column: 1 / -1; padding: 80px 20px;" class="text-center text-slate-400">
                        <i class="fa-solid fa-store-slash text-5xl mb-3 opacity-25"></i>
                        <p class="text-sm">No products found matching your search or filters.</p>
                        <a href="{{ route('store.shop') }}" class="btn btn-primary btn-sm mt-4">Clear All Filters</a>
                    </div>
                @endforelse
            </div>

            <!-- PAGINATION -->
            <x-custom_pagination :paginator="$products" size="md" class="mt-12 mb-6" />
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
    function onParentCategoryChange(parentCb) {
        const accordionItem = parentCb.closest('.accordion-item');
        const childCbs = accordionItem.querySelectorAll('.child-category-cb');

        // Toggle parent check icon visibility
        const parentIcon = parentCb.closest('label').querySelector('.cb-box i');
        if (parentIcon) {
            if (parentCb.checked) {
                parentIcon.classList.remove('opacity-0');
            } else {
                parentIcon.classList.add('opacity-0');
            }
        }

        childCbs.forEach(cb => {
            cb.checked = parentCb.checked;
            const icon = cb.closest('label').querySelector('.cb-box i');
            if (icon) {
                if (parentCb.checked) {
                    icon.classList.remove('opacity-0');
                    cb.removeAttribute('name');
                } else {
                    icon.classList.add('opacity-0');
                    cb.setAttribute('name', 'sub_category[]');
                }
            }
        });

        document.getElementById('filterForm').submit();
    }

    function onChildCategoryChange(childCb) {
        const accordionItem = childCb.closest('.accordion-item');
        const parentCb = accordionItem.querySelector('.parent-category-cb');
        const childCbs = accordionItem.querySelectorAll('.child-category-cb');

        // Toggle child check icon visibility
        const childIcon = childCb.closest('label').querySelector('.cb-box i');
        if (childIcon) {
            if (childCb.checked) {
                childIcon.classList.remove('opacity-0');
            } else {
                childIcon.classList.add('opacity-0');
            }
        }

        const allChecked = Array.from(childCbs).every(cb => cb.checked);
        if (allChecked && childCbs.length > 0) {
            parentCb.checked = true;
            const parentIcon = parentCb.closest('label').querySelector('.cb-box i');
            if (parentIcon) parentIcon.classList.remove('opacity-0');
            childCbs.forEach(cb => cb.removeAttribute('name'));
        } else {
            parentCb.checked = false;
            const parentIcon = parentCb.closest('label').querySelector('.cb-box i');
            if (parentIcon) parentIcon.classList.add('opacity-0');
            childCbs.forEach(cb => cb.setAttribute('name', 'sub_category[]'));
        }

        document.getElementById('filterForm').submit();
    }

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

    function toggleAccordion(id) {
        const item = document.getElementById(id);
        if (item) {
            item.classList.toggle('is-open');
            const content = item.querySelector('.accordion-content');
            const icon = item.querySelector('.accordion-toggle-btn i');
            if (content) {
                content.classList.toggle('hidden');
            }
            if (icon) {
                icon.classList.toggle('rotate-180');
            }
        }
    }
</script>
@endsection
