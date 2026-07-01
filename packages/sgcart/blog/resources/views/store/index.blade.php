@extends('layouts.store')

@section('title', isset($category) ? $category->name . ' — SGCart Blog' : 'SGCart Blog')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Header banner -->
    <div class="text-center max-w-3xl mx-auto mt-6 mb-12">
        <span id="journalBadge" class="text-xs font-bold text-accent uppercase tracking-widest bg-accent/15 px-3.5 py-1.5 rounded-full border border-accent/20">
            {{ isset($category) ? $category->name . ' Archives' : 'SGCart Blog' }}
        </span>
        <h1 id="journalTitle" class="font-display text-3xl sm:text-4xl md:text-5xl font-extrabold tracking-tight text-slate-900 dark:text-white mt-4 mb-4">
            {{ isset($category) ? $category->name : 'Style Lookbooks, Shopping Tips & Trends' }}
        </h1>
        <p id="journalDescription" class="text-slate-550 dark:text-slate-455 text-sm sm:text-base leading-relaxed">
            {{ isset($category) ? ($category->description ?? '') : 'Explore style lookbooks, exclusive coupon deals, fabric spotlights, and blog posts designed to elevate your checkout experience.' }}
        </p>
        
        <!-- Search bar centered with Autocomplete -->
        <div class="mt-8 max-w-md mx-auto relative">
            <form id="blogSearchForm" class="relative" onsubmit="handleSearchSubmit(event)" autocomplete="off">
                <input type="text" id="blogSearchInput" placeholder="Search blog posts, shopping tips, coupons..." 
                    class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl py-3.5 pl-5 pr-12 text-sm placeholder:text-slate-400 outline-none focus:border-accent focus:ring-1 focus:ring-accent text-slate-800 dark:text-slate-100 shadow-sm transition-all duration-300">
                <button type="submit" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-accent transition-colors">
                    <i class="fa-solid fa-magnifying-glass text-sm"></i>
                </button>
            </form>
            
            <!-- Autocomplete Dropdown -->
            <div id="blogSearchDropdown" class="absolute left-0 right-0 mt-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl z-30 hidden overflow-hidden transition-all duration-200 max-h-80 overflow-y-auto"></div>
        </div>
    </div>

    <!-- Category Filter Bar (Modern Pills) -->
    <div id="categoryContainer" class="flex items-center gap-3 overflow-x-auto pb-4 mb-10 no-scrollbar scroll-smooth whitespace-nowrap">
        <div class="h-8 bg-slate-200 dark:bg-slate-800 rounded-full w-24 animate-pulse inline-block"></div>
        <div class="h-8 bg-slate-200 dark:bg-slate-800 rounded-full w-32 animate-pulse inline-block"></div>
        <div class="h-8 bg-slate-200 dark:bg-slate-800 rounded-full w-28 animate-pulse inline-block"></div>
        <div class="h-8 bg-slate-200 dark:bg-slate-800 rounded-full w-20 animate-pulse inline-block"></div>
        <div class="h-8 bg-slate-200 dark:bg-slate-800 rounded-full w-36 animate-pulse inline-block"></div>
        <div class="h-8 bg-slate-200 dark:bg-slate-800 rounded-full w-24 animate-pulse inline-block"></div>
    </div>

    <!-- Active Search Filter Badge -->
    <div id="searchBadgeContainer" class="mb-8 hidden">
        <span class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold bg-accent/10 text-accent border border-accent/20">
            <span>Search results for: <strong class="underline" id="searchBadgeText">""</strong></span>
            <button onclick="clearSearchFilter()" class="text-accent/60 hover:text-accent ml-1 transition-colors border-none bg-transparent cursor-pointer"><i class="fa-solid fa-xmark"></i></button>
        </span>
    </div>

    <!-- Articles Grid -->
    <div id="postsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-10">
        <!-- Skeleton Cards -->
        @for($i = 0; $i < 3; $i++)
            <div class="animate-pulse bg-white dark:bg-slate-900 border border-slate-200/50 dark:border-slate-800/80 rounded-2xl overflow-hidden shadow-sm">
                <div class="bg-slate-200 dark:bg-slate-800 aspect-[16/10]"></div>
                <div class="p-6 space-y-4">
                    <div class="h-3 bg-slate-200 dark:bg-slate-800 rounded w-1/4"></div>
                    <div class="h-5 bg-slate-200 dark:bg-slate-800 rounded w-3/4"></div>
                    <div class="h-4 bg-slate-200 dark:bg-slate-800 rounded w-5/6"></div>
                    <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-between">
                        <div class="h-4 bg-slate-200 dark:bg-slate-800 rounded w-1/3"></div>
                        <div class="h-4 bg-slate-200 dark:bg-slate-800 rounded w-1/4"></div>
                    </div>
                </div>
            </div>
        @endfor
    </div>

    <!-- Pagination Container -->
    <div id="paginationContainer" class="mt-16 flex items-center justify-center gap-2 hidden"></div>
