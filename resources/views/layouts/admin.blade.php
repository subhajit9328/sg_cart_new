<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SGCart Admin')</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind compiled by Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }
        .font-display { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-thumb { background: rgba(100, 116, 139, .35); border-radius: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }

        /* Sidebar nav links */
        .nav-link { display: flex; align-items: center; gap: .75rem; padding: .62rem .85rem; border-radius: .6rem; font-size: .86rem; font-weight: 500; color: #94a3b8; cursor: pointer; white-space: nowrap; transition: background .15s, color .15s; text-decoration: none; }
        .nav-link:hover { background: rgba(255, 255, 255, .06); color: #fff; }
        .nav-link.active { background: rgba(37, 99, 235, .22); color: #fff; }
        .nav-link.active i { color: #60a5fa; }
        .nav-link i { width: 20px; text-align: center; flex-shrink: 0; }

        /* Collapsed (icon-only) sidebar */
        #sidebar.icon-only .sidebar-text { display: none; }
        #sidebar.icon-only .nav-link { justify-content: center; }
        #sidebar.icon-only .section-label { display: none; }
        #sidebar.icon-only .brand-text { display: none; }
        #sidebar.icon-only { w: 5rem; }
    </style>
    <script>
        // Check dark mode preference on load (default to light)
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-200">

<div class="flex min-h-screen" id="appShell">

    <!-- ============ Sidebar ============ -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-64 bg-slate-900 flex flex-col -translate-x-full lg:translate-x-0 transition-all duration-200">
        <div class="h-16 flex items-center gap-3 px-5 border-b border-white/10 flex-shrink-0">
            <div class="w-9 h-9 rounded-lg bg-blue-600 flex items-center justify-center font-bold text-white font-display flex-shrink-0">S</div>
            <span class="font-display font-bold text-white text-lg brand-text">SGCart</span>
        </div>

        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
            <p class="px-3 pt-1 pb-1 text-xs font-semibold uppercase tracking-wider text-slate-500 section-label">Main</p>
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ Route::is('admin.dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-gauge-high"></i>
                <span class="sidebar-text">Dashboard</span>
            </a>

            @canany(['manage products', 'manage categories', 'manage manufacturers'])
            <p class="px-3 pt-4 pb-1 text-xs font-semibold uppercase tracking-wider text-slate-500 section-label">Catalogue</p>

            @can('manage products')
            <a href="{{ route('admin.products.index') }}" class="nav-link {{ Request::is('admin/products*') ? 'active' : '' }}">
                <i class="fa-solid fa-box-open"></i>
                <span class="sidebar-text">Products</span>
            </a>
            @endcan

            @can('manage products')
            <a href="{{ route('admin.categories.index') }}" class="nav-link {{ Request::is('admin/categories*') ? 'active' : '' }}">
                <i class="fa-solid fa-tags"></i>
                <span class="sidebar-text">Categories</span>
            </a>
            @endcan

            @can('manage products')
            <a href="{{ route('admin.manufacturers.index') }}" class="nav-link {{ Request::is('admin/manufacturers*') ? 'active' : '' }}">
                <i class="fa-solid fa-industry"></i>
                <span class="sidebar-text">Manufacturers</span>
            </a>
            @endcan
            @endcanany

            @canany(['manage users', 'manage roles'])
            <p class="px-3 pt-4 pb-1 text-xs font-semibold uppercase tracking-wider text-slate-500 section-label">Access Control</p>

            @can('manage users')
            <a href="{{ route('admin.users.index') }}" class="nav-link {{ Request::is('admin/users*') ? 'active' : '' }}">
                <i class="fa-solid fa-users-gear"></i>
                <span class="sidebar-text">User Management</span>
            </a>
            @endcan

            @can('manage roles')
            <a href="{{ route('admin.roles.index') }}" class="nav-link {{ Request::is('admin/roles*') ? 'active' : '' }}">
                <i class="fa-solid fa-shield-halved"></i>
                <span class="sidebar-text">Role Management</span>
            </a>
            @endcan
            @endcanany
        </nav>


        <div class="border-t border-white/10 p-3 flex-shrink-0">
            <form action="{{ route('logout') }}" method="POST" id="logoutForm" class="hidden">
                @csrf
            </form>
            <a onclick="document.getElementById('logoutForm').submit();" class="nav-link hover:text-red-400">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span class="sidebar-text">Sign out</span>
            </a>
        </div>
    </aside>

    <div id="sidebarBackdrop" class="fixed inset-0 bg-slate-900/50 z-30 hidden"></div>

    <!-- ============ Main wrap ============ -->
    <div id="mainWrap" class="flex-1 min-w-0 transition-all duration-200 lg:ml-64">

        <!-- Topbar -->
        <header id="topbar" class="fixed top-0 right-0 left-0 lg:left-64 h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center gap-3 px-4 lg:px-6 z-20 transition-all duration-200">

            <button id="sidebarToggleBtn" class="w-10 h-10 rounded-lg border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 flex-shrink-0">
                <i class="fa-solid fa-bars"></i>
            </button>

            <div class="hidden sm:flex items-center gap-2 bg-slate-100 dark:bg-slate-800 rounded-lg px-3 py-2 w-full max-w-sm">
                <i class="fa-solid fa-magnifying-glass text-slate-400 text-sm"></i>
                <input type="text" class="bg-transparent outline-none text-sm w-full placeholder:text-slate-400" placeholder="Search dashboard, user, roles…">
            </div>

            <div class="flex items-center gap-2 ml-auto">
                <button id="themeToggleBtn" class="w-10 h-10 rounded-lg border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">
                    <i class="fa-solid fa-moon" id="themeIcon"></i>
                </button>
                <button class="relative w-10 h-10 rounded-lg border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">
                    <i class="fa-regular fa-bell"></i>
                    <span class="absolute top-2 right-2 w-2 h-2 rounded-full bg-rose-500"></span>
                </button>

                <div class="relative">
                    <button id="userMenuBtn" class="flex items-center gap-2 pl-1 pr-2 py-1 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800">
                        <img src="https://i.pravatar.cc/64?img=12" class="w-8 h-8 rounded-full object-cover">
                        <div class="hidden md:block text-left leading-tight">
                            <p class="text-sm font-semibold">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-slate-400">{{ auth()->user()->roles->first()?->name ?? 'User' }}</p>
                        </div>
                        <i class="fa-solid fa-chevron-down text-xs text-slate-400"></i>
                    </button>
                    <div id="userMenu" class="hidden absolute right-0 mt-2 w-44 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg shadow-lg py-1 text-sm z-30">
                        <a class="block px-3 py-2 hover:bg-slate-50 dark:hover:bg-slate-700"><i class="fa-regular fa-user mr-2 w-4"></i>Profile</a>
                        <a class="block px-3 py-2 hover:bg-slate-50 dark:hover:bg-slate-700"><i class="fa-solid fa-gear mr-2 w-4"></i>Settings</a>
                        <hr class="my-1 border-slate-200 dark:border-slate-700">
                        <a onclick="document.getElementById('logoutForm').submit();" class="block px-3 py-2 hover:bg-slate-50 dark:hover:bg-slate-700 text-rose-500 cursor-pointer"><i class="fa-solid fa-right-from-bracket mr-2 w-4"></i>Sign out</a>
                    </div>
                </div>
            </div>
        </header>

        <!-- ============ Content ============ -->
        <main class="pt-24 px-4 lg:px-6 pb-10 w-full">
            @if(session('success'))
                <div class="mb-5 flex items-center gap-3 p-4 text-sm text-emerald-800 border border-emerald-200 dark:border-emerald-800/30 rounded-lg bg-emerald-50 dark:bg-emerald-950/20 dark:text-emerald-400 animate-fadeIn" role="alert">
                    <i class="fa-solid fa-circle-check text-emerald-500 text-base"></i>
                    <div>
                        <span class="font-medium">Success!</span> {{ session('success') }}
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-5 flex items-center gap-3 p-4 text-sm text-rose-800 border border-rose-200 dark:border-rose-800/30 rounded-lg bg-rose-50 dark:bg-rose-950/20 dark:text-rose-400 animate-fadeIn" role="alert">
                    <i class="fa-solid fa-circle-xmark text-rose-500 text-base"></i>
                    <div>
                        <span class="font-medium">Error!</span> {{ session('error') }}
                    </div>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<!-- Scripts -->
<script>
    // Theme toggle
    const themeToggleBtn = document.getElementById('themeToggleBtn');
    const themeIcon = document.getElementById('themeIcon');
    
    function updateThemeIcon() {
        if (document.documentElement.classList.contains('dark')) {
            themeIcon.className = 'fa-solid fa-sun';
        } else {
            themeIcon.className = 'fa-solid fa-moon';
        }
    }
    
    updateThemeIcon();

    themeToggleBtn.addEventListener('click', () => {
        if (document.documentElement.classList.contains('dark')) {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('theme', 'light');
        } else {
            document.documentElement.classList.add('dark');
            localStorage.setItem('theme', 'dark');
        }
        updateThemeIcon();
    });

    // Profile dropdown
    const userMenuBtn = document.getElementById('userMenuBtn');
    const userMenu = document.getElementById('userMenu');

    userMenuBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        userMenu.classList.toggle('hidden');
    });

    document.addEventListener('click', () => {
        userMenu.classList.add('hidden');
    });

    // Responsive & Collapsible Sidebar
    const sidebar = document.getElementById('sidebar');
    const mainWrap = document.getElementById('mainWrap');
    const topbar = document.getElementById('topbar');
    const sidebarBackdrop = document.getElementById('sidebarBackdrop');
    const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');

    function isDesktop() { 
        return window.matchMedia('(min-width: 1024px)').matches; 
    }

    sidebarToggleBtn.addEventListener('click', () => {
        if (isDesktop()) {
            const collapsed = sidebar.classList.toggle('icon-only');
            sidebar.classList.toggle('w-64', !collapsed);
            sidebar.classList.toggle('w-20', collapsed);
            mainWrap.classList.toggle('lg:ml-64', !collapsed);
            mainWrap.classList.toggle('lg:ml-20', collapsed);
            topbar.classList.toggle('lg:left-64', !collapsed);
            topbar.classList.toggle('lg:left-20', collapsed);
        } else {
            sidebar.classList.toggle('-translate-x-full');
            sidebar.classList.toggle('translate-x-0');
            sidebarBackdrop.classList.toggle('hidden');
        }
    });

    sidebarBackdrop.addEventListener('click', () => {
        sidebar.classList.add('-translate-x-full');
        sidebar.classList.remove('translate-x-0');
        sidebarBackdrop.classList.add('hidden');
    });
</script>
</body>
</html>