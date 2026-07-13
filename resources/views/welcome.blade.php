@extends('layouts.store')

@section('title', 'SGCart — Premium Fashion Storefront')

@section('content')


<!-- HERO SECTION -->
<div class="hero @if(isset($heroImages) && $heroImages->isNotEmpty()) hero-full-width @endif">
    @if(isset($heroImages) && $heroImages->isNotEmpty())
        <!-- BACKGROUND CAROUSEL / SLIDER / IMAGE -->
        <div class="hero-bg-container">
            @if($heroImages->count() > 1)
                <!-- CAROUSEL/SLIDER MODE -->
                <div class="hero-carousel">
                    <div class="carousel-slides">
                        @foreach($heroImages as $img)
                            <div class="carousel-slide">
                                @if($img->url)
                                    <a href="{{ $img->url }}" target="_blank" class="w-full h-full block">
                                        <picture class="w-full h-full block">
                                            @if($img->image_mobile)
                                                <source media="(max-width: 640px)" srcset="{{ \Illuminate\Support\Facades\Storage::url($img->image_mobile) }}">
                                            @endif
                                            @if($img->image_tablet)
                                                <source media="(max-width: 1024px)" srcset="{{ \Illuminate\Support\Facades\Storage::url($img->image_tablet) }}">
                                            @endif
                                            <img src="{{ \Illuminate\Support\Facades\Storage::url($img->image_desktop ?: $img->image_path) }}" alt="Hero Collection Slide"/>
                                        </picture>
                                    </a>
                                @else
                                    <picture class="w-full h-full block">
                                        @if($img->image_mobile)
                                            <source media="(max-width: 640px)" srcset="{{ \Illuminate\Support\Facades\Storage::url($img->image_mobile) }}">
                                        @endif
                                        @if($img->image_tablet)
                                            <source media="(max-width: 1024px)" srcset="{{ \Illuminate\Support\Facades\Storage::url($img->image_tablet) }}">
                                        @endif
                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($img->image_desktop ?: $img->image_path) }}" alt="Hero Collection Slide"/>
                                    </picture>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <!-- Navigation Dots -->
                    <div class="carousel-dots">
                        @foreach($heroImages as $index => $img)
                            <button class="carousel-dot" data-index="{{ $index }}"></button>
                        @endforeach
                    </div>
                    <!-- Prev/Next Controls -->
                    <button class="carousel-prev" aria-label="Previous slide"><i class="fa-solid fa-chevron-left"></i></button>
                    <button class="carousel-next" aria-label="Next slide"><i class="fa-solid fa-chevron-right"></i></button>
                </div>
            @else
                <!-- SINGLE IMAGE MODE -->
                @php $firstImg = $heroImages->first(); @endphp
                <div class="hero-single-image">
                    @if($firstImg->url)
                        <a href="{{ $firstImg->url }}" target="_blank" class="w-full h-full block">
                            <picture class="w-full h-full block">
                                @if($firstImg->image_mobile)
                                    <source media="(max-width: 640px)" srcset="{{ \Illuminate\Support\Facades\Storage::url($firstImg->image_mobile) }}">
                                @endif
                                @if($firstImg->image_tablet)
                                    <source media="(max-width: 1024px)" srcset="{{ \Illuminate\Support\Facades\Storage::url($firstImg->image_tablet) }}">
                                @endif
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($firstImg->image_desktop ?: $firstImg->image_path) }}" alt="Hero Collection Slide"/>
                            </picture>
                        </a>
                    @else
                        <picture class="w-full h-full block">
                            @if($firstImg->image_mobile)
                                <source media="(max-width: 640px)" srcset="{{ \Illuminate\Support\Facades\Storage::url($firstImg->image_mobile) }}">
                            @endif
                            @if($firstImg->image_tablet)
                                <source media="(max-width: 1024px)" srcset="{{ \Illuminate\Support\Facades\Storage::url($firstImg->image_tablet) }}">
                            @endif
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($firstImg->image_desktop ?: $firstImg->image_path) }}" alt="Hero Collection Slide"/>
                        </picture>
                    @endif
                </div>
            @endif
        </div>
    @else
        <!-- DEFAULT 2-COLUMN VIEW WHEN NO IMAGES UPLOADED -->
        <div class="hero-left">
            <div class="absolute w-80 h-80 rounded-full bg-blue-500/10 blur-3xl -top-10 -left-10"></div>
            <div class="hero-eyebrow">Spring Collection 2026</div>
            <h1 class="hero-title">Elevate Your<br>Standard <em>Style</em></h1>
            <p class="hero-sub">Discover high-quality linen shirts, structured accessories, and lightweight knitwear engineered for maximum ease and durability.</p>
            <div class="hero-actions">
                <a href="{{ route('store.shop') }}" class="btn btn-accent">Shop The Drop <i class="fa-solid fa-arrow-right"></i></a>
                <a href="{{ route('store.shop', ['category' => "Women's Clothing"]) }}" class="btn btn-ghost">View Editorial</a>
            </div>
        </div>
        <div class="hero-right">
            <img src="https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=1200&auto=format&fit=crop&q=80" alt="Model wear in linen"/>
            <div class="hero-overlay-tag">
                <p class="tag-num">100%</p>
                <p class="tag-label">Organic Cotton</p>
            </div>
        </div>
    @endif
