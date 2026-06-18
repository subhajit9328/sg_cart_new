<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'sgcart — Modern Fashion')</title>
    
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
        <a href="{{ route('store.home') }}" class="mobile-nav-link">Home</a>
        <a href="{{ route('store.shop') }}" class="mobile-nav-link">Shop All</a>
        <a href="{{ route('store.account') }}" class="mobile-nav-link">My Account</a>
        <a href="{{ route('store.cart') }}" class="mobile-nav-link">Cart</a>
    </div>
</div>

<!-- NAVBAR -->
<nav id="navbar">
    <div class="nav-inner">
        <button class="hamburger" id="hamburger" onclick="toggleMobileNav()">
            <span></span><span></span><span></span>
        </button>
        <a class="logo" href="{{ route('store.home') }}">sgcart<span>.</span></a>
        
        <ul class="nav-links">
            <li><a href="{{ route('store.home') }}" class="{{ Route::is('store.home') ? 'active' : '' }}">Home</a></li>
            <li><a href="{{ route('store.shop') }}" class="{{ Route::is('store.shop') ? 'active' : '' }}">Shop</a></li>
            <li><a href="{{ route('store.account') }}" class="{{ Route::is('store.account') ? 'active' : '' }}">Account</a></li>
        </ul>
        
        <div class="nav-actions">
            <form action="{{ route('store.shop') }}" method="GET" class="nav-search-wrap">
                <i class="fa-solid fa-magnifying-glass" style="color:var(--stone);font-size:13px"></i>
                <input type="text" name="search" placeholder="Search products…" value="{{ request('search') }}"/>
            </form>
            <a class="nav-btn" href="{{ route('store.account') }}" title="Account"><i class="fa-regular fa-user"></i></a>
            <a class="nav-btn" href="{{ route('store.cart') }}" title="Cart">
                <i class="fa-solid fa-bag-shopping"></i>
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
            <div class="footer-logo">sgcart<span>.</span></div>
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
        <p>Created with premium minimal designs.</p>
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
        const t = document.createElement('div');
        t.className = `toast ${type}`;
        
        const icon = type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation';
        t.innerHTML = `
            <i class="fa-solid ${icon} toast-icon"></i>
            <span>${text}</span>
        `;
        
        wrap.appendChild(t);
        setTimeout(() => {
            t.classList.add('out');
            setTimeout(() => t.remove(), 300);
        }, 3000);
    }

    // Flash Toast triggers
    @if(session('success'))
        showToast("{{ session('success') }}", 'success');
    @endif

    @if(session('error'))
        showToast("{{ session('error') }}", 'error');
    @endif
</script>
@yield('scripts')
</body>
</html>
