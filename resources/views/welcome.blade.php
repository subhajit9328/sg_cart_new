@extends('layouts.store')

@section('title', 'SGCart — Premium Fashion Storefront')

@section('content')


<!-- HERO SECTION -->
<div class="hero">
    <div class="hero-left">
        <div class="absolute w-80 h-80 rounded-full bg-blue-500/10 blur-3xl -top-10 -left-10"></div>
        <div class="hero-eyebrow">Spring Collection 2026</div>
        <h1 class="hero-title">Elevate Your<br>Standard <em>Style</em></h1>
        <p class="hero-sub">Discover high-quality linen shirts, structured accessories, and lightweight knitwear engineered for maximum ease and durability.</p>
        <div class="hero-actions">
            <a href="{{ route('store.shop') }}" class="btn btn-accent">Shop The Drop <i class="fa-solid fa-arrow-right"></i></a>
            <a href="{{ route('store.shop', ['category' => 'Women']) }}" class="btn btn-ghost">View Editorial</a>
        </div>
    </div>
    <div class="hero-right">
        <img src="https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=1200&auto=format&fit=crop&q=80" alt="Model wear in linen"/>
        <div class="hero-overlay-tag">
            <p class="tag-num">100%</p>
            <p class="tag-label">Organic Cotton</p>
        </div>
    </div>
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
                <a href="{{ route('store.shop', ['category' => $cat]) }}" class="cat-card">
                    @php
                        $catImgs = [
                            'Women' => 'https://images.unsplash.com/photo-1509631179647-0177331693ae?w=400&auto=format&fit=crop&q=80',
                            'Men' => 'https://images.unsplash.com/photo-1492562080023-ab3db95bfbce?w=400&auto=format&fit=crop&q=80',
                            'Accessories' => 'https://images.unsplash.com/photo-1509319117193-57bab727e09d?w=400&auto=format&fit=crop&q=80',
                            'Footwear' => 'https://images.unsplash.com/photo-1549298916-b41d501d3772?w=400&auto=format&fit=crop&q=80',
                            'Beauty' => 'https://images.unsplash.com/photo-1608248597279-f99d160bfcbc?w=400&auto=format&fit=crop&q=80',
                            'Kids' => 'https://images.unsplash.com/photo-1519457431-44ccd64a579b?w=400&auto=format&fit=crop&q=80'
                        ];
                    @endphp
                    <img src="{{ $catImgs[$cat] ?? 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400&auto=format&fit=crop&q=80' }}" alt="{{ $cat }}"/>
                    <div class="cat-card-label">
                        <p>{{ $cat }}</p>
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
                        <div class="product-card-overlay">
                            <button onclick="event.stopPropagation(); window.location.href='{{ route('store.product', $product['slug']) }}'" class="btn btn-primary btn-sm w-full"><i class="fa-solid fa-cart-shopping"></i> Quick Buy</button>
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
            @endforeach
        </div>
    </div>
</div>
@endsection