</div>

<!-- MARQUEE STRIP -->
<div class="marquee-strip">
    <div class="marquee-inner">
        <span>FREE SHIPPING WORLDWIDE <span class="marquee-dot">•</span> NEW ARRIVALS DAILY <span class="marquee-dot">•</span> SECURE CHECKOUT <span class="marquee-dot">•</span> 20% DISCOUNT CODE: SGCART20</span>
        <span>FREE SHIPPING WORLDWIDE <span class="marquee-dot">•</span> NEW ARRIVALS DAILY <span class="marquee-dot">•</span> SECURE CHECKOUT <span class="marquee-dot">•</span> 20% DISCOUNT CODE: SGCART20</span>
    </div>
</div>

<!-- FEATURED CATEGORIES -->
<div class="section" style="background:#fff">
    <div class="section-inner">
        <div class="section-header">
            <div>
                <h2 class="section-title">Shop by Category</h2>
                <p class="section-sub">Explore our curated capsules for every wardrobe need</p>
            </div>
            <a href="{{ route('store.shop') }}" class="btn btn-outline btn-sm">Explore All</a>
        </div>
        <div class="cat-grid">
            @foreach($categories as $cat)
                <a href="{{ route('store.shop', ['category' => $cat->name]) }}" class="cat-card">
                    <img src="{{ $cat->image ? \Illuminate\Support\Facades\Storage::url($cat->image) : asset('images/no-image.svg') }}" alt="{{ $cat->name }}"/>
                    <div class="cat-card-label">
                        <p>{{ $cat->name }}</p>
                        <span>Shop Capsule <i class="fa-solid fa-chevron-right text-[10px] ml-0.5"></i></span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</div>

<!-- FEATURED PRODUCTS -->
<div class="section">
    <div class="section-inner">
        <div class="section-header">
            <div>
                <h2 class="section-title">New Drops</h2>
                <p class="section-sub">Fresh arrivals styled for the seasonal transit</p>
            </div>
            <a href="{{ route('store.shop') }}" class="btn btn-outline btn-sm">View All</a>
        </div>
        
        <div class="grid-4">
            @foreach($products as $product)
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
            @endforeach
        </div>
    </div>
</div>

