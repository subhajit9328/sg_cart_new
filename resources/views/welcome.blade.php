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
                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($img->image_path) }}" alt="Hero Collection Slide"/>
                                    </a>
                                @else
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($img->image_path) }}" alt="Hero Collection Slide"/>
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
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($firstImg->image_path) }}" alt="Hero Collection Slide"/>
                        </a>
                    @else
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($firstImg->image_path) }}" alt="Hero Collection Slide"/>
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

        function stopAutoPlay() {
            clearInterval(autoPlayInterval);
        }

        dots.forEach((dot) => {
            dot.addEventListener('click', () => {
                const index = parseInt(dot.getAttribute('data-index'));
                showSlide(index);
                stopAutoPlay();
                startAutoPlay();
            });
        });

        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                showSlide(currentIndex - 1);
                stopAutoPlay();
                startAutoPlay();
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                showSlide(currentIndex + 1);
                stopAutoPlay();
                startAutoPlay();
            });
        }

        // Initialize
        showSlide(0);
        startAutoPlay();
    });
</script>
@endpush
