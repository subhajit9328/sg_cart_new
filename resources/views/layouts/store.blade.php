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

    <body class="storefront">

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
            <a class="logo" href="{{ route('store.home') }}" onclick="closeMobileNav()">
                <i class="fa-solid fa-cart-shopping logo-icon"></i>sgcart<span>.</span>
            </a>
            <button class="mobile-nav-close" onclick="closeMobileNav()" aria-label="Close menu">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <a href="{{ route('store.home') }}" class="mobile-nav-link"><i class="fa-solid fa-house mr-3 text-stone" style="font-size:15px"></i>Home</a>
        <a href="{{ route('store.shop') }}" class="mobile-nav-link"><i class="fa-solid fa-shop mr-3 text-stone" style="font-size:15px"></i>Shop All</a>
        @auth
            <a href="{{ route('store.account') }}" class="mobile-nav-link"><i class="fa-regular fa-user mr-3 text-stone" style="font-size:15px"></i>My Account</a>
        @else
            <a href="{{ route('store.login') }}" class="mobile-nav-link"><i class="fa-solid fa-arrow-right-to-bracket mr-3 text-stone" style="font-size:15px"></i>Login</a>
        @endauth
        <a href="{{ route('store.cart') }}" class="mobile-nav-link"><i class="fa-solid fa-cart-shopping mr-3 text-stone" style="font-size:15px"></i>Cart</a>
    </div>
</div>

<!-- NAVBAR -->
<nav id="navbar">
    <div class="nav-inner">
        <button class="hamburger" id="hamburger" onclick="toggleMobileNav()">
            <span></span><span></span><span></span>
        </button>
        <a class="logo" href="{{ route('store.home') }}"><i class="fa-solid fa-cart-shopping logo-icon"></i>sgcart</a>
        
        <ul class="nav-links">
            <li><a href="{{ route('store.home') }}" class="{{ Route::is('store.home') ? 'active' : '' }}">Home</a></li>
            <li><a href="{{ route('store.shop') }}" class="{{ Route::is('store.shop') ? 'active' : '' }}">Shop</a></li>
            @auth
                <li><a href="{{ route('store.account') }}" class="{{ Route::is('store.account') ? 'active' : '' }}">Account</a></li>
            @else
                <li><a href="{{ route('store.login') }}" class="{{ Route::is('store.login') ? 'active' : '' }}">Login</a></li>
            @endauth
        </ul>
        
        <div class="nav-actions">
            <div class="nav-search-container">
                <form action="{{ route('store.shop') }}" method="GET" class="nav-search-wrap" id="navSearchForm">
                    <input type="text" name="search" id="navSearchInput" placeholder="Search products…" autocomplete="off" value="{{ request('search') }}"/>
                    <button type="submit" class="nav-search-btn" aria-label="Search"><i class="fa-solid fa-magnifying-glass" style="font-size:12px"></i></button>
                </form>
                <div class="nav-search-dropdown" id="navSearchDropdown"></div>
            </div>
            <a class="nav-btn" href="{{ route('store.account') }}" title="Account"><i class="fa-regular fa-user"></i></a>
            <a class="nav-btn" href="{{ route('store.cart') }}" title="Cart">
                <i class="fa-solid fa-cart-shopping"></i>
                <span id="cartBadge">{{ count(session('cart', [])) }}</span>
            </a>
        </div>
    </div>
</nav>

<!-- MAIN CONTENT -->
<div class="min-h-screen" style="padding-top: 64px; padding-bottom: 48px;">
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
                <li><a href="{{ route('store.shop', ['category' => 'Women']) }}">Women</a></li>
                <li><a href="{{ route('store.shop', ['category' => 'Men']) }}">Men</a></li>
                <li><a href="{{ route('store.shop', ['category' => 'Accessories']) }}">Accessories</a></li>
                <li><a href="{{ route('store.shop', ['category' => 'Footwear']) }}">Footwear</a></li>
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
                    <button class="modal-cancel border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 px-5 py-2.5 rounded-lg text-sm font-semibold cursor-pointer transition-colors bg-transparent">Cancel</button>
                    <button class="modal-confirm btn ${confirmBtnClass} px-5 py-2.5 rounded-lg text-sm font-semibold cursor-pointer">Confirm</button>
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
    const searchContainer = document.querySelector('.nav-search-container');
    const searchButton = searchContainer ? searchContainer.querySelector('.nav-search-btn') : null;
    let debounceTimer;

    if (searchInput && searchDropdown) {
        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            const query = searchInput.value.trim();

            if (query.length < 2) {
                searchDropdown.classList.remove('show');
                searchDropdown.innerHTML = '';
                if (searchButton) {
                    searchButton.innerHTML = '<i class="fa-solid fa-magnifying-glass" style="font-size:12px"></i>';
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
                searchButton.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin text-accent" style="font-size:12px"></i>';
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
                        searchButton.innerHTML = '<i class="fa-solid fa-magnifying-glass" style="font-size:12px"></i>';
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
                                        <span class="search-result-price">$${item.price.toFixed(2)}</span>
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
                        searchButton.innerHTML = '<i class="fa-solid fa-magnifying-glass" style="font-size:12px"></i>';
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
</script>
@yield('scripts')
</body>
</html>