<!-- STUDIO FEED CAROUSEL -->
@if(isset($socialPosts) && $socialPosts->isNotEmpty())
<div class="section" style="background:#fff">
    <div class="section-inner">
        <div class="section-header">
            <div>
                <h2 class="section-title">Studio Feed</h2>
                <p class="section-sub">Get inspired by real customer looks</p>
            </div>
            <a href="{{ route('store.social-share.index') }}" class="btn btn-outline btn-sm">View All</a>
        </div>

        <div class="relative">
            <div id="studioCarouselTrack" class="studio-carousel-track flex overflow-x-auto gap-6 scroll-smooth snap-x snap-mandatory">
                @foreach($socialPosts as $post)
                    @php
                        $isFollowing = false;
                        if(auth('customer')->check()) {
                            $isFollowing = auth('customer')->user()->following->contains($post->customer_id);
                        }
                    @endphp
                    <div class="studio-card-item snap-start shrink-0">
                        <div class="bg-white dark:bg-[#151411] border border-[#e8e4df] dark:border-[#2e2c28] rounded-3xl overflow-hidden transition-all duration-300 hover:shadow-xl flex flex-col h-full group" onclick="openStudioModal({{ json_encode($post) }}, {{ $isFollowing ? 'true' : 'false' }}, '{{ auth('customer')->id() == $post->customer_id ? 'true' : 'false' }}')">
                            
                            <!-- Influencer Header -->
                            <div class="p-3.5 flex items-center justify-between border-b border-slate-50 dark:border-slate-800" onclick="event.stopPropagation();">
                                <div class="flex items-center gap-2 min-w-0">
                                    <div class="w-7 h-7 rounded-full bg-slate-100 dark:bg-slate-850 flex items-center justify-center font-bold text-[10px] text-slate-655 overflow-hidden shrink-0 border border-slate-200 dark:border-slate-700">
                                        @if($post->customer?->profile_picture)
                                            <img src="{{ Storage::url($post->customer->profile_picture) }}" alt="Avatar" class="w-full h-full object-cover">
                                        @else
                                            {{ strtoupper(substr($post->customer?->name ?? '?', 0, 2)) }}
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="font-display font-bold text-xs text-slate-800 dark:text-slate-200 truncate leading-tight">{{ $post->customer?->name ?? 'Influencer' }}</h4>
                                    </div>
                                </div>
                                
                                <!-- Follow button -->
                                @if(auth('customer')->check() && auth('customer')->id() == $post->customer_id)
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">You</span>
                                @else
                                    <button 
                                        type="button" 
                                        data-influencer-id="{{ $post->customer_id }}"
                                        onclick="toggleFollow(this, '{{ $post->customer_id }}')" 
                                        class="follow-btn px-2.5 py-0.5 rounded-full text-[9px] font-extrabold tracking-wider uppercase transition-all border {{ $isFollowing ? 'bg-slate-100 text-slate-750 border-slate-200 dark:bg-slate-800 dark:text-slate-350 dark:border-slate-700' : 'bg-rose-500 text-white border-rose-500 hover:bg-rose-600' }} cursor-pointer select-none"
                                    >
                                        {{ $isFollowing ? 'Following' : 'Follow' }}
                                    </button>
                                @endif
                            </div>

                            <!-- Media -->
                            <div class="relative aspect-[3/4] bg-slate-950 overflow-hidden flex items-center justify-center">
                                @if($post->media_type === 'video')
                                    <video src="{{ Storage::url($post->media_path) }}" loop muted playsinline class="w-full h-full object-cover opacity-90"></video>
                                    <div class="absolute inset-0 bg-black/10 flex items-center justify-center">
                                        <span class="w-10 h-10 rounded-full bg-white/25 backdrop-blur-sm border border-white/30 flex items-center justify-center text-white text-sm">
                                            <i class="fa-solid fa-play ml-0.5"></i>
                                        </span>
                                    </div>
                                @else
                                    <img src="{{ Storage::url($post->media_path) }}" alt="Look" class="w-full h-full object-cover">
                                @endif
                                
                                @if($post->caption)
                                    <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent p-3 pt-8 text-white">
                                        <p class="text-[11px] font-medium line-clamp-2 leading-relaxed opacity-90">{{ $post->caption }}</p>
                                    </div>
                                @endif
                            </div>

                            <!-- Linked Product Banner -->
                            @if($post->product)
                                <div class="p-3 bg-slate-50/50 dark:bg-[#1a1916]/50 border-t border-slate-100 dark:border-slate-800 flex items-center gap-2.5 mt-auto bg-white dark:bg-[#151411]" onclick="event.stopPropagation();">
                                    <div class="w-9 h-9 rounded-lg overflow-hidden shrink-0 border border-slate-200 dark:border-slate-850 bg-white">
                                        <img src="{{ $post->product->image ? Storage::url($post->product->image) : '/images/placeholder.jpg' }}" alt="Product" class="w-full h-full object-cover">
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h5 class="font-semibold text-[11px] text-slate-800 dark:text-slate-200 truncate leading-snug">{{ $post->product->name }}</h5>
                                        <p class="text-[10px] font-extrabold text-slate-950 dark:text-white mt-0.5">₹{{ number_format($post->product->price, 2) }}</p>
                                    </div>
                                    @if($post->shop_link)
                                        <a href="{{ $post->shop_link }}" class="btn btn-primary px-2 py-1 rounded-md text-[9px] font-bold uppercase tracking-wider shrink-0 no-underline" style="display:inline-block;">
                                            Shop
                                        </a>
                                    @endif
                                </div>
                            @else
                                <div class="p-3 bg-slate-50/30 dark:bg-[#1a1916]/30 border-t border-slate-100 dark:border-slate-800 text-center text-[10px] text-slate-400 italic mt-auto">
                                    General inspiration look
                                </div>
                            @endif

                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Carousel Controls -->
            <button class="studio-prev-btn absolute left-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-white/85 dark:bg-black/85 border border-slate-200 dark:border-slate-800 flex items-center justify-center text-slate-700 dark:text-slate-350 shadow cursor-pointer hover:scale-105 transition-transform z-10">
                <i class="fa-solid fa-chevron-left text-xs"></i>
            </button>
            <button class="studio-next-btn absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-white/85 dark:bg-black/85 border border-slate-200 dark:border-slate-800 flex items-center justify-center text-slate-700 dark:text-slate-350 shadow cursor-pointer hover:scale-105 transition-transform z-10">
                <i class="fa-solid fa-chevron-right text-xs"></i>
            </button>
        </div>
    </div>
