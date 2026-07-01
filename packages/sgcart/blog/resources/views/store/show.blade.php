@extends('layouts.store')

@section('title', ($post->meta_title ?? $post->title) . ' — SGCart Blog')

@if($post->meta_description)
    @section('meta_description', $post->meta_description)
@endif

@section('content')
<div class="w-full max-w-none px-4 sm:px-8 lg:px-16 py-10">
       <!-- Combined Header Navigation: Back Button and Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 border-b border-slate-100 dark:border-slate-800/60 pb-4">
        <!-- Back Button (Restored premium box design) -->
        <a href="/blog" class="inline-flex items-center gap-2.5 px-4 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-350 text-xs sm:text-sm font-bold shadow-sm hover:bg-accent hover:border-accent hover:text-white hover:shadow-md transition-all duration-300 group" style="text-decoration: none;">
            <i class="fa-solid fa-arrow-left text-[10px] group-hover:-translate-x-1 transition-transform"></i> Back to SGCart Blog
        </a>

        <!-- Breadcrumbs -->
        <nav class="flex items-center space-x-2 text-xs text-slate-400 dark:text-slate-500 overflow-x-auto whitespace-nowrap scrollbar-none" aria-label="Breadcrumb">
            <a href="/" class="hover:text-accent transition-colors flex items-center gap-1"><i class="fa-solid fa-house"></i> Home</a>
            <i class="fa-solid fa-chevron-right text-[8px] opacity-60"></i>
            <a href="/blog" class="hover:text-accent transition-colors">Blog</a>
            <i class="fa-solid fa-chevron-right text-[8px] opacity-60"></i>
            <span class="text-slate-600 dark:text-slate-350 font-medium truncate max-w-[240px]">{{ $post->title }}</span>
        </nav>
    </div>

    <!-- Premium Article Hero Banner Header -->
    <header class="text-center max-w-4xl mx-auto px-4 mb-8">
        <h1 class="font-display text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-[1.2] mb-5 font-sans">
            {{ $post->title }}
        </h1>
        
        <!-- Author Profile/Meta info (Compact and Elegant) -->
        <div class="flex items-center justify-center flex-wrap gap-x-4 gap-y-1.5 text-xs text-slate-500 dark:text-slate-400">
            @php
                $wordCount = str_word_count(strip_tags($post->content));
                $readTime = max(1, ceil($wordCount / 200));
                $authorName = $post->author->name ?? 'Admin';
                $publishDate = ($post->published_at ?? $post->created_at)->format('M d, Y');
            @endphp
            <span>By <strong class="text-slate-700 dark:text-slate-350 font-semibold">{{ $authorName }}</strong></span>
            <span class="text-slate-300 dark:text-slate-700">•</span>
            <span class="flex items-center gap-1.5"><i class="fa-regular fa-calendar-days text-accent/80"></i> {{ $publishDate }}</span>
            <span class="text-slate-300 dark:text-slate-700">•</span>
            <span class="flex items-center gap-1.5"><i class="fa-regular fa-clock text-accent/80"></i> {{ $readTime }} min read</span>
        </div>
    </header>

    <!-- Main Content Layout (Centered with Left Floating share bar, NO grid displacement) -->
    <div class="relative w-full max-w-5xl mx-auto">
        
        <!-- Left Floating Share Bar (Desktop Only - positioned absolutely to the left of the main content card) -->
        <div class="hidden lg:flex flex-col items-center absolute -left-20 top-24 gap-4">
            <span class="text-[9px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500 vertical-text mb-2">Share Article</span>
            
            @php
                $shareUrl = urlencode(request()->url());
                $shareTitle = urlencode($post->title);
            @endphp
            <a href="https://twitter.com/intent/tweet?text={{ $shareTitle }}&url={{ $shareUrl }}" target="_blank" class="w-11 h-11 rounded-full flex items-center justify-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-500 hover:text-sky-500 hover:border-sky-200 hover:bg-sky-50/20 hover:shadow-md transition-all duration-300" title="Share on Twitter / X">
                <i class="fa-brands fa-x-twitter text-sm"></i>
            </a>
            
            <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank" class="w-11 h-11 rounded-full flex items-center justify-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-500 hover:text-blue-600 hover:border-blue-200 hover:bg-blue-50/20 hover:shadow-md transition-all duration-300" title="Share on Facebook">
                <i class="fa-brands fa-facebook-f text-sm"></i>
            </a>
            
            <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ $shareUrl }}&title={{ $shareTitle }}" target="_blank" class="w-11 h-11 rounded-full flex items-center justify-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-500 hover:text-blue-750 hover:border-blue-200 hover:bg-blue-50/20 hover:shadow-md transition-all duration-300" title="Share on LinkedIn">
                <i class="fa-brands fa-linkedin-in text-sm"></i>
            </a>
            
            <button onclick="copyLinkToClipboard()" class="w-11 h-11 rounded-full flex items-center justify-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-500 hover:text-emerald-600 hover:border-emerald-200 hover:bg-emerald-50/20 hover:shadow-md transition-all duration-300 cursor-pointer" title="Copy Link">
                <i class="fa-solid fa-link text-sm"></i>
            </button>
        </div>

        <!-- Main Content Area (Centered) -->
        <main class="bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border border-slate-200/50 dark:border-slate-800/60 rounded-3xl p-5 sm:p-8 md:p-10 shadow-lg relative">
            <article>
                <!-- Featured Image -->
                @if($post->featured_image)
                    <div class="group relative rounded-2xl overflow-hidden mb-12 border border-slate-250/40 dark:border-slate-800/40 shadow-lg aspect-[21/9] max-h-[520px] bg-slate-100 dark:bg-slate-950">
                        <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-101 transition-transform duration-700 ease-out">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-30 group-hover:opacity-40 transition-opacity duration-300"></div>
                    </div>
                @endif

                <!-- Post Content Body (Premium Editorial Styling) -->
                <div class="blog-content-body max-w-4xl mx-auto text-slate-800 dark:text-slate-200 text-[16px] sm:text-[18px] leading-[1.8] space-y-6 font-normal">
                    {!! $post->content !!}
                </div>

                <!-- Action Bar & Tags -->
                <div class="mt-14 pt-8 border-t border-slate-200/60 dark:border-slate-800/80 flex flex-col sm:flex-row sm:items-center sm:justify-end gap-6 lg:hidden">
                    <!-- Mobile Share Options -->
                    <div class="flex items-center gap-3 lg:hidden">
                        <span class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mr-1">Share Article:</span>
                        
                        <a href="https://twitter.com/intent/tweet?text={{ $shareTitle }}&url={{ $shareUrl }}" target="_blank" class="w-9 h-9 rounded-full flex items-center justify-center bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-sky-500 hover:bg-sky-50 dark:hover:bg-sky-950/30 transition-all duration-300">
                            <i class="fa-brands fa-x-twitter text-xs"></i>
                        </a>
                        
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank" class="w-9 h-9 rounded-full flex items-center justify-center bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-950/30 transition-all duration-300">
                            <i class="fa-brands fa-facebook-f text-xs"></i>
                        </a>
                        
                        <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ $shareUrl }}&title={{ $shareTitle }}" target="_blank" class="w-9 h-9 rounded-full flex items-center justify-center bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-blue-700 hover:bg-blue-50 dark:hover:bg-blue-955/30 transition-all duration-300">
                            <i class="fa-brands fa-linkedin-in text-xs"></i>
                        </a>
                        
                        <button onclick="copyLinkToClipboard()" class="w-9 h-9 rounded-full flex items-center justify-center bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 transition-all duration-300 cursor-pointer">
                            <i class="fa-solid fa-link text-xs"></i>
                        </button>
                    </div>
                </div>
            </article>

            <!-- Linked Products (Blade) / Related Products Fallback (JS) -->
            @if($post->products->isNotEmpty())
                <div class="mt-16 pt-10 border-t border-slate-200/60 dark:border-slate-800/80">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <span class="text-xs font-bold text-accent uppercase tracking-widest">Featured Collection</span>
                            <h3 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white mt-1">Featured Shop Items</h3>
                        </div>
                        <a href="/shop" class="text-sm font-semibold text-accent hover:text-accent/80 transition-colors flex items-center gap-1">
                            Shop All <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        @foreach($post->products as $prod)
                            @php
                                $price = number_format($prod->sale_price ?? $prod->price, 2);
                                $hasSale = $prod->sale_price !== null;
                                $img = $prod->image ? Storage::url($prod->image) : asset('images/no-image.svg');
                            @endphp
                            <div class="bg-slate-50 dark:bg-slate-955 border border-slate-200/40 dark:border-slate-800/50 rounded-xl p-3 shadow-sm hover:shadow-md transition-all duration-300 group flex flex-col justify-between">
                                <a href="{{ route('store.product', $prod->slug) }}" class="block aspect-square overflow-hidden rounded-lg bg-white mb-3 relative border border-slate-100 dark:border-slate-900">
                                    <img src="{{ $img }}" alt="{{ $prod->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                    @if($hasSale)
                                        <span class="absolute top-2 left-2 bg-rose-500 text-white text-[10px] font-extrabold uppercase px-1.5 py-0.5 rounded shadow">Sale</span>
                                    @endif
                                </a>
                                <div>
                                    <h4 class="font-bold text-sm text-slate-800 dark:text-slate-200 line-clamp-1 group-hover:text-accent transition-colors mb-1">
                                        <a href="{{ route('store.product', $prod->slug) }}">{{ $prod->name }}</a>
                                    </h4>
                                    <div class="flex items-center gap-2 mb-2.5">
                                        <span class="text-sm font-extrabold text-slate-900 dark:text-white">₹{{ $price }}</span>
                                    </div>
                                </div>
                                <a href="{{ route('store.product', $prod->slug) }}" class="w-full py-2 bg-white dark:bg-slate-900 hover:bg-slate-900 hover:text-white dark:hover:bg-white dark:hover:text-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold text-center rounded-lg transition-all duration-300 block">
                                    Shop Now
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <!-- Fallback: Dynamic related products loaded via JS -->
                <div id="relatedProductsSection" class="mt-16 pt-10 border-t border-slate-200/60 dark:border-slate-800/80 hidden">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <span class="text-xs font-bold text-accent uppercase tracking-widest">Store Collection</span>
                            <h3 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white mt-1">Featured Shop Favorites</h3>
                        </div>
                        <a href="/shop" class="text-sm font-semibold text-accent hover:text-accent/80 transition-colors flex items-center gap-1">
                            Shop All <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                    
                    <div id="relatedProductsGrid" class="grid grid-cols-1 sm:grid-cols-3 gap-6"></div>
                </div>
            @endif
        </main>

    </div>
