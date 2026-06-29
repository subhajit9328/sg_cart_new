<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'sgcart — Modern Fashion')</title>
    <link rel="icon" type="image/svg+xml" href='data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="%23c8a97e" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>'>
    
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
    
    <!-- Vite asset compilation -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>
<body class="storefront">

@php
    $navCategories = collect(App\Http\Controllers\StoreController::getProducts())->pluck('cat')->unique()->values();
    $cartCount = \App\Models\Cart::getActiveCart()->items->sum('quantity');
@endphp

<!-- SCROLL TOP -->
<button class="scroll-top" id="scrollTop" onclick="window.scrollTo({top:0,behavior:'smooth'})">
    <i class="fa-solid fa-arrow-up"></i>
</button>

<!-- TOAST -->
<div class="toast-wrap" id="toastWrap"></div>

<!-- MOBILE NAV -->
<div class="mobile-nav" id="mobileNav">
    <div class="mobile-nav-bg" onclick="closeMobileNav()"></div>
    <div class="mobile-nav-panel">
        <div class="mobile-nav-header">
            <a class="mobile-nav-logo" href="{{ route('store.home') }}" onclick="closeMobileNav()">
                <i class="fa-solid fa-cart-shopping"></i>
                <span class="mobile-nav-brand">sgcart<span class="brand-dot">.</span></span>
            </a>
            <button class="mobile-nav-close" onclick="closeMobileNav()" aria-label="Close menu">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="mobile-nav-body">
            <!-- User Status Card -->
            @auth('customer')
                <div class="mobile-nav-user-card">
                    <div class="w-10 h-10 rounded-full bg-accent/10 border border-accent/20 flex items-center justify-center text-accent text-lg">
                        <i class="fa-regular fa-user"></i>
                    </div>
                    <div class="mobile-nav-user-info leading-tight">
                        <p class="text-[10px] text-stone uppercase tracking-wider font-semibold">Logged in as</p>
                        <p class="text-[14px] font-bold text-ink truncate">{{ Auth::guard('customer')->user()->name }}</p>
                    </div>
                </div>
            @else
                <div class="mobile-nav-user-card" style="background:var(--color-blush)">
                    <div class="mobile-nav-user-info leading-normal">
                        <p class="text-xs text-stone mb-2.5">Sign in to track orders, save items to your wishlist, and check out faster.</p>
                        <a href="{{ route('store.login') }}" class="btn btn-accent btn-sm w-full py-2 rounded-lg text-center font-semibold text-xs tracking-wider" onclick="closeMobileNav()">Sign In</a>
                    </div>
                </div>
            @endauth

            <!-- Navigation Links -->
            <div class="mobile-nav-section">
                <span class="mobile-nav-section-title">Navigation</span>
                <nav class="mobile-nav-list">
                    <a href="{{ route('store.home') }}" class="mobile-nav-link {{ Route::is('store.home') ? 'active' : '' }}" onclick="closeMobileNav()">
                        <i class="fa-solid fa-house"></i>
                        <span>Home</span>
                    </a>
                    <a href="{{ route('store.shop') }}" class="mobile-nav-link {{ (Route::is('store.shop') && !request('category')) ? 'active' : '' }}" onclick="closeMobileNav()">
                        <i class="fa-solid fa-store"></i>
                        <span>Shop All</span>
                    </a>
                    <a href="{{ route('store.cart') }}" class="mobile-nav-link {{ Route::is('store.cart') ? 'active' : '' }}" onclick="closeMobileNav()">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <span>Cart</span>
                        <span class="ml-auto min-w-[18px] h-[18px] bg-accent text-white rounded-full font-bold text-[9px] flex items-center justify-center px-1.5 py-0.5 leading-none" id="mobileCartBadge" style="{{ $cartCount > 0 ? '' : 'display: none;' }}">{{ $cartCount }}</span>
                    </a>
                </nav>
            </div>

            <!-- Categories Section -->
            <div class="mobile-nav-section">
                <span class="mobile-nav-section-title">Browse Categories</span>
                <nav class="mobile-nav-list">
                    @foreach($navCategories as $cat)
                        <a href="{{ route('store.shop', ['category' => $cat]) }}" class="mobile-nav-link {{ request('category') === $cat ? 'active' : '' }}" onclick="closeMobileNav()">
                            <i class="fa-solid fa-tag"></i>
                            <span>{{ $cat }}</span>
                        </a>
                    @endforeach
                </nav>
            </div>

            <!-- User Options / Logout -->
            @auth('customer')
                <div class="mobile-nav-section">
                    <span class="mobile-nav-section-title">Account Settings</span>
                    <nav class="mobile-nav-list">
                        <a href="{{ route('store.account') }}" class="mobile-nav-link {{ Route::is('store.account') ? 'active' : '' }}" onclick="closeMobileNav()">
                            <i class="fa-solid fa-circle-user"></i>
                            <span>My Account</span>
                        </a>
                        <a onclick="event.preventDefault(); closeMobileNav(); showConfirm('Are you sure you want to sign out?', () => document.getElementById('storeLogoutForm').submit());" class="mobile-nav-link text-rose-600 hover:bg-rose-50 hover:text-rose-700 cursor-pointer">
                            <i class="fa-solid fa-right-from-bracket text-rose-500"></i>
                            <span>Sign Out</span>
                        </a>
                    </nav>
                </div>
            @endauth
        </div>

        <!-- Footer Promo Area -->
        <div class="mobile-nav-footer">
            <div class="flex items-center gap-3 text-xs text-stone">
                <i class="fa-solid fa-truck-fast text-[14px] text-accent"></i>
                <div class="leading-tight">
                    <p class="font-bold text-ink">Free Shipping</p>
                    <p class="text-[10px] text-stone">On orders above ₹999</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- NAVBAR: Amazon-style double row -->