</div>

<!-- Studio Lightbox Modal Overlay -->
<div id="studioModal" class="fixed inset-0 z-50 bg-black/90 flex items-center justify-center p-4 opacity-0 pointer-events-none transition-all duration-300">
    <div class="relative bg-slate-900 dark:bg-slate-950 rounded-3xl max-w-4xl w-full max-h-[90vh] overflow-hidden shadow-2xl flex flex-col md:flex-row border border-slate-800">
        
        <!-- Close Button -->
        <button onclick="closeStudioModal()" class="absolute right-4 top-4 z-20 w-9 h-9 bg-black/60 hover:bg-black/80 text-white rounded-full flex items-center justify-center transition-colors border-none cursor-pointer">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <!-- Media Display (Left side) -->
        <div class="w-full md:w-3/5 bg-black flex items-center justify-center aspect-[4/5] md:aspect-auto md:h-[80vh]">
            <div id="studioModalMedia" class="w-full h-full flex items-center justify-center relative">
                <!-- Injected -->
            </div>
        </div>

        <!-- Sidebar Details (Right side) -->
        <div class="w-full md:w-2/5 p-6 flex flex-col justify-between bg-white dark:bg-[#12110e] border-t md:border-t-0 md:border-l border-slate-100 dark:border-slate-800 overflow-y-auto">
            
            <!-- Influencer & Follow header -->
            <div>
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-3">
                        <div id="modalInfluencerAvatar" class="w-10 h-10 rounded-full bg-slate-200 dark:bg-slate-850 flex items-center justify-center font-bold text-slate-655 overflow-hidden shrink-0 border border-slate-200 dark:border-slate-700">
                            <!-- Injected -->
                        </div>
                        <div>
                            <h3 id="modalInfluencerName" class="font-display font-extrabold text-sm text-slate-900 dark:text-white leading-tight">Name</h3>
                            <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">Style Partner</p>
                        </div>
                    </div>
                    
                    <button 
                        type="button" 
                        id="modalFollowBtn"
                        class="px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider transition-all border cursor-pointer select-none"
                    >
                        Follow
                    </button>
                </div>

                <!-- Caption text -->
                <div class="py-5">
                    <p class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">Caption</p>
                    <p id="modalCaption" class="text-sm text-slate-750 dark:text-slate-350 leading-relaxed font-medium">Caption text...</p>
                </div>
            </div>

            <!-- Product Link Card at bottom -->
            <div id="modalProductCard" class="mt-auto pt-4 border-t border-slate-100 dark:border-slate-800">
                <p class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-3">Shop the Look</p>
                <div class="p-3 border border-slate-200 dark:border-slate-800 rounded-2xl flex items-center gap-3.5 bg-slate-50/50 dark:bg-[#1a1916]/30">
                    <div class="w-14 h-14 rounded-xl overflow-hidden shrink-0 border border-slate-200 dark:border-slate-800 bg-white">
                        <img id="modalProductImg" src="" alt="Product" class="w-full h-full object-cover">
                    </div>
                    <div class="min-w-0 flex-1">
                        <h4 id="modalProductName" class="font-bold text-xs text-slate-800 dark:text-slate-200 truncate leading-snug">Product Name</h4>
                        <p id="modalProductPrice" class="font-extrabold text-sm text-slate-950 dark:text-white mt-1">₹0.00</p>
                    </div>
                    <a id="modalProductShopBtn" href="" class="btn btn-primary btn-sm px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider shrink-0 no-underline">
                        Shop
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>
@endif