</div>
@endsection

<style>
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    @keyframes cardFadeIn {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .card-animate {
        animation: cardFadeIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
</style>

<script>
    // Router State
    let searchQuery = new URLSearchParams(window.location.search).get('search') || '';
    let activeCategorySlug = "{{ isset($category) ? $category->slug : '' }}"; 
    let currentPage = 1;
    let lastPage = 1;
    let isFetching = false;
    let debounceTimer;
    let isInitialRender = true;

    document.addEventListener('DOMContentLoaded', () => {
        // Bind search suggestion events
        const searchInput = document.getElementById('blogSearchInput');
        if (searchInput) {
            searchInput.value = searchQuery;
            searchInput.value = searchQuery;
            searchInput.addEventListener('input', handleSearchAutocomplete);
        }

        // Dropdown auto-hide
        document.addEventListener('click', (e) => {
            const dropdown = document.getElementById('blogSearchDropdown');
            const searchForm = document.getElementById('blogSearchForm');
            if (dropdown && !searchForm.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });

        // Intercept local category links to enable fast SPA filtering
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a');
            if (link && link.href) {
                const url = new URL(link.href);
                if (url.origin === window.location.origin && url.pathname.startsWith('/blog/category/')) {
                    e.preventDefault();
                    window.history.pushState({}, '', link.href);
                    handleNavigation();
                } else if (url.origin === window.location.origin && url.pathname === '/blog') {
                    e.preventDefault();
                    window.history.pushState({}, '', link.href);
                    handleNavigation();
                }
            }
        });

        // Initial Routing Load
        loadBlogData(1, searchQuery, activeCategorySlug, false);
    });

    // Popstate event for back/forward navigation
    window.addEventListener('popstate', handleNavigation);

    function handleNavigation() {
        const path = window.location.pathname;
        const urlParams = new URLSearchParams(window.location.search);
        searchQuery = urlParams.get('search') || '';
        
        const mainSearchInput = document.getElementById('blogSearchInput');
        if (mainSearchInput) mainSearchInput.value = searchQuery;

        const dropdown = document.getElementById('blogSearchDropdown');
        if (dropdown) dropdown.classList.add('hidden');

        if (path === '/blog' || path === '/blog/') {
            activeCategorySlug = '';
            setActiveCategoryPill('');
            loadBlogData(1, searchQuery, '', false);
        } else if (path.includes('/blog/category/')) {
            activeCategorySlug = path.split('/category/')[1] || '';
            setActiveCategoryPill(activeCategorySlug);
            loadBlogData(1, searchQuery, activeCategorySlug, false);
        }
    }

    // Auto-suggest suggestions
    function handleSearchAutocomplete() {
        const query = document.getElementById('blogSearchInput').value.trim();
        const dropdown = document.getElementById('blogSearchDropdown');

        clearTimeout(debounceTimer);

        if (query.length < 2) {
            dropdown.innerHTML = '';
            dropdown.classList.add('hidden');
            return;
        }

        dropdown.classList.remove('hidden');
        dropdown.innerHTML = `
            <div class="py-4 text-center text-slate-400">
                <i class="fa-solid fa-circle-notch fa-spin text-accent text-sm mr-2"></i>
                <span class="text-xs">Searching blog posts...</span>
            </div>
        `;

        debounceTimer = setTimeout(async () => {
            try {
                const response = await fetch(`/api/blog?search=${encodeURIComponent(query)}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const result = await response.json();
                
                if (result.success && result.data.posts.data.length > 0) {
                    let html = '<div class="flex flex-col">';
                    result.data.posts.data.slice(0, 5).forEach(post => {
                        const img = post.featured_image_url 
                            ? `<img src="${post.featured_image_url}" class="w-8 h-8 rounded object-cover shadow-sm">`
                            : `<div class="w-8 h-8 rounded bg-slate-105 dark:bg-slate-800 flex items-center justify-center text-slate-400"><i class="fa-solid fa-newspaper text-xs"></i></div>`;
                        
                        html += `
                            <a href="/blog/${post.slug}" class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-800 border-b border-slate-100 dark:border-slate-800 last:border-0 transition-colors">
                                ${img}
                                <div class="min-w-0">
                                    <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate leading-snug">${post.title}</h4>
                                    <span class="text-[10px] text-slate-400">${post.category ? post.category.name : 'Articles'}</span>
                                </div>
                            </a>
                        `;
                    });
                    html += '</div>';
                    dropdown.innerHTML = html;
                } else {
                    dropdown.innerHTML = `
                        <div class="py-4 text-center text-slate-550 dark:text-slate-400 text-xs">
                            No blog posts found for "${query}"
                        </div>
                    `;
                }
            } catch (error) {
                console.error(error);
                dropdown.classList.add('hidden');
            }
        }, 300);
    }

    function handleSearchSubmit(event) {
        event.preventDefault();
        const dropdown = document.getElementById('blogSearchDropdown');
        if (dropdown) dropdown.classList.add('hidden');

        const mainInput = document.getElementById('blogSearchInput');
        if (mainInput) {
            searchQuery = mainInput.value.trim();
        }

        currentPage = 1;
        updateUrlState();
        loadBlogData(currentPage, searchQuery, activeCategorySlug, false);
    }

    function clearSearchFilter() {
        searchQuery = '';
        const searchInput = document.getElementById('blogSearchInput');
        if (searchInput) searchInput.value = '';
        
        currentPage = 1;
        updateUrlState();
        loadBlogData(currentPage, searchQuery, activeCategorySlug, false);
    }

    function selectCategory(slug) {
        activeCategorySlug = slug;
        currentPage = 1;
        updateUrlState();
        loadBlogData(currentPage, searchQuery, activeCategorySlug, false);
    }

    // Removed loadNextPage

    function updateUrlState() {
        const url = new URL(window.location);
        
        if (activeCategorySlug) {
            url.pathname = `/blog/category/${activeCategorySlug}`;
        } else {
            url.pathname = '/blog';
        }

        if (searchQuery) {
            url.searchParams.set('search', searchQuery);
        } else {
            url.searchParams.delete('search');
        }
        
        url.searchParams.delete('page');
        window.history.pushState({}, '', url);
    }

    async function loadBlogData(page, search, categorySlug, append = false) {
        if (isFetching) return;
        isFetching = true;
        
        showLoadingState();

        try {
            let fetchUrl = categorySlug 
                ? `/api/blog/category/${categorySlug}?page=${page}&search=${encodeURIComponent(search)}`
                : `/api/blog?page=${page}&search=${encodeURIComponent(search)}`;

            const response = await fetch(fetchUrl, {
                headers: { 'Accept': 'application/json' }
            });
            const result = await response.json();
            
            if (result.success) {
                const data = result.data;
                currentPage = data.posts.current_page;
                lastPage = data.posts.last_page;

                updateHeaderLayout(data.category || null);
                renderCategories(data.categories, data.category ? data.category.id : null);
                renderPosts(data.posts.data, append);
                renderPagination();
                toggleSearchBadge(search);
            } else {
                renderErrorState('Failed to fetch articles.');
            }
        } catch (error) {
            console.error('Error fetching blog data:', error);
            renderErrorState('An error occurred while loading posts.');
        } finally {
            isFetching = false;
        }
    }

    function showLoadingState() {
        const grid = document.getElementById('postsGrid');
        grid.innerHTML = '';
        for (let i = 0; i < 3; i++) {
            grid.innerHTML += `
                <div class="animate-pulse bg-white dark:bg-slate-900 border border-slate-200/50 dark:border-slate-800/80 rounded-2xl overflow-hidden shadow-sm">
                    <div class="bg-slate-200 dark:bg-slate-800 aspect-[16/10]"></div>
                    <div class="p-6 space-y-4">
                        <div class="h-3 bg-slate-200 dark:bg-slate-800 rounded w-1/4"></div>
                        <div class="h-5 bg-slate-200 dark:bg-slate-800 rounded w-3/4"></div>
                        <div class="h-4 bg-slate-200 dark:bg-slate-800 rounded w-5/6"></div>
                        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-between">
                            <div class="h-4 bg-slate-200 dark:bg-slate-800 rounded w-1/3"></div>
                            <div class="h-4 bg-slate-200 dark:bg-slate-800 rounded w-1/4"></div>
                        </div>
                    </div>
                </div>
            `;
        }
    }

    function renderPagination() {
        const container = document.getElementById('paginationContainer');
        if (!container) return;

        if (lastPage <= 1) {
            container.classList.add('hidden');
            return;
        }

        container.classList.remove('hidden');
        container.innerHTML = '';

        // Previous Button
        const prevBtn = document.createElement('button');
        prevBtn.type = 'button';
        prevBtn.disabled = currentPage === 1;
        prevBtn.className = `w-10 h-10 rounded-xl flex items-center justify-center border transition-all ${currentPage === 1 ? 'border-slate-100 dark:border-slate-800 text-slate-350 dark:text-slate-700 cursor-not-allowed bg-slate-50 dark:bg-slate-900/50' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-350 hover:border-accent hover:text-accent bg-white dark:bg-slate-900 cursor-pointer shadow-sm'}`;
        prevBtn.innerHTML = '<i class="fa-solid fa-chevron-left text-xs"></i>';
        prevBtn.onclick = () => {
            if (currentPage > 1) {
                currentPage--;
                loadBlogData(currentPage, searchQuery, activeCategorySlug, false);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        };
        container.appendChild(prevBtn);

        // Page Numbers
        const maxVisiblePages = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
        let endPage = Math.min(lastPage, startPage + maxVisiblePages - 1);

        if (endPage - startPage + 1 < maxVisiblePages) {
            startPage = Math.max(1, endPage - maxVisiblePages + 1);
        }

        if (startPage > 1) {
            const firstPageBtn = createPageBtn(1);
            container.appendChild(firstPageBtn);
            if (startPage > 2) {
                const ellipsis = document.createElement('span');
                ellipsis.className = 'text-slate-400 dark:text-slate-600 px-1.5 text-sm select-none';
                ellipsis.textContent = '...';
                container.appendChild(ellipsis);
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            const pageBtn = createPageBtn(i);
            container.appendChild(pageBtn);
        }

        if (endPage < lastPage) {
            if (endPage < lastPage - 1) {
                const ellipsis = document.createElement('span');
                ellipsis.className = 'text-slate-400 dark:text-slate-600 px-1.5 text-sm select-none';
                ellipsis.textContent = '...';
                container.appendChild(ellipsis);
            }
            const lastPageBtn = createPageBtn(lastPage);
            container.appendChild(lastPageBtn);
        }

        // Next Button
        const nextBtn = document.createElement('button');
        nextBtn.type = 'button';
        nextBtn.disabled = currentPage === lastPage;
        nextBtn.className = `w-10 h-10 rounded-xl flex items-center justify-center border transition-all ${currentPage === lastPage ? 'border-slate-100 dark:border-slate-800 text-slate-350 dark:text-slate-700 cursor-not-allowed bg-slate-50 dark:bg-slate-900/50' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-350 hover:border-accent hover:text-accent bg-white dark:bg-slate-900 cursor-pointer shadow-sm'}`;
        nextBtn.innerHTML = '<i class="fa-solid fa-chevron-right text-xs"></i>';
        nextBtn.onclick = () => {
            if (currentPage < lastPage) {
                currentPage++;
                loadBlogData(currentPage, searchQuery, activeCategorySlug, false);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        };
        container.appendChild(nextBtn);
    }

    function createPageBtn(pageNumber) {
        const btn = document.createElement('button');
        btn.type = 'button';
        const isActive = pageNumber === currentPage;
        btn.className = `w-10 h-10 rounded-xl font-bold text-xs flex items-center justify-center border transition-all ${isActive ? 'bg-accent border-accent text-white shadow-sm shadow-accent/20' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:border-accent hover:text-accent bg-white dark:bg-slate-900 cursor-pointer'}`;
        btn.textContent = pageNumber;
        btn.onclick = () => {
            if (pageNumber !== currentPage) {
                currentPage = pageNumber;
                loadBlogData(currentPage, searchQuery, activeCategorySlug, false);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        };
        return btn;
    }

    function updateHeaderLayout(categoryObj) {
        const badge = document.getElementById('journalBadge');
        const title = document.getElementById('journalTitle');
        const description = document.getElementById('journalDescription');

        if (categoryObj) {
            badge.textContent = `${categoryObj.name} Archives`;
            title.textContent = categoryObj.name;
            description.textContent = categoryObj.description || '';
            document.title = `${categoryObj.name} — SGCart Blog`;
        } else {
            badge.textContent = 'SGCart Blog';
            title.textContent = 'Style Lookbooks, Shopping Tips & Trends';
            description.textContent = 'Explore style lookbooks, exclusive coupon deals, fabric spotlights, and blog posts designed to elevate your checkout experience.';
            document.title = 'SGCart Blog';
        }
    }

    function renderCategories(categories, activeCategoryId) {
        const container = document.getElementById('categoryContainer');
        let html = `
            <a href="/blog" data-category-slug="" class="shrink-0 px-4.5 py-2 rounded-full text-xs sm:text-sm font-semibold border transition-all duration-300 ${!activeCategoryId ? 'bg-accent text-white border-accent shadow-sm shadow-accent/20' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:border-accent hover:text-accent'} select-none">
                All Articles
            </a>
        `;
        categories.forEach(cat => {
            if (cat.posts_count > 0) {
                const isActive = cat.id === activeCategoryId;
                html += `
                    <a href="/blog/category/${cat.slug}" data-category-slug="${cat.slug}" class="shrink-0 px-4.5 py-2 rounded-full text-xs sm:text-sm font-semibold border transition-all duration-300 ${isActive ? 'bg-accent text-white border-accent shadow-sm shadow-accent/20' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:border-accent hover:text-accent'} select-none">
                        ${cat.name} <span class="text-[10px] ${isActive ? 'text-white/80' : 'text-slate-400 dark:text-slate-500'} font-medium ml-1">(${cat.posts_count})</span>
                    </a>
                `;
            }
        });
        container.innerHTML = html;
    }

    function renderPosts(posts, append) {
        const grid = document.getElementById('postsGrid');

        if (posts.length === 0 && !append) {
            grid.outerHTML = `
                <div id="postsGrid" class="text-center py-20 bg-slate-55/40 dark:bg-slate-900/30 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-xl mx-auto col-span-3">
                    <i class="fa-solid fa-newspaper text-5xl text-slate-300 dark:text-slate-700 mb-4 block"></i>
                    <h3 class="font-bold text-lg text-slate-800 dark:text-white">No blog posts found</h3>
                    <p class="text-slate-555 dark:text-slate-455 mt-1 text-sm px-6">We couldn't find any blog posts matching your query.</p>
                    <button onclick="clearSearchFilter()" class="inline-flex items-center gap-2 mt-5 px-5 py-2.5 rounded-xl bg-accent hover:bg-accent/90 text-white text-xs font-bold tracking-wider uppercase shadow-md shadow-accent/10 transition-colors border-none cursor-pointer">
                        Reset Search
                    </button>
                </div>
            `;
            return;
        }

        const oldGrid = document.getElementById('postsGrid');
        if (oldGrid.tagName !== 'DIV') {
            const newGrid = document.createElement('div');
            newGrid.id = 'postsGrid';
            newGrid.className = 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-10';
            oldGrid.parentNode.replaceChild(newGrid, oldGrid);
        }

        let html = append ? document.getElementById('postsGrid').innerHTML : '';
        posts.forEach(post => {
            const date = new Date(post.published_at || post.created_at);
            const formattedDate = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            
            const imageHtml = post.featured_image_url 
                ? `<img src="${post.featured_image_url}" alt="${post.title}" class="w-full h-full object-cover group-hover:scale-102 transition-transform duration-500 ease-out">`
                : `<div class="w-full h-full flex items-center justify-center text-slate-300"><i class="fa-solid fa-newspaper text-5xl opacity-40"></i></div>`;
            
            const categoryHtml = post.category 
                ? `<a href="/blog/category/${post.category.slug}" class="text-[9px] font-extrabold uppercase tracking-widest text-accent mb-2.5 block relative z-20 hover:text-accent/80 transition-colors">${post.category.name}</a>`
                : `<span class="text-[9px] font-extrabold uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-2.5 block">Uncategorized</span>`;

            const firstLetter = (post.author && post.author.name) ? post.author.name.charAt(0).toUpperCase() : 'A';
            const authorName = (post.author && post.author.name) ? post.author.name : 'Admin';

            html += `
                <article class="flex flex-col group h-full bg-white dark:bg-slate-900 border border-slate-200/50 dark:border-slate-800/80 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300 relative card-animate">
                    <div class="block overflow-hidden aspect-[16/10] bg-slate-105 dark:bg-slate-955 relative border-b border-slate-100 dark:border-slate-900">
                        ${imageHtml}
                        <span class="absolute top-4 right-4 bg-slate-900/75 dark:bg-slate-955/75 backdrop-blur-md text-white text-[10px] font-bold px-3 py-1.5 rounded-full border border-white/10 shadow-sm z-20">
                            <i class="fa-regular fa-clock mr-1 text-[10px]"></i> ${formattedDate}
                        </span>
                    </div>

                    <div class="flex-1 flex flex-col justify-between p-6">
                        <div>
                            ${categoryHtml}
                            <h3 class="font-display font-bold text-lg text-slate-900 dark:text-white mb-3 line-clamp-2 leading-snug tracking-tight group-hover:text-accent transition-colors">
                                <a href="/blog/${post.slug}" class="after:absolute after:inset-0 after:z-10 focus:outline-none">
                                    ${post.title}
                                </a>
                            </h3>
                            <p class="text-slate-555 dark:text-slate-450 text-sm line-clamp-2 leading-relaxed mb-4">
                                ${post.summary || ''}
                            </p>
                        </div>

                        <div class="pt-4 flex items-center justify-between border-t border-slate-100 dark:border-slate-800/80 mt-auto">
                            <span class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1.5 font-medium">
                                <span class="w-6 h-6 rounded-full bg-accent/10 border border-accent/20 flex items-center justify-center text-accent text-[9px] font-bold">
                                    ${firstLetter}
                                </span>
                                ${authorName}
                            </span>
                            <span class="inline-flex items-center gap-1 text-accent group-hover:text-accent/80 text-xs font-bold uppercase tracking-wider transition-all group-hover:gap-1.5 pointer-events-none">
                                Read More <i class="fa-solid fa-arrow-right text-[9px]"></i>
                            </span>
                        </div>
                    </div>
                </article>
            `;
        });
        document.getElementById('postsGrid').innerHTML = html;
    }

    function toggleSearchBadge(search) {
        const badge = document.getElementById('searchBadgeContainer');
        const text = document.getElementById('searchBadgeText');
        if (search) {
            text.textContent = `"${search}"`;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    }

    function renderErrorState(message) {
        const grid = document.getElementById('postsGrid');
        grid.outerHTML = `
            <div id="postsGrid" class="text-center py-20 bg-rose-50/20 dark:bg-rose-955/5 border border-rose-200/40 dark:border-rose-900/20 rounded-2xl max-w-xl mx-auto col-span-3">
                <i class="fa-solid fa-circle-exclamation text-5xl text-rose-500 mb-4 block"></i>
                <h3 class="font-bold text-lg text-slate-800 dark:text-white">Error</h3>
                <p class="text-slate-500 dark:text-slate-450 mt-1 text-sm px-6">${message}</p>
                <button onclick="loadBlogData(currentPage, searchQuery, activeCategorySlug, false)" class="inline-flex items-center gap-2 mt-5 px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-slate-800 dark:hover:bg-slate-700 text-white text-xs font-bold tracking-wider uppercase transition-colors border-none cursor-pointer">
                    Try Again
                </button>
            </div>
        `;
    }

    function setActiveCategoryPill(slug) {
        const container = document.getElementById('categoryContainer');
        if (!container) return;
        
        const pills = container.querySelectorAll('a[data-category-slug]');
        pills.forEach(pill => {
            const pillSlug = pill.getAttribute('data-category-slug');
            if (pillSlug === slug) {
                pill.className = "shrink-0 px-4.5 py-2 rounded-full text-xs sm:text-sm font-semibold border transition-all duration-300 bg-accent text-white border-accent shadow-sm shadow-accent/20 select-none";
                const badge = pill.querySelector('span');
                if (badge) badge.className = "text-[10px] text-white/80 font-medium ml-1";
            } else {
                pill.className = "shrink-0 px-4.5 py-2 rounded-full text-xs sm:text-sm font-semibold border transition-all duration-300 bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:border-accent hover:text-accent select-none";
                const badge = pill.querySelector('span');
                if (badge) badge.className = "text-[10px] text-slate-400 dark:text-slate-500 font-medium ml-1";
            }
        });
    }
</script>
