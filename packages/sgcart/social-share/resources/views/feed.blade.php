@extends('layouts.store')

@section('title', 'Studio Feed — sgcart')

@section('content')
<div class="storefront-container py-8">
    <!-- Header -->
    <div class="text-center max-w-2xl mx-auto mb-12">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-650 border border-rose-100 dark:bg-rose-955/20 dark:text-rose-400 dark:border-rose-900/50 mb-3">
            <i class="fa-solid fa-fire fa-beat-field"></i> SGCart Studio
        </span>
        <h1 class="font-display font-extrabold text-3xl md:text-4xl text-slate-900 dark:text-white tracking-tight">Style Influencer Feed</h1>
        <p class="text-sm text-slate-500 dark:text-slate-450 mt-3 leading-relaxed">
            Get inspired by real customer looks! Discover styles, follow trendsetters, and shop the exact products they are wearing directly.
        </p>
    </div>

    <!-- Grid Feed -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @forelse($posts as $post)
            @php
                $isFollowing = false;
                if(auth('customer')->check()) {
                    $isFollowing = auth('customer')->user()->following->contains($post->customer_id);
                }
            @endphp
            <div class="bg-white dark:bg-[#151411] border border-[#e8e4df] dark:border-[#2e2c28] rounded-3xl overflow-hidden transition-all duration-300 hover:shadow-xl hover:-translate-y-1 flex flex-col h-full group">
                
                <!-- Influencer Info Header -->
                <div class="p-4 flex items-center justify-between border-b border-slate-55/60 dark:border-slate-800">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-850 flex items-center justify-center font-bold text-xs text-slate-650 overflow-hidden shrink-0 border border-slate-200 dark:border-slate-700">
                            @if($post->customer?->profile_picture)
                                <img src="{{ Storage::url($post->customer->profile_picture) }}" alt="Avatar" class="w-full h-full object-cover">
                            @else
                                {{ strtoupper(substr($post->customer?->name ?? '?', 0, 2)) }}
                            @endif
                        </div>
                        <div class="min-w-0">
                            <h3 class="font-display font-bold text-[13px] text-slate-800 dark:text-slate-200 truncate leading-tight">{{ $post->customer?->name ?? 'Influencer' }}</h3>
                            <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">Style Partner</p>
                        </div>
                    </div>

                    <!-- Follow Button -->
                    @if(auth('customer')->check() && auth('customer')->id() == $post->customer_id)
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">You</span>
                    @else
                        <button 
                            type="button" 
                            data-influencer-id="{{ $post->customer_id }}"
                            onclick="toggleFollow(this, '{{ $post->customer_id }}')" 
                            class="follow-btn px-3 py-1 rounded-full text-[10px] font-extrabold tracking-wider uppercase transition-all border {{ $isFollowing ? 'bg-slate-100 text-slate-750 border-slate-200 dark:bg-slate-800 dark:text-slate-350 dark:border-slate-700' : 'bg-rose-500 text-white border-rose-500 hover:bg-rose-600' }} cursor-pointer select-none"
                        >
                            {{ $isFollowing ? 'Following' : 'Follow' }}
                        </button>
                    @endif
                </div>

                <!-- Media Content -->
                <div class="relative aspect-[3/4] bg-slate-950 overflow-hidden cursor-pointer flex items-center justify-center group" onclick="openStudioModal({{ json_encode($post) }}, {{ $isFollowing ? 'true' : 'false' }}, '{{ auth('customer')->id() == $post->customer_id ? 'true' : 'false' }}')">
                    @if($post->media_type === 'video')
                        <video src="{{ Storage::url($post->media_path) }}" loop muted playsinline class="w-full h-full object-cover opacity-90 group-hover:scale-105 transition-all duration-500"></video>
                        <div class="absolute inset-0 bg-black/10 group-hover:bg-black/30 flex items-center justify-center transition-colors">
                            <span class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-md border border-white/30 flex items-center justify-center text-white text-lg scale-90 group-hover:scale-100 transition-all duration-300">
                                <i class="fa-solid fa-play ml-0.5"></i>
                            </span>
                        </div>
                    @else
                        <img src="{{ Storage::url($post->media_path) }}" alt="Look" class="w-full h-full object-cover group-hover:scale-105 transition-all duration-500">
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors"></div>
                    @endif

                    <!-- Caption Overlay (Subtle) -->
                    @if($post->caption)
                        <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent p-4 pt-10 text-white">
                            <p class="text-xs font-medium line-clamp-2 leading-relaxed opacity-90">{{ $post->caption }}</p>
                        </div>
                    @endif
                </div>

                <!-- Linked Product Banner -->
                @if($post->product)
                    <div class="p-3.5 bg-slate-50/50 dark:bg-[#1a1916]/50 border-t border-slate-100 dark:border-slate-800 flex items-center gap-3 mt-auto">
                        <div class="w-11 h-11 rounded-lg overflow-hidden shrink-0 border border-slate-200 dark:border-slate-850 bg-white">
                            <img src="{{ $post->product->image ? Storage::url($post->product->image) : '/images/placeholder.jpg' }}" alt="Product" class="w-full h-full object-cover">
                        </div>
                        <div class="min-w-0 flex-1">
                            <h4 class="font-semibold text-xs text-slate-800 dark:text-slate-200 truncate leading-snug">{{ $post->product->name }}</h4>
                            <p class="text-[11px] font-extrabold text-slate-950 dark:text-white mt-0.5">₹{{ number_format($post->product->price, 2) }}</p>
                        </div>
                        @if($post->shop_link)
                            <a href="{{ $post->shop_link }}" class="btn btn-primary px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-wider shrink-0 no-underline" style="display:inline-block; font-size:10px;">
                                Shop
                            </a>
                        @endif
                    </div>
                @else
                    <div class="p-3.5 bg-slate-50/30 dark:bg-[#1a1916]/30 border-t border-slate-100 dark:border-slate-800 text-center text-[11px] text-slate-400 italic mt-auto">
                        General inspiration look
                    </div>
                @endif

            </div>
        @empty
            <div class="col-span-full py-16 text-center text-slate-400 dark:text-slate-500">
                <i class="fa-solid fa-hashtag text-5xl mb-4 opacity-25 block"></i>
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200 mb-1">No Studio Posts Yet</h3>
                <p class="text-sm">Be the first to upload and share your look from your Account section!</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($posts->hasPages())
        <div class="mt-12 flex justify-center">
            {{ $posts->links() }}
        </div>
    @endif
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
                        <div id="modalInfluencerAvatar" class="w-10 h-10 rounded-full bg-slate-200 dark:bg-slate-850 flex items-center justify-center font-bold text-slate-650 overflow-hidden shrink-0 border border-slate-200 dark:border-slate-700">
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

@endsection

@section('scripts')
<script>
    function toggleFollow(btn, influencerId) {
        if (!btn) return;
        
        // Disable button to prevent double-clicks
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
                        b.className = 'follow-btn px-3 py-1 rounded-full text-[10px] font-extrabold tracking-wider uppercase transition-all border bg-slate-100 text-slate-750 border-slate-200 dark:bg-slate-800 dark:text-slate-350 dark:border-slate-700 cursor-pointer select-none';
                    } else {
                        b.textContent = 'Follow';
                        b.className = 'follow-btn px-3 py-1 rounded-full text-[10px] font-extrabold tracking-wider uppercase transition-all border bg-rose-500 text-white border-rose-500 hover:bg-rose-600 cursor-pointer select-none';
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
    document.getElementById('studioModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeStudioModal();
        }
    });
</script>
@endsection
