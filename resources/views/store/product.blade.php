@extends('layouts.store')

@section('title', $product['name'] . ' — sgcart')

@section('content')

    <style>
        .pd-main-img {
            overflow: hidden !important;
            position: relative;
        }

        #mainProductImg {
            transition: transform 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94), transform-origin 0.15s cubic-bezier(0.25, 0.46, 0.45, 0.94), opacity 0.2s ease !important;
            cursor: zoom-in;
            transform-origin: center center;
            will-change: transform, transform-origin;
        }

        .zoom-hint {
            transition: opacity 0.25s ease-in-out;
        }

        .pd-main-img:hover .zoom-hint {
            opacity: 0;
        }
    </style>

    <div class="section-inner pt-5 md:pt-6">

        <!-- PRODUCT PANEL -->
        <div class="pd-layout">

            <!-- Left Gallery Column -->
            <!-- Left Gallery Column -->
            <div class="pd-gallery">
                <div class="pd-thumbs"
                     style="{{ (!isset($product['images']) || count($product['images']) <= 1) ? 'display: none;' : '' }}">
                    @if(isset($product['images']))
                        @foreach($product['images'] as $imgSrc)
                            <div class="pd-thumb {{ $loop->first ? 'active' : '' }}"
                                 onclick="changeImage('{{ $imgSrc }}', this)">
                                <img src="{{ $imgSrc }}" alt="{{ $product['name'] }} Thumb"/>
                            </div>
                        @endforeach
                    @endif
                </div>

                <div class="pd-main-img">
                    <img id="mainProductImg" src="{{ $product['img'] }}" alt="{{ $product['name'] }}"/>
                    @if($product['badge'])
                        <span class="product-badge badge-{{ strtolower($product['badge']) }}"
                              style="top:16px; left:16px">{{ $product['badge'] }}</span>
                    @endif
                    <div
                        class="zoom-hint absolute bottom-4 right-4 bg-white/90 dark:bg-slate-900/90 backdrop-blur-sm text-slate-800 dark:text-slate-100 text-[10px] font-bold uppercase tracking-wider px-2.5 py-1.5 rounded-lg shadow-sm border border-slate-200/50 dark:border-slate-800 pointer-events-none flex items-center gap-1.5 z-10">
                        <i class="fa-solid fa-magnifying-glass-plus text-slate-500 dark:text-slate-400"></i> Hover to
                        Zoom
                    </div>
                </div>
            </div>

            <!-- Right Purchase Options Column -->
            <div>
                <form action="{{ route('store.cart.add') }}" method="POST" id="purchaseForm">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product['id'] }}"/>
                </form>

                <p class="text-xs font-bold text-accent uppercase tracking-widest mb-1.5">{{ $product['cat'] }}
                    Capsule</p>
                <div class="flex justify-between items-start gap-4 mb-2">
                    <h1 class="font-display font-extrabold text-3xl text-slate-900 leading-tight flex-1">{{ $product['name'] }}</h1>
                    @php
                        $inWishlist = in_array($product['id'], session('wishlist', []));
                    @endphp
                    <button type="button"
                            class="wishlist-detail-btn {{ $inWishlist ? 'active' : '' }}"
                            data-product-id="{{ $product['id'] }}"
                            onclick="toggleWishlist(this)"
                            title="{{ $inWishlist ? 'Remove from Wishlist' : 'Add to Wishlist' }}"
                            style="margin-top: 4px; flex-shrink: 0;">
                        <i class="{{ $inWishlist ? 'fa-solid' : 'fa-regular' }} fa-heart text-base"></i>
                    </button>
                </div>

                @if($approvedReviews->isNotEmpty())
                    <div class="flex items-center gap-1.5 mb-4">
                        <div class="flex items-center gap-0.5 text-amber-400 text-xs">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= round($avgProductRating))
                                    <i class="fa-solid fa-star"></i>
                                @else
                                    <i class="fa-regular fa-star text-slate-200 dark:text-slate-700"></i>
                                @endif
                            @endfor
                        </div>
                        <span
                            class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ number_format($avgProductRating, 1) }}</span>
                        <span
                            class="text-[10px] text-slate-400 font-medium">({{ $approvedReviews->count() }} {{ \Illuminate\Support\Str::plural('review', $approvedReviews->count()) }})</span>
                    </div>
                @endif



                <!-- Pricing & Stock Badge -->
                <div class="flex items-center justify-between flex-wrap gap-3 mb-5 pb-5 border-b border-slate-100">
                    <div class="flex items-end gap-3" id="variantPriceWrapper">
                        <span
                            class="font-display font-extrabold text-2xl text-slate-900">₹{{ number_format($product['price'], 2) }}</span>
                        @if($product['old'])
                            <span
                                class="text-lg text-slate-400 line-through">₹{{ number_format($product['old'], 2) }}</span>
                            <span
                                class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">Save {{ round((1 - $product['price'] / $product['old']) * 100) }}%</span>
                        @endif
                    </div>
                    <div>
                        @if(($product['stock'] ?? 0) > 0)
                            <span
                                class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> In Stock ({{ $product['stock'] }} left)
                            </span>
                        @else
                            <span
                                class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Out of Stock
                            </span>
                        @endif
                    </div>
                </div>

                <p class="text-xs text-slate-500 leading-relaxed mb-6">{{ $product['desc'] }}</p>

                <!-- Size Picker -->
                @if(!empty($product['sizes']))
                    <div class="mb-5">
                        <label class="label">Select Size</label>
                        <input type="hidden" name="size" id="sizeInput" value="{{ $product['sizes'][0] ?? '' }}"
                               form="purchaseForm"/>
                        <div class="flex flex-wrap gap-2">
                            @foreach($product['sizes'] as $sz)
                                <button type="button" class="size-btn {{ $loop->first ? 'active' : '' }}"
                                        onclick="selectSize('{{ $sz }}', this)">
                                    {{ $sz }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Color Picker -->
                @if(!empty($product['colors']))
                    <div class="mb-6">
                        <label class="label">Select Color</label>
                        <input type="hidden" name="color" id="colorInput" value="{{ $product['colors'][0] ?? '' }}"
                               form="purchaseForm"/>
                        <div class="flex gap-3">
                            @foreach($product['colors'] as $col)
                                <button type="button" class="color-btn {{ $loop->first ? 'active' : '' }}"
                                        style="background: {{ $col }}"
                                        onclick="selectColor('{{ $col }}', this)">
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Quantity & Actions -->
                <div class="flex gap-3 items-end pt-5 border-t border-slate-100 mb-6">
                    @if(($product['stock'] ?? 0) <= 0)
                        <div class="flex-1">
                            <button type="button"
                                    class="btn btn-primary w-full bg-slate-200 text-slate-400 border-none cursor-not-allowed hover:translate-y-0"
                                    style="height:46px; background-color:#e2e8f0 !important; color:#94a3b8 !important;"
                                    disabled>
                                <i class="fa-solid fa-ban"></i> Out of Stock
                            </button>
                        </div>
                    @else
                        <div>
                            <label class="label">Quantity</label>
                            <div class="qty-row" style="height:46px">
                                <button type="button" class="qty-btn" onclick="adjQty(-1)"><i
                                        class="fa-solid fa-minus text-xs"></i></button>
                                <input type="number" name="quantity" id="qtyInput" value="1" min="1" class="qty-num"
                                       readonly form="purchaseForm"/>
                                <button type="button" class="qty-btn" onclick="adjQty(1)"><i
                                        class="fa-solid fa-plus text-xs"></i></button>
                            </div>
                        </div>
                        <div class="flex-1">
                            <button type="submit" class="btn btn-primary w-full" style="height:46px"
                                    form="purchaseForm"><i class="fa-solid fa-cart-shopping"></i> Add To Cart
                            </button>
                        </div>
                    @endif
                </div>

                @if(($product['stock'] ?? 0) > 0 && ($product['stock'] ?? 0) <= 5)
                    <p class="text-xs text-amber-600 font-bold mb-6"><i
                            class="fa-solid fa-triangle-exclamation mr-1"></i> Only {{ $product['stock'] }} left in
                        stock - order soon!</p>
                @endif

                <!-- Info list -->
                <div class="border-t border-slate-100 pt-3">
                    <div class="info-row"><i
                            class="fa-solid fa-truck-fast"></i><span>Free global delivery on orders.</span></div>
                    <div class="info-row"><i class="fa-solid fa-rotate-left"></i><span>30-day effortless returns and refunds.</span>
                    </div>
                </div>

                <!-- Product specs tabs -->
                <div class="mt-6 pt-5 border-t border-slate-100">
                    <div class="flex gap-4 border-b border-slate-100 pb-2 mb-3">
                        <button type="button" class="tab-btn active" onclick="setSpecTab('description')">Description
                        </button>
                        @if(!empty($product['sku']) || !empty($product['manufacturer']) || !empty($product['weight']) || !empty($product['dimensions']))
                            <button type="button" class="tab-btn" onclick="setSpecTab('specs')">Specifications</button>
                        @endif
                        <button type="button" class="tab-btn" onclick="setSpecTab('shipping')">Shipping</button>
                        <button type="button" class="tab-btn" onclick="setSpecTab('reviews')">Reviews
                            ({{ $approvedReviews->count() }})
                        </button>
                    </div>
                    <div id="spec-description" class="spec-content">
                        <p class="text-xs text-slate-500 leading-relaxed">{!! nl2br(e($product['description'])) !!}</p>
                    </div>
                    @if(!empty($product['sku']) || !empty($product['manufacturer']) || !empty($product['weight']) || !empty($product['dimensions']))
                        <div id="spec-specs" class="spec-content" style="display:none">
                            <table class="w-full text-xs text-left text-slate-500">
                                <tbody>
                                @if(!empty($product['sku']))
                                    <tr class="border-b border-slate-100/50">
                                        <td class="py-2 font-bold text-slate-400 w-1/3">SKU</td>
                                        <td class="py-2 font-mono text-slate-700">{{ $product['sku'] }}</td>
                                    </tr>
                                @endif
                                @if(!empty($product['manufacturer']))
                                    <tr class="border-b border-slate-100/50">
                                        <td class="py-2 font-bold text-slate-400 w-1/3">Manufacturer</td>
                                        <td class="py-2 text-slate-700">{{ $product['manufacturer'] }}</td>
                                    </tr>
                                @endif
                                @if(!empty($product['weight']))
                                    <tr class="border-b border-slate-100/50">
                                        <td class="py-2 font-bold text-slate-400 w-1/3">Weight</td>
                                        <td class="py-2 text-slate-700">{{ $product['weight'] }}</td>
                                    </tr>
                                @endif
                                @if(!empty($product['dimensions']))
                                    <tr class="border-b border-slate-100/50">
                                        <td class="py-2 font-bold text-slate-400 w-1/3">Dimensions</td>
                                        <td class="py-2 text-slate-700">{{ $product['dimensions'] }}</td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </div>
                    @endif
                    <div id="spec-shipping" class="spec-content" style="display:none">
                        <p class="text-xs text-slate-500 leading-relaxed">Standard shipping takes between 3 to 7
                            business days depending on location. Tracking information is sent automatically via email
                            once shipped.</p>
                    </div>
                    <div id="spec-reviews" class="spec-content" style="display:none">
                        <!-- Summary Widget Grid -->
                        <div
                            class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center
                             dark:bg-slate-850/50 border border-slate-100 dark:border-slate-800/80 rounded-2xl p-6 mb-8">
                            <!-- Overall Score -->
                            <div class="flex flex-col items-center justify-center text-center">
                                <span
                                    class="text-4xl font-extrabold text-slate-850 dark:text-white mb-2">{{ number_format($avgProductRating, 1) }}</span>
                                <div class="flex gap-1 text-amber-400 text-lg mb-1.5">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= round($avgProductRating))
                                            <i class="fa-solid fa-star"></i>
                                        @else
                                            <i class="fa-regular fa-star text-slate-200 dark:text-slate-750"></i>
                                        @endif
                                    @endfor
                                </div>
                                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                                    {{ $approvedReviews->count() }} {{ Str::plural('rating', $approvedReviews->count()) }} and reviews
                                </span>
                            </div>

                            <!-- Star Progress bars -->
                            <div class="space-y-2.5">
                                @foreach([5 => $pct5, 4 => $pct4, 3 => $pct3, 2 => $pct2, 1 => $pct1] as $star => $pct)
                                    @php
                                        $count = ${"count" . $star};
                                    @endphp
                                    <div class="flex items-center gap-3 text-xs">
                                        <span
                                            class="w-8 text-right font-bold text-slate-755 dark:text-slate-350 shrink-0">{{ $star }} ★</span>
                                        <div
                                            class="flex-1 h-2 bg-slate-200 dark:bg-slate-200/20 dark:bg-slate-850 rounded-full overflow-hidden">
                                            <div class="h-full bg-amber-400 rounded-full"
                                                 style="width: {{ $pct }}%"></div>
                                        </div>
                                        <span
                                            class="w-8 text-left text-slate-500 dark:text-slate-400 shrink-0">{{ $count }}</span>
                                    </div>
                                @endforeach
                            </div>
                            <!-- Review Filter & Sorting Container -->
                            @if($approvedReviews->isNotEmpty())
                                <div class="col-span-2">

                                    <div class="mb-6">
                                        <h4 class="text-xs font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-2.5">
                                            User reviews sorted by</h4>
                                        <div class="flex flex-wrap gap-2" id="review-filter-container">
                                            <button type="button" onclick="filterReviews('helpful')"
                                                    class="review-filter-btn px-4 py-1.5 rounded-full text-xs font-bold border border-slate-200 dark:border-slate-750 text-slate-700 dark:text-slate-400 bg-transparent transition-colors cursor-pointer"
                                                    data-filter="helpful">Most Helpful
                                            </button>
                                            <button type="button" onclick="filterReviews('latest')"
                                                    class="review-filter-btn px-4 py-1.5 rounded-full text-xs font-bold border border-slate-200 dark:border-slate-750 text-slate-700 dark:text-slate-400 bg-transparent transition-colors cursor-pointer"
                                                    data-filter="latest">Latest
                                            </button>
                                            <button type="button" onclick="filterReviews('positive')"
                                                    class="review-filter-btn px-4 py-1.5 rounded-full text-xs font-bold border border-slate-200 dark:border-slate-750 text-slate-700 dark:text-slate-400 bg-transparent transition-colors cursor-pointer"
                                                    data-filter="positive">Positive
                                            </button>
                                            <button type="button" onclick="filterReviews('negative')"
                                                    class="review-filter-btn px-4 py-1.5 rounded-full text-xs font-bold border border-slate-200 dark:border-slate-750 text-slate-700 dark:text-slate-400 bg-transparent transition-colors cursor-pointer"
                                                    data-filter="negative">Negative
                                            </button>
                                        </div>
                                    </div>
                                    @endif

                                    <!-- Reviews Shimmer Placeholder -->
                                    <div id="reviews-shimmer" class="space-y-6 hidden" style="display: none;">
                                        <div
                                            class="animate-pulse flex flex-col gap-3 pb-6 border-b border-slate-100 dark:border-slate-800/60">
                                            <div class="h-4 bg-slate-200 dark:bg-slate-800 rounded w-1/4"></div>
                                            <div class="h-3 bg-slate-200 dark:bg-slate-800 rounded w-full"></div>
                                            <div class="h-3 bg-slate-200 dark:bg-slate-800 rounded w-5/6"></div>
                                            <div class="h-3 bg-slate-200 dark:bg-slate-800 rounded w-1/3 mt-2"></div>
                                        </div>
                                        <div
                                            class="animate-pulse flex flex-col gap-3 pb-6 border-b border-slate-100 dark:border-slate-800/60">
                                            <div class="h-4 bg-slate-200 dark:bg-slate-800 rounded w-1/4"></div>
                                            <div class="h-3 bg-slate-200 dark:bg-slate-800 rounded w-full"></div>
                                            <div class="h-3 bg-slate-200 dark:bg-slate-800 rounded w-1/2 mt-2"></div>
                                        </div>
                                    </div>

                                    <!-- Reviews List Container -->
                                    <div class="space-y-6" id="review-list-container">
                                        @include('store.partials.reviews', ['reviewsData' => $reviewsData])
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>

            </div>


            <!-- RELATED PRODUCTS -->
            @if(isset($related) && count($related) > 0)
                <div class="section" style="margin-top:40px">
                    <div class="section-header">
                        <h2 class="section-title">Related Products</h2>
                    </div>
                    <div class="grid-4">
                        @foreach($related as $rel)
                            <div class="product-card"
                                 onclick="window.location.href='{{ route('store.product', $rel['slug']) }}'">
                                <div class="product-card-img">
                                    <img src="{{ $rel['img'] }}" alt="{{ $rel['name'] }}"/>
                                    @php
                                        $inWishlist = in_array($rel['id'], session('wishlist', []));
                                    @endphp
                                    <button type="button" class="wishlist-btn {{ $inWishlist ? 'active' : '' }}"
                                            data-product-id="{{ $rel['id'] }}"
                                            onclick="event.stopPropagation(); toggleWishlist(this)"
                                            title="{{ $inWishlist ? 'Remove from Wishlist' : 'Add to Wishlist' }}">
                                        <i class="{{ $inWishlist ? 'fa-solid' : 'fa-regular' }} fa-heart"></i>
                                    </button>
                                </div>
                                <div class="product-card-body">
                                    <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">{{ $rel['cat'] }}</p>
                                    <h3 class="font-display font-bold text-sm mt-1 text-slate-800 line-clamp-1">{{ $rel['name'] }}</h3>
                                    <div class="flex items-center gap-1.5 mt-2">
                                        <span
                                            class="font-bold text-sm text-slate-900">₹{{ number_format($rel['price'], 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
        @endsection

        @section('scripts')
            <script>
                function changeImage(src, el) {
                    document.getElementById('mainProductImg').src = src;
                    document.querySelectorAll('.pd-thumb').forEach(t => t.classList.remove('active'));
                    el.classList.add('active');
                }

                function selectSize(val, el) {
                    document.getElementById('sizeInput').value = val;
                    document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('active'));
                    el.classList.add('active');
                }

                function selectColor(val, el) {
                    document.getElementById('colorInput').value = val;
                    document.querySelectorAll('.color-btn').forEach(b => b.classList.remove('active'));
                    el.classList.add('active');
                }

                function adjQty(dir) {
                    const inp = document.getElementById('qtyInput');
                    if (!inp) return;
                    let val = parseInt(inp.value) + dir;
                    if (val < 1) val = 1;
                    inp.value = val;
                }

                function setSpecTab(name) {
                    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                    document.querySelectorAll('.spec-content').forEach(c => c.style.display = 'none');

                    event.target.classList.add('active');
                    document.getElementById(`spec-${name}`).style.display = 'block';
                    if (name === 'reviews') {
                        const urlParams = new URLSearchParams(window.location.search);
                        const filter = urlParams.get('review_filter') || 'helpful';
                        const page = parseInt(urlParams.get('review_page')) || 1;
                        fetchReviews(filter, page, true);
                    }
                }

                // Zoom Image Feature
                document.addEventListener('DOMContentLoaded', function () {
                    const gallery = document.querySelector('.pd-main-img');
                    const img = document.getElementById('mainProductImg');

                    if (gallery && img) {
                        // Reusable zoom coordinate calculation and scaling
                        function zoomMove(clientX, clientY) {
                            const rect = gallery.getBoundingClientRect();
                            const x = ((clientX - rect.left) / rect.width) * 100;
                            const y = ((clientY - rect.top) / rect.height) * 100;

                            // Clamp coordinates between 0% and 100%
                            const clampedX = Math.max(0, Math.min(100, x));
                            const clampedY = Math.max(0, Math.min(100, y));

                            img.style.transformOrigin = `${clampedX}% ${clampedY}%`;
                            img.style.transform = 'scale(2.5)';
                        }

                        function zoomReset() {
                            img.style.transform = 'scale(1)';
                            img.style.transformOrigin = 'center center';
                        }

                        // Mouse Events
                        gallery.addEventListener('mousemove', function (e) {
                            zoomMove(e.clientX, e.clientY);
                        });

                        gallery.addEventListener('mouseleave', function () {
                            zoomReset();
                        });

                        // Mobile Touch Events (Swipe to zoom & pan)
                        gallery.addEventListener('touchstart', function (e) {
                            if (e.touches.length > 0) {
                                zoomMove(e.touches[0].clientX, e.touches[0].clientY);
                            }
                        }, {passive: true});

                        gallery.addEventListener('touchmove', function (e) {
                            if (e.touches.length > 0) {
                                // Prevent page scroll when interacting with zoom container
                                if (e.cancelable) {
                                    e.preventDefault();
                                }
                                zoomMove(e.touches[0].clientX, e.touches[0].clientY);
                            }
                        }, {passive: false});

                        gallery.addEventListener('touchend', function () {
                            zoomReset();
                        });

                        gallery.addEventListener('touchcancel', function () {
                            zoomReset();
                        });
                    }
                });
            </script>

            <!-- Photo Lightbox Modal -->
            <div id="lightbox-modal"
                 class="fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-black/85 hidden"
                 onclick="closeLightbox()">
                <button
                    class="absolute top-4 right-4 text-white hover:text-slate-350 bg-transparent border-none cursor-pointer outline-none">
                    <i class="fa-solid fa-xmark text-2xl"></i>
                </button>
                <img id="lightbox-image" src="" alt="Zoomed Review Image"
                     class="max-w-full max-h-[90vh] object-contain rounded-lg shadow-2xl"
                     onclick="event.stopPropagation()">
            </div>

            <script>
                function openLightbox(src) {
                    const modal = document.getElementById('lightbox-modal');
                    const img = document.getElementById('lightbox-image');
                    img.src = src;
                    modal.classList.remove('hidden');
                }

                function closeLightbox() {
                    const modal = document.getElementById('lightbox-modal');
                    modal.classList.add('hidden');
                }

                function showShimmer(callback) {
                    const listContainer = document.getElementById('review-list-container');
                    const shimmerContainer = document.getElementById('reviews-shimmer');
                    if (listContainer && shimmerContainer) {
                        listContainer.classList.add('hidden');
                        listContainer.style.display = 'none';
                        shimmerContainer.classList.remove('hidden');
                        shimmerContainer.style.display = 'block';
                        setTimeout(() => {
                            shimmerContainer.classList.add('hidden');
                            shimmerContainer.style.display = 'none';
                            listContainer.classList.remove('hidden');
                            listContainer.style.display = 'block';
                            if (typeof callback === 'function') {
                                callback();
                            }
                        }, 500);
                    } else {
                        if (typeof callback === 'function') {
                            callback();
                        }
                    }
                }

                function filterReviews(type) {
                    fetchReviews(type, 1);
                }

                function changeReviewPage(page) {
                    const urlParams = new URLSearchParams(window.location.search);
                    const currentFilter = urlParams.get('review_filter') || 'helpful';
                    fetchReviews(currentFilter, page);
                }

                function fetchReviews(filter, page, useShimmer = true) {
                    function executeFetch() {
                        const url = new URL(window.location.href);
                        url.searchParams.set('review_filter', filter);
                        url.searchParams.set('review_page', page);
                        url.searchParams.set('ajax', '1');

                        fetch(url.toString(), {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                            .then(response => response.text())
                            .then(html => {
                                const container = document.getElementById('review-list-container');
                                if (container) {
                                    container.innerHTML = html;
                                }

                                // Update URL parameters in the address bar without reloading
                                const displayUrl = new URL(window.location.href);
                                displayUrl.searchParams.set('review_filter', filter);
                                displayUrl.searchParams.set('review_page', page);
                                window.history.pushState({
                                    review_filter: filter,
                                    review_page: page
                                }, '', displayUrl.toString());

                                // Update active filter button styles
                                updateFilterButtonStyles(filter);
                            })
                            .catch(error => {
                                console.error('Error fetching reviews:', error);
                            });
                    }

                    if (useShimmer) {
                        showShimmer(executeFetch);
                    } else {
                        executeFetch();
                    }
                }

                function updateFilterButtonStyles(activeFilter) {
                    const container = document.getElementById('review-filter-container');
                    if (container) {
                        container.querySelectorAll('.review-filter-btn').forEach(btn => {
                            const btnFilter = btn.getAttribute('data-filter');
                            if (btnFilter === activeFilter) {
                                btn.classList.add('border-amber-500', 'text-amber-500', 'bg-amber-50/50', 'dark:bg-amber-200/20');
                                btn.classList.remove('border-slate-200', 'dark:border-slate-800', 'text-slate-655', 'dark:text-slate-400', 'bg-transparent');
                            } else {
                                btn.classList.remove('border-amber-500', 'text-amber-500', 'bg-amber-50/50', 'dark:bg-amber-200/20');
                                btn.classList.add('border-slate-200', 'dark:border-slate-800', 'text-slate-655', 'dark:text-slate-400', 'bg-transparent');
                            }
                        });
                    }
                }

                // Listen for browser Back/Forward navigation
                window.addEventListener('popstate', function (event) {
                    const urlParams = new URLSearchParams(window.location.search);
                    const filter = urlParams.get('review_filter') || 'helpful';
                    const page = parseInt(urlParams.get('review_page')) || 1;

                    // Only fetch with shimmer if we are actually viewing the reviews tab
                    const reviewsTabContent = document.getElementById('spec-reviews');
                    if (reviewsTabContent && reviewsTabContent.style.display !== 'none') {
                        fetchReviews(filter, page, true);
                    } else {
                        fetchReviews(filter, page, false);
                    }
                });

                document.addEventListener('DOMContentLoaded', function () {
                    const urlParams = new URLSearchParams(window.location.search);
                    const filter = urlParams.get('review_filter') || 'helpful';
                    const page = parseInt(urlParams.get('review_page')) || 1;

                    // Sync filter buttons with url parameter on initial page load
                    updateFilterButtonStyles(filter);

                    // If URL parameters indicate reviews are queried or we have a reviews hash, activate reviews tab
                    if (urlParams.has('review_filter') || urlParams.has('review_page') || window.location.hash === '#spec-reviews') {
                        const reviewsTabBtn = document.querySelector('button[onclick*="reviews"]');
                        if (reviewsTabBtn) {
                            // Set tab active without full page transition
                            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                            document.querySelectorAll('.spec-content').forEach(c => c.style.display = 'none');
                            reviewsTabBtn.classList.add('active');
                            const reviewsSpec = document.getElementById('spec-reviews');
                            if (reviewsSpec) {
                                reviewsSpec.style.display = 'block';
                            }
                            // Fetch reviews with shimmer
                            fetchReviews(filter, page, true);
                        }
                    }
                });
            </script>

    @includeIf('product-variants::storefront-variant-script')
@endsection