<header id="siteHeader">
    <!-- ROW 1: Main Header -->
    <div class="header-main">
        <div class="header-main-inner">

            <!-- Hamburger Menu Button (Mobile only) -->
            <button type="button" class="header-hamburger" id="hamburger" onclick="toggleMobileNav()" aria-label="Toggle navigation menu">
                <i class="fa-solid fa-bars"></i>
            </button>

            <!-- Logo -->
            <a class="header-logo" href="{{ route('store.home') }}">
                <i class="fa-solid fa-cart-shopping"></i>
                <span class="header-logo-text">sgcart</span>
            </a>

            <!-- Search Bar -->
            <div class="header-search-wrap">
                <form action="{{ route('store.shop') }}" method="GET" id="navSearchForm" class="header-search-form">
                    <input type="text" name="search" id="navSearchInput"
                           placeholder="Search products, brands and more…"
                           autocomplete="off"
                           value="{{ request('search') }}"
                           class="header-search-input"/>
                    <button type="button" class="header-search-clear-btn" id="clearSearchBtn" title="Clear Search" style="display: none;">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                    <button type="button" class="header-search-voice-btn" id="voiceSearchBtn" title="Search by Voice">
                        <i class="fa-solid fa-microphone"></i>
                    </button>
                    @if(class_exists(\SGCart\ImageSearch\ImageSearchServiceProvider::class))
                    <button type="button" class="header-search-camera-btn" id="cameraSearchBtn" title="Search by Image">
                        <i class="fa-solid fa-camera"></i>
                    </button>
                    <input type="file" id="cameraSearchInput" accept="image/*" style="display:none;" />
                    @endif
                    <button type="submit" class="header-search-btn" aria-label="Search">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </form>
                <div class="nav-search-dropdown" id="navSearchDropdown"></div>
            </div>

            <!-- Right Actions -->
            <div class="header-actions">
                <!-- Theme Toggle -->
                <button type="button" id="themeToggleBtn" class="header-theme-btn" title="Toggle Dark/Light Mode">
                    <i class="fa-solid fa-moon header-theme-icon" id="themeIcon"></i>
                </button>

                <!-- Wishlist -->
                <a href="{{ Auth::guard('customer')->check() ? route('store.account', 'wishlist') : route('store.wishlist') }}" class="header-wishlist-btn" title="Wishlist">
                    <i class="fa-solid fa-heart header-wishlist-icon"></i>
                    @php
                        $wishlistCount = count(session('wishlist', []));
                    @endphp
                    <span class="header-wishlist-count" id="wishlistBadge" style="{{ $wishlistCount > 0 ? '' : 'display: none;' }}">{{ $wishlistCount }}</span>
                </a>

                <!-- Cart -->
                <a href="{{ route('store.cart') }}" class="header-cart-btn" title="Cart">
                    <i class="fa-solid fa-cart-shopping header-cart-icon"></i>
                    <span class="header-cart-count" id="cartBadge" style="{{ $cartCount > 0 ? '' : 'display: none;' }}">{{ $cartCount }}</span>
                </a>

                <!-- Account -->
                @auth('customer')
                <a href="{{ route('store.account') }}" class="header-account-btn" title="My Account">
                    <i class="fa-regular fa-circle-user header-account-icon"></i>
                    <span class="header-account-name">{{ explode(' ', Auth::guard('customer')->user()->name)[0] }}</span>
                </a>
                @else
                <a href="{{ route('store.login') }}" class="header-account-btn" title="Sign In">
                    <i class="fa-regular fa-circle-user header-account-icon"></i>
                    <span class="header-account-name">Login</span>
                </a>
                @endauth
            </div>

        </div>
    </div>

    <!-- ROW 2: Sub Navigation Bar -->
    <div class="header-sub">
        <div class="header-sub-inner">

            <!-- Navigation Links -->
            <nav class="header-sub-links" aria-label="Category navigation">
                <a href="{{ route('store.home') }}" class="header-sub-link {{ Route::is('store.home') ? 'active' : '' }}">
                    <i class="fa-solid fa-house"></i> Home
                </a>
                <a href="{{ route('store.shop') }}" class="header-sub-link {{ (Route::is('store.shop') && !request('category')) ? 'active' : '' }}">
                    <i class="fa-solid fa-store"></i> Shop All
                </a>
                @foreach($navCategories->take(6) as $cat)
                    <a href="{{ route('store.shop', ['category' => $cat]) }}"
                       class="header-sub-link {{ request('category') === $cat ? 'active' : '' }}">
                        {{ $cat }}
                    </a>
                @endforeach
            </nav>

            <!-- Promo strip (right-aligned) -->
            <div class="header-sub-promo">
                <i class="fa-solid fa-truck-fast"></i>
                <span>Free shipping on orders over ₹999</span>
            </div>

        </div>
    </div>
</header>

<!-- MAIN CONTENT -->
<div class="min-h-screen" style="padding-top: 112px; padding-bottom: 48px;">
    @yield('content')
</div>

<!-- FOOTER -->
<footer>
    <div class="footer-inner">
        <div>
            <div class="footer-logo"><i class="fa-solid fa-cart-shopping logo-icon"></i>sgcart<span>.</span></div>
            <p class="footer-desc">Curated collections of premium clothing, footwear, and minimal accessories designed for the modern lifestyle.</p>
        </div>
        <div>
            <h4 class="footer-title">Shop</h4>
            <ul class="footer-links">
                @php
                    $footerCategories = collect(App\Http\Controllers\StoreController::getProducts())->pluck('cat')->unique()->values()->take(4);
                @endphp
                @foreach($footerCategories as $cat)
                    <li><a href="{{ route('store.shop', ['category' => $cat]) }}">{{ $cat }}</a></li>
                @endforeach
            </ul>
        </div>
        <div>
            <h4 class="footer-title">Help</h4>
            <ul class="footer-links">
                <li><a href="#">FAQ & Support</a></li>
                <li><a href="#">Shipping & Returns</a></li>
                <li><a href="#">Size Guides</a></li>
                <li><a href="#">Contact Us</a></li>
            </ul>
        </div>
        <div>
            <h4 class="footer-title">Newsletter</h4>
            <p class="footer-desc" style="margin-bottom:16px">Subscribe to get updates on new drops, private sales, and fashion edits.</p>
            <div class="footer-newsletter" style="display:flex;gap:8px">
                <input class="inp" placeholder="your.email@domain.com" style="flex:1;height:42px"/>
                <button class="btn btn-primary btn-sm" onclick="showToast('Subscribed!','success')" style="height:42px">Join</button>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; {{ date('Y') }} SGCart E-commerce. All rights reserved.</p>
    </div>
</footer>