</div>

<!-- Extra typography overrides -->
<style>
    .vertical-text {
        writing-mode: vertical-lr;
        text-orientation: mixed;
    }
    

    
    .blog-content-body p {
        margin-bottom: 2rem;
        line-height: 1.88;
        letter-spacing: -0.003em;
    }
    
    .blog-content-body h2 {
        font-family: 'Montserrat', sans-serif;
        font-weight: 800;
        font-size: 1.875rem; /* 30px */
        line-height: 1.35;
        margin-top: 3.5rem;
        margin-bottom: 1.5rem;
        color: #0f172a;
        letter-spacing: -0.020em;
        position: relative;
    }
    .dark .blog-content-body h2 {
        color: #f8fafc;
    }
    .blog-content-body h2::after {
        content: '';
        display: block;
        width: 44px;
        height: 4px;
        background-color: var(--color-accent, #c8a97e);
        border-radius: 2px;
        margin-top: 0.75rem;
    }
    
    .blog-content-body h3 {
        font-family: 'Montserrat', sans-serif;
        font-weight: 750;
        font-size: 1.45rem;
        line-height: 1.4;
        margin-top: 2.75rem;
        margin-bottom: 1.15rem;
        color: #1e293b;
        letter-spacing: -0.015em;
        position: relative;
    }
    .dark .blog-content-body h3 {
        color: #f1f5f9;
    }
    .blog-content-body h3::after {
        content: '';
        display: block;
        width: 32px;
        height: 3px;
        background-color: var(--color-accent, #c8a97e);
        border-radius: 1.5px;
        margin-top: 0.5rem;
    }

    .blog-content-body a {
        color: var(--color-accent, #c8a97e);
        text-decoration: underline;
        text-decoration-thickness: 1.5px;
        text-underline-offset: 4px;
        font-weight: 700;
        transition: color 0.2s ease;
    }
    .blog-content-body a:hover {
        color: #b09168;
    }
    
    .blog-content-body ul {
        list-style-type: none;
        padding-left: 0;
        margin-bottom: 2rem;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }
    .blog-content-body ul li {
        position: relative;
        padding-left: 1.75rem;
        line-height: 1.8;
    }
    .blog-content-body ul li::before {
        content: "";
        position: absolute;
        left: 0.35rem;
        top: 0.65rem;
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background-color: var(--color-accent, #c8a97e);
        box-shadow: 0 0 4px rgba(200, 169, 126, 0.4);
    }
    
    .blog-content-body ol {
        list-style-type: none;
        counter-reset: li;
        padding-left: 0;
        margin-bottom: 2rem;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }
    .blog-content-body ol li {
        position: relative;
        padding-left: 2.25rem;
        line-height: 1.8;
    }
    .blog-content-body ol li::before {
        content: counter(li);
        counter-increment: li;
        position: absolute;
        left: 0;
        top: 0.15rem;
        width: 1.35rem;
        height: 1.35rem;
        border-radius: 50%;
        background-color: rgba(200, 169, 126, 0.12);
        border: 1px solid rgba(200, 169, 126, 0.25);
        color: var(--color-accent, #c8a97e);
        font-size: 0.75rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .blog-content-body blockquote {
        position: relative;
        border-left: 4px solid var(--color-accent, #c8a97e);
        background-color: rgba(200, 169, 126, 0.05);
        padding: 2.25rem 2.5rem;
        font-style: italic;
        color: #334155;
        font-size: 1.25rem;
        line-height: 1.85;
        border-radius: 0 1rem 1rem 0;
        margin: 2.5rem 0;
        box-shadow: inset 0 2px 8px rgba(0,0,0,0.01);
    }
    .dark .blog-content-body blockquote {
        color: #cbd5e1;
        background-color: rgba(200, 169, 126, 0.02);
    }
    .blog-content-body blockquote::before {
        content: "“";
        font-family: 'Montserrat', sans-serif;
        font-size: 4.5rem;
        color: var(--color-accent, #c8a97e);
        opacity: 0.2;
        position: absolute;
        top: -0.85rem;
        left: 0.75rem;
        line-height: 1;
    }
    
    .blog-content-body img {
        max-width: 100%;
        height: auto;
        border-radius: 1rem;
        margin: 3rem auto;
        display: block;
        box-shadow: 0 15px 35px -15px rgba(0,0,0,0.15);
        border: 1px solid rgba(0,0,0,0.05);
    }
    .dark .blog-content-body img {
        border-color: rgba(255,255,255,0.05);
        box-shadow: 0 15px 35px -15px rgba(0,0,0,0.4);
    }

    .blog-content-body code {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 0.85em;
        background-color: #f1f5f9;
        color: #0f172a;
        padding: 0.25rem 0.5rem;
        border-radius: 0.35rem;
        border: 1px solid #e2e8f0;
        font-weight: 550;
    }
    .dark .blog-content-body code {
        background-color: #1e293b;
        color: #f8fafc;
        border-color: #334155;
    }
    
    .blog-content-body pre {
        background-color: #f1f5f9;
        padding: 1.25rem 1.5rem;
        border-radius: 0.75rem;
        overflow-x: auto;
        margin-bottom: 2rem;
        border: 1px solid #e2e8f0;
    }
    .dark .blog-content-body pre {
        background-color: #1e293b;
        border-color: #334155;
    }
    .blog-content-body pre code {
        background-color: transparent;
        padding: 0;
        border-radius: 0;
        border: none;
        color: inherit;
        font-size: 0.9em;
    }
</style>

<script>
    function copyLinkToClipboard() {
        navigator.clipboard.writeText(window.location.href).then(() => {
            showToast('Article link copied to clipboard!', 'success');
        }, (err) => {
            console.error('Could not copy link: ', err);
            showToast('Failed to copy link.', 'error');
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const fallbackGrid = document.getElementById('relatedProductsGrid');
        if (fallbackGrid) {
            loadRelatedProducts();
        }
    });

    async function loadRelatedProducts() {
        try {
            const response = await fetch('/search-live?q=', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await response.json();
            
            if (Array.isArray(data) && data.length > 0) {
                document.getElementById('relatedProductsSection').classList.remove('hidden');
                const container = document.getElementById('relatedProductsGrid');
                container.innerHTML = '';
                
                data.slice(0, 3).forEach(prod => {
                    const price = parseFloat(prod.price).toFixed(2);
                    const hasSale = prod.badge && prod.badge.toLowerCase() === 'sale';
                    const badgeHtml = hasSale ? `<span class="absolute top-2 left-2 bg-rose-500 text-white text-[10px] font-extrabold uppercase px-1.5 py-0.5 rounded shadow">Sale</span>` : '';
                    
                    container.innerHTML += `
                        <div class="bg-slate-50 dark:bg-slate-955 border border-slate-200/40 dark:border-slate-800/50 rounded-xl p-3 shadow-sm hover:shadow-md transition-all duration-300 group flex flex-col justify-between">
                            <a href="${prod.url}" class="block aspect-square overflow-hidden rounded-lg bg-white mb-3 relative border border-slate-100 dark:border-slate-900">
                                <img src="${prod.img}" alt="${prod.name}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                ${badgeHtml}
                            </a>
                            <div>
                                <h4 class="font-bold text-sm text-slate-800 dark:text-slate-200 line-clamp-1 group-hover:text-accent transition-colors mb-1">
                                    <a href="${prod.url}">${prod.name}</a>
                                </h4>
                                <div class="flex items-center gap-2 mb-2.5">
                                    <span class="text-sm font-extrabold text-slate-900 dark:text-white">₹${price}</span>
                                </div>
                            </div>
                            <a href="${prod.url}" class="w-full py-2 bg-white dark:bg-slate-900 hover:bg-slate-900 hover:text-white dark:hover:bg-white dark:hover:text-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold text-center rounded-lg transition-all duration-300 block">
                                Shop Now
                            </a>
                        </div>
                    `;
                });
            }
        } catch (error) {
            console.error('Error loading related products:', error);
        }
    }
</script>
@endsection
