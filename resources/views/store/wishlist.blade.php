@extends('layouts.store')

@section('title', 'My Wishlist — sgcart')

@section('content')
<div class="storefront-container" id="tab-wishlist">
    <div class="bg-white border border-[#e8e4df] rounded-2xl p-5 md:p-6" style="margin-top: 2rem;">
        <h2 class="font-display font-bold text-base text-slate-900 mb-4 border-b border-slate-100 pb-2 flex items-center gap-2.5">
            <i class="fa-solid fa-heart text-accent text-sm"></i> My Wishlist
        </h2>
        
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
</div>
@endsection