<!-- Toast Notifications and General Script -->
<script>
    // Scroll Top Button
    const scrollTop = document.getElementById('scrollTop');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 300) {
            scrollTop.classList.add('show');
        } else {
            scrollTop.classList.remove('show');
        }
    });

    // Mobile Navigation Drawer Toggle
    const mobileNav = document.getElementById('mobileNav');
    const hamburger = document.getElementById('hamburger');
    
    function toggleMobileNav() {
        mobileNav.classList.toggle('open');
        hamburger.classList.toggle('open');
    }
    
    function closeMobileNav() {
        mobileNav.classList.remove('open');
        hamburger.classList.remove('open');
    }

    // Global Toast Handler
    function showToast(text, type='success') {
        const wrap = document.getElementById('toastWrap');
        if (!wrap) return;
        const t = document.createElement('div');
        t.className = `toast ${type}`;
        
        let icon = 'fa-circle-check';
        if (type === 'error') {
            icon = 'fa-circle-xmark';
        } else if (type === 'warning') {
            icon = 'fa-triangle-exclamation';
        } else if (type === 'info') {
            icon = 'fa-circle-info';
        }
        
        t.innerHTML = `
            <i class="fa-solid ${icon} toast-icon flex-shrink-0"></i>
            <span class="grow pr-2">${text}</span>
            <button class="toast-close ml-auto flex-shrink-0 text-white/70 hover:text-white cursor-pointer transition-colors text-sm border-none bg-transparent outline-none focus:outline-none" aria-label="Close">
                <i class="fa-solid fa-xmark"></i>
            </button>
        `;
        
        wrap.appendChild(t);

        let autoDismiss = setTimeout(() => {
            dismissToast();
        }, 3000);

        function dismissToast() {
            clearTimeout(autoDismiss);
            t.classList.add('out');
            setTimeout(() => t.remove(), 300);
        }

        t.querySelector('.toast-close').addEventListener('click', (e) => {
            e.stopPropagation();
            dismissToast();
        });
    }

    // Global Alert Modal
    function showAlert(text, title='Alert') {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm animate-fadeIn';
        modal.innerHTML = `
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 dark:border-slate-800 transform scale-95 opacity-0 transition-all duration-300 popup-content">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-lg font-bold text-ink dark:text-white font-display">${title}</h3>
                    <button class="modal-close text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer border-none bg-transparent outline-none focus:outline-none">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                <p class="text-sm text-stone dark:text-slate-400 mb-6 leading-relaxed">${text}</p>
                <div class="flex justify-end">
                    <button class="modal-ok btn btn-primary px-5 py-2.5 rounded-lg text-sm font-semibold cursor-pointer">OK</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        
        // Trigger scale-in transition
        setTimeout(() => {
            const content = modal.querySelector('.popup-content');
            if (content) {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }
        }, 10);
        
        function closeModal() {
            const content = modal.querySelector('.popup-content');
            if (content) {
                content.classList.remove('scale-100', 'opacity-100');
                content.classList.add('scale-95', 'opacity-0');
            }
            modal.classList.remove('animate-fadeIn');
            modal.classList.add('animate-fadeOut');
            setTimeout(() => modal.remove(), 200);
        }
        
        modal.querySelector('.modal-close').addEventListener('click', closeModal);
        modal.querySelector('.modal-ok').addEventListener('click', closeModal);
    }

    // Global Confirmation Modal
    function showConfirm(text, callback, title='Confirm Action', type='default') {
        const confirmBtnClass = type === 'danger' ? 'bg-[#dc2626] hover:bg-[#b91c1c] text-white' : 'btn-primary';
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm animate-fadeIn';
        modal.innerHTML = `
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 dark:border-slate-800 transform scale-95 opacity-0 transition-all duration-300 popup-content">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-lg font-bold text-ink dark:text-white font-display">${title}</h3>
                    <button class="modal-close text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer border-none bg-transparent outline-none focus:outline-none">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                <p class="text-sm text-stone dark:text-slate-400 mb-6 leading-relaxed">${text}</p>
                <div class="flex justify-end gap-3">
                    <button class="modal-cancel border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 px-5 py-2.5 rounded-lg text-sm font-semibold cursor-pointer transition-colors bg-transparent" style="text-transform: none;">Cancel</button>
                    <button class="modal-confirm btn ${confirmBtnClass} px-5 py-2.5 rounded-lg text-sm font-semibold cursor-pointer" style="text-transform: none;">Confirm</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        
        // Trigger scale-in transition
        setTimeout(() => {
            const content = modal.querySelector('.popup-content');
            if (content) {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }
        }, 10);
        
        function closeModal(confirmed = false) {
            const content = modal.querySelector('.popup-content');
            if (content) {
                content.classList.remove('scale-100', 'opacity-100');
                content.classList.add('scale-95', 'opacity-0');
            }
            modal.classList.remove('animate-fadeIn');
            modal.classList.add('animate-fadeOut');
            setTimeout(() => {
                modal.remove();
                if (confirmed && typeof callback === 'function') {
                    callback();
                }
            }, 200);
        }
        
        modal.querySelector('.modal-close').addEventListener('click', () => closeModal(false));
        modal.querySelector('.modal-cancel').addEventListener('click', () => closeModal(false));
        modal.querySelector('.modal-confirm').addEventListener('click', () => closeModal(true));
    }

    // Flash Toast triggers
    @if(session('success'))
        showToast("{{ session('success') }}", 'success');
    @endif
    @if(session('error'))
        showToast("{{ session('error') }}", 'error');
    @endif
    @if(session('warning'))
        showToast("{{ session('warning') }}", 'warning');
    @endif
    @if(session('info'))
        showToast("{{ session('info') }}", 'info');
    @endif

    // Global Form Submit Loader
    document.addEventListener('submit', (e) => {
        if (e.defaultPrevented) return;

        const form = e.target;
        if (form && (form.classList.contains('wishlist-ajax-form') || (form.action && form.action.includes('/wishlist/toggle')))) {
            return;
        }

        const submitBtns = form.querySelectorAll('button[type="submit"], input[type="submit"]');
        submitBtns.forEach(btn => {
            // Check if spinner is already added
            if (!btn.querySelector('.fa-spinner')) {
                const spinner = document.createElement('i');
                spinner.className = 'fa-solid fa-spinner fa-spin mr-2';
                btn.insertBefore(spinner, btn.firstChild);
            }

            btn.disabled = true;
            btn.style.pointerEvents = 'none';
            btn.style.opacity = '0.8';
        });
    });

    // Restore submit buttons when pageshow triggers (handles browser back button cache)
    window.addEventListener('pageshow', (e) => {
        const submitBtns = document.querySelectorAll('button[type="submit"], input[type="submit"]');
        submitBtns.forEach(btn => {
            btn.disabled = false;
            btn.style.pointerEvents = '';
            btn.style.opacity = '';
            const spinner = btn.querySelector('.fa-spinner');
            if (spinner) {
                spinner.remove();
            }
        });
    });

    // Password visibility toggle
    document.querySelectorAll('input[type="password"]:not([name="card_cvv"])').forEach(input => {
        let parent = input.parentNode;
        if (!parent.classList.contains('relative')) {
            const wrapper = document.createElement('div');
            wrapper.className = 'relative w-full flex items-center';
            parent.insertBefore(wrapper, input);
            wrapper.appendChild(input);
            parent = wrapper;
        } else {
            parent.classList.add('flex', 'items-center');
        }
        
        input.classList.add('pr-12');
        
        const toggleBtn = document.createElement('button');
        toggleBtn.type = 'button';
        toggleBtn.className = 'absolute right-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer border-none bg-transparent outline-none focus:outline-none flex items-center justify-center p-1 text-sm z-10';
        toggleBtn.innerHTML = '<i class="fa-regular fa-eye"></i>';
        
        parent.appendChild(toggleBtn);
        
        toggleBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            if (input.type === 'password') {
                input.type = 'text';
                toggleBtn.innerHTML = '<i class="fa-regular fa-eye-slash"></i>';
            } else {
                input.type = 'password';
                toggleBtn.innerHTML = '<i class="fa-regular fa-eye"></i>';
            }
        });
    });

    // Live Search with Dropdown
    const searchInput = document.getElementById('navSearchInput');
    const searchDropdown = document.getElementById('navSearchDropdown');
    const searchContainer = document.querySelector('.header-search-wrap');
    const searchButton = searchContainer ? searchContainer.querySelector('.header-search-btn') : null;
    let debounceTimer;

    if (searchInput && searchDropdown) {
        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            const query = searchInput.value.trim();

            if (query.length < 2) {
                searchDropdown.classList.remove('show');
                searchDropdown.innerHTML = '';
                if (searchButton) {
                    searchButton.innerHTML = '<i class="fa-solid fa-magnifying-glass"></i>';
                }
                return;
            }

            // Show loading state and toggle button icon
            searchDropdown.classList.add('show');
            searchDropdown.innerHTML = `
                <div class="search-loading">
                    <i class="fa-solid fa-circle-notch fa-spin text-accent text-sm"></i>
                    <span>Searching...</span>
                </div>
            `;
            if (searchButton) {
                searchButton.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin text-accent"></i>';
            }

            debounceTimer = setTimeout(() => {
                fetch(`/search-live?q=${encodeURIComponent(query)}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (searchButton) {
                        searchButton.innerHTML = '<i class="fa-solid fa-magnifying-glass"></i>';
                    }
                    if (data.length === 0) {
                        searchDropdown.innerHTML = `
                            <div class="search-no-results">
                                <i class="fa-solid fa-magnifying-glass mb-1"></i>
                                <span>No products found for "${query}"</span>
                            </div>
                        `;
                    } else {
                        let html = '<div class="search-results-list">';
                        data.forEach(item => {
                            html += `
                                <a href="${item.url}" class="search-result-item">
                                    <img src="${item.img}" alt="${item.name}">
                                    <div class="search-result-info">
                                        <span class="search-result-cat">${item.cat}</span>
                                        <span class="search-result-name">${item.name}</span>
                                        <span class="search-result-price">₹${parseFloat(item.price).toFixed(2)}</span>
                                    </div>
                                </a>
                            `;
                        });
                        html += '</div>';
                        html += `
                            <div class="search-result-footer">
                                <a href="/shop?search=${encodeURIComponent(query)}">View All Results</a>
                            </div>
                        `;
                        searchDropdown.innerHTML = html;
                    }
                })
                .catch(error => {
                    console.error('Error fetching search results:', error);
                    if (searchButton) {
                        searchButton.innerHTML = '<i class="fa-solid fa-magnifying-glass"></i>';
                    }
                    searchDropdown.innerHTML = `
                        <div class="search-no-results">
                            <i class="fa-solid fa-triangle-exclamation text-amber-500 mb-1"></i>
                            <span>Error loading search results</span>
                        </div>
                    `;
                });
            }, 300);
        });

        // Hide when click outside
        document.addEventListener('click', (e) => {
            if (!searchContainer.contains(e.target)) {
                searchDropdown.classList.remove('show');
            }
        });

        // Show when input focused and has enough text
        searchInput.addEventListener('focus', () => {
            if (searchInput.value.trim().length >= 2) {
                searchDropdown.classList.add('show');
            }
        });

        // Keyboard navigation support
        searchInput.addEventListener('keydown', (e) => {
            const items = searchDropdown.querySelectorAll('.search-result-item');
            let activeIndex = Array.from(items).findIndex(item => item.classList.contains('bg-[#f0ece7]'));

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (items.length === 0) return;
                if (activeIndex > -1) items[activeIndex].classList.remove('bg-[#f0ece7]');
                activeIndex = (activeIndex + 1) % items.length;
                items[activeIndex].classList.add('bg-[#f0ece7]');
                items[activeIndex].scrollIntoView({ block: 'nearest' });
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (items.length === 0) return;
                if (activeIndex > -1) items[activeIndex].classList.remove('bg-[#f0ece7]');
                activeIndex = (activeIndex - 1 + items.length) % items.length;
                items[activeIndex].classList.add('bg-[#f0ece7]');
                items[activeIndex].scrollIntoView({ block: 'nearest' });
            } else if (e.key === 'Enter') {
                if (activeIndex > -1 && items[activeIndex]) {
                    e.preventDefault();
                    items[activeIndex].click();
                }
            } else if (e.key === 'Escape') {
                searchDropdown.classList.remove('show');
                searchInput.blur();
            }
        });
    }

    // Voice Search Feature
    (function() {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        const voiceBtn = document.getElementById('voiceSearchBtn');
        const searchInput = document.getElementById('navSearchInput');
        const searchForm = document.getElementById('navSearchForm');

        if (voiceBtn && searchInput && searchForm) {
            if (!SpeechRecognition) {
                // Inform user if SpeechRecognition is not supported in this browser (e.g. Firefox)
                voiceBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    showToast('Voice search is not supported in this browser. Please try Chrome, Edge, or Safari.', 'info');
                });
                return;
            }

            // Speech recognition state variables
            let activeRecognition = null;
            let recognitionState = 'inactive'; // 'inactive', 'starting', 'listening', 'stopping'
            const originalPlaceholder = searchInput.placeholder;

            // Reset states when loaded via back/forward browser cache (bfcache)
            window.addEventListener('pageshow', function(event) {
                if (event.persisted) {
                    recognitionState = 'inactive';
                    voiceBtn.classList.remove('listening');
                    searchInput.placeholder = originalPlaceholder;
                    activeRecognition = null;
                }
            });

            voiceBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                if (recognitionState === 'listening' || recognitionState === 'starting') {
                    recognitionState = 'stopping';
                    if (activeRecognition) {
                        activeRecognition.stop();
                    }
                } else if (recognitionState === 'inactive') {
                    try {
                        recognitionState = 'starting';
                        
                        // Create a fresh instance every time to avoid state corruption or locked state errors
                        activeRecognition = new SpeechRecognition();
                        activeRecognition.continuous = false;
                        activeRecognition.interimResults = false;
                        activeRecognition.maxAlternatives = 1;
                        activeRecognition.lang = document.documentElement.lang || navigator.language || 'en-US';

                        activeRecognition.onstart = function() {
                            recognitionState = 'listening';
                            voiceBtn.classList.add('listening');
                            searchInput.placeholder = 'Listening... Speak now';
                            showToast('Listening for voice input...', 'info');
                        };

                        activeRecognition.onresult = function(event) {
                            if (event.results && event.results[0] && event.results[0][0]) {
                                const transcript = event.results[0][0].transcript;
                                let query = transcript.trim();
                                if (query.endsWith('.')) {
                                    query = query.slice(0, -1);
                                }
                                searchInput.value = query;
                                
                                // Trigger live suggestions dropdown matching the voice input
                                searchInput.dispatchEvent(new Event('input'));
                            }
                        };

                        activeRecognition.onerror = function(event) {
                            console.error('Speech recognition error event:', event.error);
                            cleanup();

                            if (event.error === 'not-allowed') {
                                showToast('Microphone access blocked. Please check browser settings.', 'error');
                            } else if (event.error === 'no-microphone') {
                                showToast('No microphone found. Please connect one.', 'error');
                            } else if (event.error === 'no-speech') {
                                showToast('No speech was detected. Try again.', 'warning');
                            } else {
                                showToast('Voice search failed. Try again.', 'error');
                            }
                        };

                        activeRecognition.onend = function() {
                            cleanup();
                        };

                        function cleanup() {
                            recognitionState = 'inactive';
                            voiceBtn.classList.remove('listening');
                            searchInput.placeholder = originalPlaceholder;
                            activeRecognition = null;
                        }

                        activeRecognition.start();
                    } catch (err) {
                        console.error('Speech recognition error on start:', err);
                        recognitionState = 'inactive';
                        activeRecognition = null;
                        showToast('Error starting speech recognition.', 'error');
                    }
                }
            });
        }
    })();

    // Search Clear Button Feature
    (function() {
        const clearBtn = document.getElementById('clearSearchBtn');
        const searchInput = document.getElementById('navSearchInput');

        if (clearBtn && searchInput) {
            // Function to toggle clear button visibility
            function toggleClearBtn() {
                if (searchInput.value.trim().length > 0) {
                    clearBtn.style.display = 'flex';
                } else {
                    clearBtn.style.display = 'none';
                }
            }

            // Check initially on load (e.g. if back-navigation restores the form query)
            toggleClearBtn();

            // Toggle clear button when input value changes
            searchInput.addEventListener('input', toggleClearBtn);

            // Clear input logic
            clearBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                searchInput.value = '';
                // Trigger input event to update/hide live suggestions dropdown
                searchInput.dispatchEvent(new Event('input'));
                
                clearBtn.style.display = 'none';
                searchInput.focus();
            });
        }
    })();

    // Dark Mode Toggle Feature
    (function() {
        const themeToggleBtn = document.getElementById('themeToggleBtn');
        const themeIcon = document.getElementById('themeIcon');
        const themeLabel = document.getElementById('themeLabel');

        function updateThemeUI() {
            const isDark = document.documentElement.classList.contains('dark');
            if (themeIcon) {
                themeIcon.className = isDark ? 'fa-solid fa-sun header-theme-icon' : 'fa-solid fa-moon header-theme-icon';
            }
            if (themeLabel) {
                themeLabel.textContent = isDark ? 'Light Mode' : 'Dark Mode';
            }
        }

        if (themeToggleBtn) {
            // Initial UI state update
            updateThemeUI();

            themeToggleBtn.addEventListener('click', function() {
                if (document.documentElement.classList.contains('dark')) {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                } else {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                }
                updateThemeUI();
            });
        }
    })();

    // AJAX Wishlist Toggle Feature
    window.toggleWishlist = function(button) {
        if (!button || button.disabled) return;

        const productId = button.getAttribute('data-product-id');
        if (!productId) return;

        // Find the icon and replace with a spinner during loading
        const icon = button.querySelector('i');
        let originalIconClass = '';
        if (icon) {
            originalIconClass = icon.className;
            icon.className = 'fa-solid fa-spinner fa-spin';
        }

        button.disabled = true;
        button.style.pointerEvents = 'none';

        // Prepare FormData
        const formData = new FormData();
        formData.append('product_id', productId);

        // Get CSRF Token
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        fetch('/wishlist/toggle', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                
                // Update wishlist count badge in the header
                const badge = document.getElementById('wishlistBadge');
                if (badge) {
                    const count = data.wishlist.length;
                    badge.textContent = count;
                    if (count > 0) {
                        badge.style.display = 'inline-flex';
                    } else {
                        badge.style.display = 'none';
                    }
                }
                
                const isProductInWishlist = data.wishlist.map(Number).includes(Number(productId));
                
                // Update all wishlist elements for this product ID on the page
                const buttons = document.querySelectorAll(`[data-product-id="${productId}"]`);
                buttons.forEach(btn => {
                    btn.disabled = false;
                    btn.style.pointerEvents = '';
                    
                    if (btn.classList.contains('wishlist-btn')) {
                        if (isProductInWishlist) {
                            btn.classList.add('active');
                        } else {
                            btn.classList.remove('active');
                        }
                        const iconEl = btn.querySelector('i');
                        if (iconEl) {
                            iconEl.className = isProductInWishlist ? 'fa-solid fa-heart' : 'fa-regular fa-heart';
                        }
                        
                        // Handle removal animation if toggled on account wishlist tab
                        if (btn.closest('#tab-wishlist') && !isProductInWishlist) {
                            const card = btn.closest('.product-card');
                            if (card) {
                                card.style.transition = 'all 0.3s ease';
                                card.style.opacity = '0';
                                card.style.transform = 'scale(0.9)';
                                setTimeout(() => {
                                    card.remove();
                                    const container = document.querySelector('#tab-wishlist .grid, #tab-wishlist .grid-cols-2');
                                    if (container && container.querySelectorAll('.product-card').length === 0) {
                                        container.outerHTML = `
                                            <div class="col-span-2 sm:col-span-3 md:col-span-4 py-12 text-center text-slate-400">
                                                <i class="fa-regular fa-heart text-4xl mb-3 opacity-20 block"></i>
                                                <p class="text-sm">Your wishlist is empty.</p>
                                                <a href="/shop" class="btn btn-primary btn-sm mt-4">Discover Products</a>
                                            </div>
                                        `;
                                    }
                                }, 300);
                            }
                        }
                    } else if (btn.classList.contains('wishlist-detail-btn')) {
                        btn.title = isProductInWishlist ? 'Remove from Wishlist' : 'Add to Wishlist';
                        if (isProductInWishlist) {
                            btn.classList.add('active');
                        } else {
                            btn.classList.remove('active');
                        }
                        const iconEl = btn.querySelector('i');
                        if (iconEl) {
                            iconEl.className = isProductInWishlist ? 'fa-solid fa-heart text-base' : 'fa-regular fa-heart text-base';
                        }
                    } else if (btn.classList.contains('wishlist-remove-btn')) {
                        const card = btn.closest('.product-card');
                        if (card) {
                            card.style.transition = 'all 0.3s ease';
                            card.style.opacity = '0';
                            card.style.transform = 'scale(0.9)';
                            setTimeout(() => {
                                card.remove();
                                const container = document.querySelector('#tab-wishlist .grid, #tab-wishlist .grid-cols-2');
                                if (container && container.querySelectorAll('.product-card').length === 0) {
                                    container.outerHTML = `
                                        <div class="py-12 text-center text-slate-400">
                                            <i class="fa-regular fa-heart text-4xl mb-3 opacity-20 block"></i>
                                            <p class="text-sm">Your wishlist is empty.</p>
                                            <a href="/shop" class="btn btn-primary btn-sm mt-4">Discover Products</a>
                                        </div>
                                    `;
                                }
                            }, 300);
                        }
                    }
                });
            } else {
                showToast(data.message || 'Something went wrong', 'error');
                // Restore button state on error
                button.disabled = false;
                button.style.pointerEvents = '';
                if (icon) icon.className = originalIconClass;
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Failed to update wishlist.', 'error');
            // Restore button state on error
            button.disabled = false;
            button.style.pointerEvents = '';
            if (icon) icon.className = originalIconClass;
        });
    };
</script>
<form action="{{ route('store.logout') }}" method="POST" id="storeLogoutForm" class="hidden">
    @csrf
</form>

@if(class_exists(\SGCart\ImageSearch\ImageSearchServiceProvider::class))
<!-- Image Crop Modal -->
<div id="imageCropModal" class="fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm hidden" style="display: none; justify-content: center; align-items: center; background: rgba(0,0,0,0.7); backdrop-filter: blur(8px);">
    <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-2xl w-full p-6 shadow-2xl border border-slate-100 dark:border-slate-800 flex flex-col" style="background: white; border-radius: 16px; width: 90%; max-width: 600px; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04); display: flex; flex-direction: column;">
        <div class="flex justify-between items-center mb-4" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 class="text-lg font-bold text-ink" style="margin: 0; font-size: 1.25rem; font-weight: 700; color: var(--color-ink);">Select Object to Search</h3>
            <button id="closeCropModalBtn" class="text-slate-400 hover:text-slate-600 cursor-pointer border-none bg-transparent" style="border: none; background: transparent; cursor: pointer; color: #94a3b8; font-size: 1.25rem; padding: 4px;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <p class="text-sm text-stone mb-4" style="margin-top: 0; margin-bottom: 16px; font-size: 0.875rem; color: #6b7280; line-height: 1.5;">
            Please adjust the selection box or draw a new one to select the specific product you want to search.
        </p>
        
        <div class="flex items-center justify-center bg-slate-50 rounded-xl overflow-hidden relative border border-dashed border-slate-200" style="background: #f8fafc; border-radius: 12px; border: 2px dashed #e2e8f0; display: flex; justify-content: center; align-items: center; overflow: hidden; padding: 12px; min-height: 300px; max-height: 450px;">
            <canvas id="cropCanvas" style="max-width: 100%; max-height: 380px; display: block; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);"></canvas>
        </div>
        
        <div class="flex justify-end gap-3 mt-5" style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px;">
            <button id="cancelCropBtn" class="border border-slate-200 hover:bg-slate-50 text-slate-700 px-5 py-2.5 rounded-lg text-sm font-semibold cursor-pointer" style="border: 1px solid #cbd5e1; background: transparent; color: #334155; padding: 10px 20px; border-radius: 8px; font-size: 0.875rem; font-weight: 600; cursor: pointer;">Cancel</button>
            <button id="confirmCropBtn" class="btn btn-accent px-6 py-2.5 rounded-lg text-sm font-semibold cursor-pointer" style="padding: 10px 20px; border-radius: 8px; font-size: 0.875rem; font-weight: 600; cursor: pointer; color: white; background-color: var(--color-accent); border: none;">Search Product</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cameraBtn = document.getElementById('cameraSearchBtn');
    const cameraInput = document.getElementById('cameraSearchInput');
    const cropModal = document.getElementById('imageCropModal');
    const closeCropModalBtn = document.getElementById('closeCropModalBtn');
    const cancelCropBtn = document.getElementById('cancelCropBtn');
    const confirmCropBtn = document.getElementById('confirmCropBtn');
    const canvas = document.getElementById('cropCanvas');
    const ctx = canvas.getContext('2d');
    
    let originalFile = null;
    let img = new Image();
    
    // Canvas selection state
    let isDrawing = false;
    let interactionMode = null; // 'draw', 'move', or handle name ('tl', 'tr', etc.)
    
    let rectX = 0, rectY = 0, rectWidth = 0, rectHeight = 0;
    let startX = 0, startY = 0;
    
    let dragStartX = 0, dragStartY = 0;
    let initialRectX = 0, initialRectY = 0;
    let initialRectWidth = 0, initialRectHeight = 0;
    
    const handleRadius = 6;
    const hitRadius = 12; // larger hit area for easier touch/click
    
    if (cameraBtn && cameraInput) {
        cameraBtn.addEventListener('click', () => {
            cameraInput.click();
        });
        
        cameraInput.addEventListener('change', function(e) {
            if (e.target.files.length === 0) return;
            
            const file = e.target.files[0];
            originalFile = file;
            
            showToast('Loading image for cropping...', 'info');
            
            const reader = new FileReader();
            reader.onload = function(event) {
                img = new Image();
                img.onload = function() {
                    setupCanvas();
                    openModal();
                };
                img.src = event.target.result;
            };
            reader.readAsDataURL(file);
            
            // Reset input so change event fires again if user selects same file
            cameraInput.value = '';
        });
    }
    
    function openModal() {
        cropModal.style.display = 'flex';
        setTimeout(() => {
            cropModal.style.opacity = '1';
        }, 10);
    }
    
    function closeModal() {
        cropModal.style.opacity = '0';
        setTimeout(() => {
            cropModal.style.display = 'none';
        }, 300);
    }
    
    closeCropModalBtn.addEventListener('click', closeModal);
    cancelCropBtn.addEventListener('click', closeModal);
    
    function setupCanvas() {
        // Set canvas dimensions based on image aspect ratio inside container max dimensions
        const maxW = 560; // Max width based on CSS max-width of container
        const maxH = 380; // Max height based on CSS max-height of container
        
        let w = img.width;
        let h = img.height;
        
        if (w > maxW) {
            h = h * (maxW / w);
            w = maxW;
        }
        if (h > maxH) {
            w = w * (maxH / h);
            h = maxH;
        }
        
        canvas.width = w;
        canvas.height = h;
        
        // Reset selection to select the full image by default (shrunk slightly to keep handles visible)
        const margin = 12;
        rectX = margin;
        rectY = margin;
        rectWidth = Math.max(10, w - 2 * margin);
        rectHeight = Math.max(10, h - 2 * margin);
        
        drawCanvas();
    }
    
    function getHandles() {
        return {
            tl: { x: rectX, y: rectY, cursor: 'nwse-resize' },
            tr: { x: rectX + rectWidth, y: rectY, cursor: 'nesw-resize' },
            bl: { x: rectX, y: rectY + rectHeight, cursor: 'nesw-resize' },
            br: { x: rectX + rectWidth, y: rectY + rectHeight, cursor: 'nwse-resize' },
            t:  { x: rectX + rectWidth / 2, y: rectY, cursor: 'ns-resize' },
            b:  { x: rectX + rectWidth / 2, y: rectY + rectHeight, cursor: 'ns-resize' },
            l:  { x: rectX, y: rectY + rectHeight / 2, cursor: 'ew-resize' },
            r:  { x: rectX + rectWidth, y: rectY + rectHeight / 2, cursor: 'ew-resize' }
        };
    }
    
    function drawHandles() {
        if (rectWidth <= 0 || rectHeight <= 0) return;
        
        const handles = getHandles();
        ctx.fillStyle = '#ffffff';
        ctx.strokeStyle = '#c8a97e';
        ctx.lineWidth = 2;
        ctx.setLineDash([]); // solid border for handles
        
        for (const key in handles) {
            const h = handles[key];
            ctx.beginPath();
            ctx.arc(h.x, h.y, handleRadius, 0, 2 * Math.PI);
            ctx.fill();
            ctx.stroke();
        }
    }
    
    function drawCanvas() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
        
        if (rectWidth > 0 && rectHeight > 0) {
            // Semi-transparent overlay
            ctx.fillStyle = 'rgba(0, 0, 0, 0.55)';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            
            // Clear selected region
            ctx.clearRect(rectX, rectY, rectWidth, rectHeight);
            
            // Redraw image portion inside selection to remove overlay
            ctx.drawImage(img, 
                rectX * (img.width / canvas.width), 
                rectY * (img.height / canvas.height), 
                rectWidth * (img.width / canvas.width), 
                rectHeight * (img.height / canvas.height),
                rectX, rectY, rectWidth, rectHeight
            );
            
            // Border around selection
            ctx.strokeStyle = '#c8a97e';
            ctx.lineWidth = 2;
            ctx.setLineDash([6, 4]);
            ctx.strokeRect(rectX, rectY, rectWidth, rectHeight);
            
            // Draw handles
            drawHandles();
        }
    }
    
    // Mouse / Touch Event Handlers for drawing and resizing selection box
    function getMousePos(e) {
        const rect = canvas.getBoundingClientRect();
        const clientX = e.touches && e.touches.length > 0 ? e.touches[0].clientX : e.clientX;
        const clientY = e.touches && e.touches.length > 0 ? e.touches[0].clientY : e.clientY;
        return {
            x: Math.max(0, Math.min(clientX - rect.left, canvas.width)),
            y: Math.max(0, Math.min(clientY - rect.top, canvas.height))
        };
    }
    
    function getInteractionAt(pos) {
        const handles = getHandles();
        
        // 1. Check if over any handle (using hitRadius for easier click/touch targeting)
        for (const key in handles) {
            const h = handles[key];
            const dist = Math.hypot(pos.x - h.x, pos.y - h.y);
            if (dist <= hitRadius) {
                return key;
            }
        }
        
        // 2. Check if inside selection box
        if (pos.x >= rectX && pos.x <= rectX + rectWidth &&
            pos.y >= rectY && pos.y <= rectY + rectHeight) {
            return 'move';
        }
        
        // 3. Otherwise, draw a new box
        return 'draw';
    }
    
    function updateCursor(e) {
        if (isDrawing) return;
        
        const pos = getMousePos(e);
        const mode = getInteractionAt(pos);
        
        if (mode === 'move') {
            canvas.style.cursor = 'move';
        } else if (mode === 'draw') {
            canvas.style.cursor = 'crosshair';
        } else {
            const handles = getHandles();
            canvas.style.cursor = handles[mode].cursor;
        }
    }
    
    function startDrawing(e) {
        e.preventDefault();
        const pos = getMousePos(e);
        
        interactionMode = getInteractionAt(pos);
        isDrawing = true;
        
        dragStartX = pos.x;
        dragStartY = pos.y;
        
        initialRectX = rectX;
        initialRectY = rectY;
        initialRectWidth = rectWidth;
        initialRectHeight = rectHeight;
        
        if (interactionMode === 'draw') {
            rectX = pos.x;
            rectY = pos.y;
            rectWidth = 0;
            rectHeight = 0;
            startX = pos.x;
            startY = pos.y;
        }
    }
    
    function draw(e) {
        if (!isDrawing) {
            updateCursor(e);
            return;
        }
        e.preventDefault();
        const pos = getMousePos(e);
        
        const dx = pos.x - dragStartX;
        const dy = pos.y - dragStartY;
        
        if (interactionMode === 'draw') {
            rectX = Math.min(startX, pos.x);
            rectY = Math.min(startY, pos.y);
            rectWidth = Math.abs(startX - pos.x);
            rectHeight = Math.abs(startY - pos.y);
        } else if (interactionMode === 'move') {
            let newX = initialRectX + dx;
            let newY = initialRectY + dy;
            
            // Clamp within canvas boundaries
            newX = Math.max(0, Math.min(newX, canvas.width - rectWidth));
            newY = Math.max(0, Math.min(newY, canvas.height - rectHeight));
            
            rectX = newX;
            rectY = newY;
        } else {
            // Handle resizing
            let x1 = rectX;
            let x2 = rectX + rectWidth;
            let y1 = rectY;
            let y2 = rectY + rectHeight;
            
            const minSize = 15;
            
            // Left-side resizing
            if (interactionMode === 'tl' || interactionMode === 'l' || interactionMode === 'bl') {
                x1 = initialRectX + dx;
                x1 = Math.max(0, Math.min(x1, x2 - minSize));
            }
            // Right-side resizing
            if (interactionMode === 'tr' || interactionMode === 'r' || interactionMode === 'br') {
                x2 = initialRectX + initialRectWidth + dx;
                x2 = Math.max(x1 + minSize, Math.min(x2, canvas.width));
            }
            // Top-side resizing
            if (interactionMode === 'tl' || interactionMode === 't' || interactionMode === 'tr') {
                y1 = initialRectY + dy;
                y1 = Math.max(0, Math.min(y1, y2 - minSize));
            }
            // Bottom-side resizing
            if (interactionMode === 'bl' || interactionMode === 'b' || interactionMode === 'br') {
                y2 = initialRectY + initialRectHeight + dy;
                y2 = Math.max(y1 + minSize, Math.min(y2, canvas.height));
            }
            
            rectX = x1;
            rectY = y1;
            rectWidth = x2 - x1;
            rectHeight = y2 - y1;
        }
        
        drawCanvas();
    }
    
    function stopDrawing(e) {
        if (!isDrawing) return;
        isDrawing = false;
        interactionMode = null;
    }
    
    canvas.addEventListener('mousedown', startDrawing);
    canvas.addEventListener('mousemove', draw);
    window.addEventListener('mouseup', stopDrawing);
    
    canvas.addEventListener('touchstart', startDrawing, { passive: false });
    canvas.addEventListener('touchmove', draw, { passive: false });
    window.addEventListener('touchend', stopDrawing);
    
    confirmCropBtn.addEventListener('click', function() {
        if (rectWidth < 10 || rectHeight < 10) {
            showToast('Please select a valid search area.', 'warning');
            return;
        }
        
        // Crop the image using temp canvas
        let targetWidth = rectWidth * (img.width / canvas.width);
        let targetHeight = rectHeight * (img.height / canvas.height);
        
        // Resize if too large
        const maxDimension = 800;
        if (targetWidth > maxDimension || targetHeight > maxDimension) {
            if (targetWidth > targetHeight) {
                targetHeight = targetHeight * (maxDimension / targetWidth);
                targetWidth = maxDimension;
            } else {
                targetWidth = targetWidth * (maxDimension / targetHeight);
                targetHeight = maxDimension;
            }
        }
        
        const tempCanvas = document.createElement('canvas');
        tempCanvas.width = targetWidth;
        tempCanvas.height = targetHeight;
        const tempCtx = tempCanvas.getContext('2d');
        
        tempCtx.drawImage(img, 
            rectX * (img.width / canvas.width), 
            rectY * (img.height / canvas.height), 
            rectWidth * (img.width / canvas.width), 
            rectHeight * (img.height / canvas.height),
            0, 0, tempCanvas.width, tempCanvas.height
        );
        
        tempCanvas.toBlob(function(blob) {
            if (blob) {
                closeModal();
                showToast('Searching for selected product...', 'info');
                const croppedFile = new File([blob], 'cropped.jpg', { type: 'image/jpeg' });
                submitSearchForm(croppedFile);
            }
        }, 'image/jpeg', 0.85);
    });
    
    function submitSearchForm(file) {
        // Build dynamic form to POST the file
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = "{{ route('image-search.search') }}";
        form.enctype = 'multipart/form-data';
        form.style.display = 'none';
        
        // Add CSRF token
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(csrfInput);
        
        // Add File input using DataTransfer to assign file programmaticly
        const fileInput = document.createElement('input');
        fileInput.type = 'file';
        fileInput.name = 'image';
        
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        fileInput.files = dataTransfer.files;
        
        form.appendChild(fileInput);
        document.body.appendChild(form);
        
        // Submit the form
        form.submit();
    }
});
</script>
@endif

@yield('scripts')
</body>
</html>