@endsection

<style>
    /* Carousel Container */
    .hero-carousel {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
    }
    .carousel-slides {
        display: flex;
        width: 100%;
        height: 100%;
        transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .carousel-slide {
        flex-shrink: 0;
        width: 100%;
        height: 100%;
    }
    .carousel-slide img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .carousel-slide picture,
    .hero-single-image picture {
        width: 100%;
        height: 100%;
        display: block;
    }
    
    /* Navigation dots */
    .carousel-dots {
        position: absolute;
        bottom: 24px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 8px;
        z-index: 10;
    }
    .carousel-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.4);
        border: 1px solid rgba(255, 255, 255, 0.2);
        cursor: pointer;
        padding: 0;
        transition: all 0.3s ease;
    }
    .carousel-dot.active {
        background: #fff;
        width: 24px;
        border-radius: 4px;
    }

    /* Arrow Controls */
    .carousel-prev,
    .carousel-next {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: rgba(0, 0, 0, 0.25);
        backdrop-filter: blur(4px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        z-index: 10;
    }
    .carousel-prev:hover,
    .carousel-next:hover {
        background: rgba(0, 0, 0, 0.5);
    }
    .carousel-prev {
        left: 16px;
    }
    .carousel-next {
        right: 16px;
    }

    /* Full width hero overrides when package slides are present */
    .hero.hero-full-width {
        display: block;
        position: relative;
        min-height: 70vh;
    }
    .hero-full-width .hero-bg-container {
        position: relative;
        width: 100%;
        height: 70vh;
        overflow: hidden;
    }
    .hero-full-width .hero-single-image {
        width: 100%;
        height: 100%;
    }
    .hero-full-width .hero-single-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    @media(max-width: 1024px) {
        .hero.hero-full-width {
            min-height: 50vh;
        }
        .hero-full-width .hero-bg-container {
            height: 50vh;
        }
    }

    /* Studio Carousel Track & Items */
    .studio-carousel-track {
        gap: 24px;
        overflow-x: auto;
        scroll-behavior: smooth;
        scrollbar-width: none; /* Hide scrollbar Firefox */
        padding: 10px 4px 20px 4px;
        -webkit-overflow-scrolling: touch;
    }
    .studio-carousel-track::-webkit-scrollbar {
        display: none; /* Hide scrollbar Chrome/Safari/Opera */
    }
    .studio-card-item {
        width: 290px;
        scroll-snap-align: start;
    }
    @media(max-width: 640px) {
        .studio-card-item {
            width: 250px;
        }
    }
</style>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const carousel = document.querySelector('.hero-carousel');
        if (!carousel) return;

        const slides = carousel.querySelector('.carousel-slides');
        const slideItems = carousel.querySelectorAll('.carousel-slide');
        const dots = carousel.querySelectorAll('.carousel-dot');
        const prevBtn = carousel.querySelector('.carousel-prev');
        const nextBtn = carousel.querySelector('.carousel-next');

        let currentIndex = 0;
        const totalSlides = slideItems.length;
        let autoPlayInterval;

        function showSlide(index) {
            if (index < 0) index = totalSlides - 1;
            if (index >= totalSlides) index = 0;
            currentIndex = index;

            slides.style.transform = `translateX(-${currentIndex * 100}%)`;

            dots.forEach((dot, idx) => {
                if (idx === currentIndex) {
                    dot.classList.add('active');
                } else {
                    dot.classList.remove('active');
                }
            });
        }

        function nextSlide() {
            showSlide(currentIndex + 1);
        }

        function startAutoPlay() {
            autoPlayInterval = setInterval(nextSlide, 5000);
        }

        // Initialize
        showSlide(0);
        startAutoPlay();
    });

    @if(isset($socialPosts) && $socialPosts->isNotEmpty())
    // Studio carousel navigation scroll script
    document.addEventListener('DOMContentLoaded', function () {
        const track = document.getElementById('studioCarouselTrack');
        const prevBtn = document.querySelector('.studio-prev-btn');
        const nextBtn = document.querySelector('.studio-next-btn');

        if (track && prevBtn && nextBtn) {
            const cardWidth = 314; // card width (290px) + gap (24px)
            prevBtn.addEventListener('click', function () {
                track.scrollBy({ left: -cardWidth, behavior: 'smooth' });
            });
            nextBtn.addEventListener('click', function () {
                track.scrollBy({ left: cardWidth, behavior: 'smooth' });
            });
        }
    });

    // Follow/Unfollow Helper inside Home page
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
            const contentType = res.headers.get('content-type');
            if (res.status === 401 || (contentType && contentType.includes('text/html'))) {
                showToast('Please log in to follow style partners.', 'error');
                setTimeout(() => {
                    window.location.href = "{{ route('store.login') }}";
                }, 1000);
                throw new Error('Unauthorized');
            }
            if (res.status === 403) {
                return res.json().then(data => {
                    showToast(data.message || 'Please verify your contact information.', 'error');
                    setTimeout(() => {
                        window.location.href = "{{ route('store.otp.verify') }}";
                    }, 1200);
                    throw new Error('Forbidden');
                });
            }
            if (!res.ok) {
                throw new Error('Server error');
            }
            return res.json();
        })
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                
                // Toggle styles for all follow buttons of this influencer in the page
                const allButtons = document.querySelectorAll(`.follow-btn[data-influencer-id="${influencerId}"]`);
                allButtons.forEach(b => {
                    if (data.is_following) {
                        b.textContent = 'Following';
                        b.className = 'follow-btn px-2.5 py-0.5 rounded-full text-[9px] font-extrabold tracking-wider uppercase transition-all border bg-slate-100 text-slate-750 border-slate-200 dark:bg-slate-800 dark:text-slate-350 dark:border-slate-700 cursor-pointer select-none';
                    } else {
                        b.textContent = 'Follow';
                        b.className = 'follow-btn px-2.5 py-0.5 rounded-full text-[9px] font-extrabold tracking-wider uppercase transition-all border bg-rose-500 text-white border-rose-500 hover:bg-rose-600 cursor-pointer select-none';
                    }
                });

                // Update the modal follow button if currently open
                const modalBtn = document.getElementById('modalFollowBtn');
                if (modalBtn && modalBtn.getAttribute('data-influencer-id') === influencerId) {
                    if (data.is_following) {
                        modalBtn.textContent = 'Following';
                        modalBtn.className = 'px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider transition-all border bg-slate-100 text-slate-750 border-slate-200 dark:bg-slate-800 dark:text-slate-350 dark:border-slate-700 cursor-pointer select-none';
                    } else {
                        modalBtn.textContent = 'Follow';
                        modalBtn.className = 'px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider transition-all border bg-rose-500 text-white border-rose-500 hover:bg-rose-600 cursor-pointer select-none';
                    }
                }
            } else {
                showToast(data.message || 'Follow request failed.', 'error');
            }
        })
        .catch(err => {
            if (err.message !== 'Unauthorized') {
                showToast('Something went wrong.', 'error');
            }
        })
        .finally(() => {
            btn.disabled = false;
        });
    }

    function openStudioModal(post, isFollowing, isSelf) {
        const modal = document.getElementById('studioModal');
        const mediaContainer = document.getElementById('studioModalMedia');
        const avatar = document.getElementById('modalInfluencerAvatar');
        const name = document.getElementById('modalInfluencerName');
        const followBtn = document.getElementById('modalFollowBtn');
        const caption = document.getElementById('modalCaption');
        const productCard = document.getElementById('modalProductCard');

        // Set media
        const mediaUrl = "{{ Storage::url(':path') }}".replace(':path', post.media_path);
        if (post.media_type === 'video') {
            mediaContainer.innerHTML = `<video src="${mediaUrl}" controls autoplay loop class="max-w-full max-h-[78vh] rounded-2xl" style="outline:none;"></video>`;
        } else {
            mediaContainer.innerHTML = `<img src="${mediaUrl}" alt="Look" class="max-w-full max-h-[78vh] rounded-2xl object-contain" />`;
        }

        // Set influencer avatar and name
        if (post.customer && post.customer.profile_picture) {
            const avatarUrl = "{{ Storage::url(':avatar') }}".replace(':avatar', post.customer.profile_picture);
            avatar.innerHTML = `<img src="${avatarUrl}" alt="Avatar" class="w-full h-full object-cover">`;
        } else {
            const initials = post.customer ? post.customer.name.substring(0, 2).toUpperCase() : 'IP';
            avatar.innerHTML = `<span>${initials}</span>`;
        }
        name.textContent = post.customer ? post.customer.name : 'Style Partner';

        // Set follow button
        if (isSelf === 'true') {
            followBtn.textContent = 'You';
            followBtn.disabled = true;
            followBtn.className = 'px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider border text-slate-400 border-slate-200 dark:border-slate-800 bg-transparent cursor-not-allowed select-none';
        } else {
            followBtn.disabled = false;
            followBtn.setAttribute('data-influencer-id', post.customer_id);
            followBtn.setAttribute('onclick', `toggleFollow(this, '${post.customer_id}')`);
            
            if (isFollowing) {
                followBtn.textContent = 'Following';
                followBtn.className = 'px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider transition-all border bg-slate-100 text-slate-750 border-slate-200 dark:bg-slate-800 dark:text-slate-350 dark:border-slate-700 cursor-pointer select-none';
            } else {
                followBtn.textContent = 'Follow';
                followBtn.className = 'px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider transition-all border bg-rose-500 text-white border-rose-500 hover:bg-rose-600 cursor-pointer select-none';
            }
        }

        // Set caption
        caption.textContent = post.caption || 'No caption provided.';

        // Set product details
        if (post.product) {
            productCard.style.display = 'block';
            document.getElementById('modalProductName').textContent = post.product.name;
            document.getElementById('modalProductPrice').textContent = '₹' + parseFloat(post.product.price).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            
            const productImg = post.product.image ? "{{ Storage::url(':img') }}".replace(':img', post.product.image) : '/images/placeholder.jpg';
            document.getElementById('modalProductImg').src = productImg;
            
            if (post.shop_link) {
                document.getElementById('modalProductShopBtn').style.display = 'inline-block';
                document.getElementById('modalProductShopBtn').href = post.shop_link;
            } else {
                document.getElementById('modalProductShopBtn').style.display = 'none';
            }
        } else {
            productCard.style.display = 'none';
        }

        // Open modal
        modal.classList.remove('opacity-0', 'pointer-events-none');
        modal.classList.add('opacity-100');
    }

    function closeStudioModal() {
        const modal = document.getElementById('studioModal');
        const mediaContainer = document.getElementById('studioModalMedia');
        mediaContainer.innerHTML = ''; // Stop video playback
        modal.classList.remove('opacity-100');
        modal.classList.add('opacity-0', 'pointer-events-none');
    }

    // Close on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeStudioModal();
        }
    });

    // Close on clicking backdrop
    document.addEventListener('click', function(e) {
        const modal = document.getElementById('studioModal');
        if (e.target === modal) {
            closeStudioModal();
        }
    });
    @endif
</script>
@endpush
