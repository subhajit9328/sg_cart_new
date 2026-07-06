<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SGCart Admin')</title>

    <link rel="icon" type="image/svg+xml" href='data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="%233b82f6" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>'>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Select2 Searchable Dropdown Styles -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Tailwind compiled by Vite -->
    @vite(['resources/css/app.css', 'resources/css/admin.css', 'resources/js/app.js'])

    @stack('styles')

    <script>
        // Check dark mode preference on load (default to light)
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>
<body class="admin-body bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-200">

<div class="flex min-h-screen" id="appShell">

    <!-- ============ Sidebar ============ -->
    <aside id="sidebar" class="fixed group inset-y-0 left-0 z-40 w-64 bg-slate-900 flex flex-col -translate-x-full lg:translate-x-0 transition-all duration-200 border-r border-white/10">
        <div class="h-16 flex items-center justify-between px-5 border-b border-white/10 flex-shrink-0 logo-container-admin">
            <a class="logo-admin" href="{{ route('admin.dashboard') }}">
                <i class="fa-solid fa-cart-shopping logo-icon"></i>
                <span class="brand-text">sgcart</span>
            </a>
            <!-- Close toggle button for mobile -->
            <button id="sidebarCloseBtn" class="lg:hidden w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-white hover:bg-white/10 border-none bg-transparent cursor-pointer transition-colors" aria-label="Close sidebar">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1" style="scroll-padding-block: 40px;">
            <p class="px-3 pt-1 pb-1 text-xs font-semibold uppercase tracking-wider text-slate-500 section-label">Main</p>
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ Route::is('admin.dashboard') ? 'active' : '' }}" data-tooltip="Dashboard">
                <i class="fa-solid fa-gauge-high"></i>
                <span class="sidebar-text">Dashboard</span>
            </a>

            @canany(['manage products', 'manage categories', 'manage manufacturers'])
            <p class="px-3 pt-4 pb-1 text-xs font-semibold uppercase tracking-wider text-slate-500 section-label">Catalogue</p>

            @can('manage products')
            <a href="{{ route('admin.products.index') }}" class="nav-link {{ Request::is('admin/products*') ? 'active' : '' }}" data-tooltip="Products">
                <i class="fa-solid fa-box-open"></i>
                <span class="sidebar-text">Products</span>
            </a>
            @endcan

            @can('manage products')
            <a href="{{ route('admin.categories.index') }}" class="nav-link {{ Request::is('admin/categories*') ? 'active' : '' }}" data-tooltip="Categories">
                <i class="fa-solid fa-tags"></i>
                <span class="sidebar-text">Categories</span>
            </a>
            @endcan

            @can('manage products')
            <a href="{{ route('admin.manufacturers.index') }}" class="nav-link {{ Request::is('admin/manufacturers*') ? 'active' : '' }}" data-tooltip="Manufacturers">
                <i class="fa-solid fa-industry"></i>
                <span class="sidebar-text">Manufacturers</span>
            </a>
            @endcan
            @endcanany

            @can('manage products')
            <p class="px-3 pt-4 pb-1 text-xs font-semibold uppercase tracking-wider text-slate-500 section-label">Sales</p>
            <a href="{{ route('admin.orders.index') }}" class="nav-link {{ Request::is('admin/orders*') ? 'active' : '' }}" data-tooltip="Orders">
                <i class="fa-solid fa-receipt"></i>
                <span class="sidebar-text">Orders</span>
            </a>
            @endcan
            @includeIf('reviews::admin-menu')
            @canany(['manage users', 'manage roles'])
            <p class="px-3 pt-4 pb-1 text-xs font-semibold uppercase tracking-wider text-slate-500 section-label">Access Control</p>

            @can('manage users')
            <a href="{{ route('admin.users.index') }}" class="nav-link {{ Request::is('admin/users*') ? 'active' : '' }}" data-tooltip="User Management">
                <i class="fa-solid fa-users-gear"></i>
                <span class="sidebar-text">User Management</span>
            </a>
            @endcan

            @can('manage roles')
            <a href="{{ route('admin.roles.index') }}" class="nav-link {{ Request::is('admin/roles*') ? 'active' : '' }}" data-tooltip="Role Management">
                <i class="fa-solid fa-shield-halved"></i>
                <span class="sidebar-text">Role Management</span>
            </a>
            @endcan
            @endcanany

            @includeIf('coupons::admin-menu')
            @includeIf('hero::admin-menu')
            @includeIf('reporting::admin-menu')
            @includeIf('product-variants::admin-menu')
            @includeIf('shipping::admin-menu')
            @includeIf('logistic-tracking::admin-menu')
            @includeIf('tax::admin-menu')
            @can('manage payments')
            <a href="{{ route('admin.payments.settings') }}" class="nav-link {{ Request::is('admin/payments*') ? 'active' : '' }}" data-tooltip="Payment Gateways">
                <i class="fa-solid fa-credit-card"></i>
                <span class="sidebar-text">Payment Gateways</span>
            </a>
            @endcan
            @includeIf('image-search::admin-menu')
            @includeIf('crm-tickets::admin-menu')
        </nav>


    </aside>

    <div id="sidebarBackdrop" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-30 hidden transition-all duration-200"></div>

    <!-- ============ Main wrap ============ -->
    <div id="mainWrap" class="flex-1 min-w-0 transition-all duration-200 lg:ml-64">

        <!-- Topbar -->
        <header id="topbar" class="fixed top-0 right-0 left-0 lg:left-64 h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center gap-3 px-4 lg:px-6 z-20 transition-all duration-200">

            <button id="sidebarToggleBtn" class="w-10 h-10 rounded-lg border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 flex-shrink-0">
                <i class="fa-solid fa-bars"></i>
            </button>

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
                        <a onclick="showConfirm('Are you sure you want to sign out?', () => document.getElementById('logoutForm').submit());" class="block px-3 py-2 hover:bg-slate-50 dark:hover:bg-slate-700 text-rose-500 cursor-pointer"><i class="fa-solid fa-right-from-bracket mr-2 w-4"></i>Sign out</a>
                    </div>
                </div>
            </div>
        </header>

        <!-- ============ Content ============ -->
        <main class="pt-20 px-4 lg:px-6 pb-10 w-full">
            @yield('content')
        </main>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    // Scroll active sidebar option into view instantly on page load without animated scroll
    window.addEventListener('DOMContentLoaded', () => {
        const activeOption = document.querySelector('.nav-link.active');
        if (activeOption) {
            const container = activeOption.closest('nav');
            if (container) {
                const containerRect = container.getBoundingClientRect();
                const optionRect = activeOption.getBoundingClientRect();
                const padding = 20; // Padding to prevent option from being stuck to the extreme top/bottom
                if (optionRect.top < containerRect.top + padding) {
                    container.scrollTop -= (containerRect.top + padding - optionRect.top);
                } else if (optionRect.bottom > containerRect.bottom - padding) {
                    container.scrollTop += (optionRect.bottom - (containerRect.bottom - padding));
                }
            }
        }
    });

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
    const sidebarCloseBtn = document.getElementById('sidebarCloseBtn');

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

    function closeMobileSidebar() {
        sidebar.classList.add('-translate-x-full');
        sidebar.classList.remove('translate-x-0');
        sidebarBackdrop.classList.add('hidden');
    }

    sidebarBackdrop.addEventListener('click', closeMobileSidebar);
    if (sidebarCloseBtn) {
        sidebarCloseBtn.addEventListener('click', closeMobileSidebar);
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
    function showConfirm(text, callback, title='Confirm Action') {
        const titleLower = title.toLowerCase();
        const isDelete = titleLower.includes('delete');
        const isRemove = titleLower.includes('remove');
        const isDangerous = isDelete || isRemove;

        const confirmBtnClass = isDangerous
            ? 'bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white shadow-lg shadow-rose-600/10 border-none'
            : 'btn btn-primary';
        const confirmText = isDelete ? 'Delete' : 'Confirm';

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
                    <button class="modal-confirm ${confirmBtnClass} px-5 py-2.5 rounded-lg text-sm font-semibold cursor-pointer" style="text-transform: none;">${confirmText}</button>
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

        const confirmBtn = modal.querySelector('.modal-confirm');
        confirmBtn.addEventListener('click', () => {
            // Check if spinner is already added
            if (!confirmBtn.querySelector('.fa-spinner')) {
                const spinner = document.createElement('i');
                spinner.className = 'fa-solid fa-spinner fa-spin mr-2';
                confirmBtn.insertBefore(spinner, confirmBtn.firstChild);
            }
            confirmBtn.disabled = true;
            confirmBtn.style.pointerEvents = 'none';
            confirmBtn.style.opacity = '0.8';

            closeModal(true);
        });
    }

    // Flash Toast triggers
    $(document).ready(function() {
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
        @if($errors->any())
            showToast("{!! addslashes($errors->first()) !!}", 'error');
        @endif
    });

    // Global Form Submit Loader
    document.addEventListener('submit', (e) => {
        if (e.defaultPrevented) return;

        const form = e.target;
        // Skip logout form
        if (form.id === 'logoutForm') return;

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

    // Global jQuery Inline Validation
    $(document).ready(function() {
        // Target all POST forms (including those with method spoofing)
        const $forms = $('form').filter(function() {
            return this.id !== 'logoutForm' &&
                   (($(this).attr('method') || '').toUpperCase() === 'POST' || $(this).find('input[name="_token"]').length > 0);
        });

        // Set novalidate to prevent default HTML5 browser tooltips
        $forms.attr('novalidate', 'novalidate');

        // Helper to get descriptive name for the field
        function getFieldName($input) {
            const id = $input.attr('id');
            let labelText = '';

            // Try to find label by 'for' attribute
            if (id) {
                labelText = $(`label[for="${id}"]`).text().trim();
            }
            // Try to find closest label in parent container
            if (!labelText) {
                labelText = $input.closest('div').find('label').first().text().trim();
            }
            // Fall back to placeholder or name
            if (!labelText) {
                labelText = $input.attr('placeholder') || $input.attr('name') || 'Field';
            }

            // Clean up common label patterns
            labelText = labelText.replace(/[:*]/g, '').trim();
            if (labelText.toLowerCase().startsWith('new ')) {
                labelText = labelText.substring(4);
            }
            return labelText || 'Field';
        }

        // Helper to get or create error element
        function getErrorElement($input) {
            let name = $input.attr('name') || $input.attr('id') || 'field';
            name = name.replace(/\[\]/g, '').replace(/[^a-zA-Z0-9_-]/g, '_');

            // Locate existing or create new error sibling
            let $err = $input.siblings(`.js-error-${name}`);
            if ($err.length === 0) {
                $err = $(`<p class="js-error-${name} text-rose-500 text-xs mt-1.5 font-medium hidden"></p>`);

                // If input has a relative wrapper (e.g. password toggle), insert after the wrapper
                let $target = $input;
                if ($input.parent().hasClass('relative')) {
                    $target = $input.parent();
                }
                $target.after($err);
            }
            return $err;
        }

        // Helper to display error
        function showError($input, message) {
            const $err = getErrorElement($input);
            $err.text(message).removeClass('hidden');
            $input.addClass('border-rose-500 focus:border-rose-500 focus:ring-rose-500');
            $input.removeClass('border-slate-200 dark:border-slate-700 focus:border-blue-500 focus:ring-blue-500');

            // Hide Laravel server-side error if present
            $input.siblings('p.text-rose-500').not($err).addClass('hidden');
            if ($input.parent().hasClass('relative')) {
                $input.parent().siblings('p.text-rose-500').not($err).addClass('hidden');
            }
        }

        // Helper to clear error
        function clearError($input) {
            const $err = getErrorElement($input);
            $err.text('').addClass('hidden');
            $input.removeClass('border-rose-500 focus:border-rose-500 focus:ring-rose-500');
            $input.addClass('border-slate-200 dark:border-slate-700 focus:border-blue-500 focus:ring-blue-500');

            // Clear Laravel server-side error if present
            $input.siblings('p.text-rose-500').not($err).addClass('hidden');
            if ($input.parent().hasClass('relative')) {
                $input.parent().siblings('p.text-rose-500').not($err).addClass('hidden');
            }
        }

        // Main validation routine for a single field
        function validateField(inputElement) {
            const $input = $(inputElement);

            // Skip hidden, disabled, buttons, or CSRF/method token fields
            if ($input.is(':hidden') || $input.is(':disabled') ||
                $input.attr('type') === 'submit' || $input.attr('type') === 'button' ||
                ['/token', '_token', '_method'].includes($input.attr('name'))) {
                return true;
            }

            const type = $input.attr('type');
            const name = $input.attr('name');
            const value = $input.val();
            const isRequired = $input.prop('required') || $input.attr('required') !== undefined;
            const displayName = getFieldName($input);

            // Required field check
            if (isRequired) {
                if (type === 'checkbox' || type === 'radio') {
                    const checkedName = $input.attr('name');
                    if (checkedName) {
                        const $group = $(`input[name="${checkedName}"]`);
                        if (!$group.is(':checked')) {
                            showError($group.first(), `At least one ${displayName} is required.`);
                            return false;
                        } else {
                            clearError($group.first());
                            return true;
                        }
                    }
                } else if (!value || value.trim() === '') {
                    showError($input, `${displayName} is required.`);
                    return false;
                }
            }

            // Email format validation
            if (type === 'email' && value && value.trim() !== '') {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value.trim())) {
                    showError($input, `Please enter a valid email address.`);
                    return false;
                }
            }

            // Password minimum length validation
            if (type === 'password' && value) {
                const minLen = parseInt($input.attr('minlength') || '8');
                if (value.length < minLen) {
                    showError($input, `Password must be at least ${minLen} characters.`);
                    return false;
                }
            }

            // Password confirmation matching validation
            if (name === 'password_confirmation' || $input.attr('id') === 'password_confirmation') {
                const $pwd = $input.closest('form').find('input[type="password"]').not($input).first();
                if ($pwd.length > 0 && value !== $pwd.val()) {
                    showError($input, `Passwords do not match.`);
                    return false;
                }
            }

            // Number validation
            if (type === 'number' && value && value.trim() !== '') {
                const num = parseFloat(value);
                if (isNaN(num)) {
                    showError($input, `Please enter a valid number.`);
                    return false;
                }
                const min = $input.attr('min');
                if (min !== undefined && num < parseFloat(min)) {
                    showError($input, `Value must be at least ${min}.`);
                    return false;
                }
                const max = $input.attr('max');
                if (max !== undefined && num > parseFloat(max)) {
                    showError($input, `Value must be at most ${max}.`);
                    return false;
                }
            }

            clearError($input);
            return true;
        }

        // Validate fields inline on input, blur, or change
        $forms.on('input blur change', 'input, select, textarea', function() {
            validateField(this);
        });

        // Block form submission and scroll to error if form is invalid
        $forms.on('submit', function(e) {
            let isFormValid = true;
            let $firstInvalid = null;

            $(this).find('input, select, textarea').each(function() {
                const isValid = validateField(this);
                if (!isValid) {
                    isFormValid = false;
                    if (!$firstInvalid) {
                        $firstInvalid = $(this);
                    }
                }
            });

            if (!isFormValid) {
                e.preventDefault();
                e.stopImmediatePropagation();

                if ($firstInvalid) {
                    $('html, body').animate({
                        scrollTop: $firstInvalid.offset().top - 120
                    }, 300);
                    $firstInvalid.focus();
                }
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

    // Global Custom Tooltips Handler (Appended to body to prevent overflow clipping)
    $(document).ready(function() {
        const $tooltip = $('<div id="globalTooltip" class="fixed hidden text-[11px] font-medium leading-relaxed px-3 py-1.5 rounded-lg shadow-xl z-[10000] pointer-events-none transition-all duration-150 transform opacity-0 w-max whitespace-normal backdrop-blur-xs"></div>');
        const $arrow = $('<div class="absolute border-[5px] border-transparent"></div>');
        $tooltip.append($arrow);
        $('body').append($tooltip);

        const themes = {
            'dark': {
                tooltip: 'bg-slate-900 dark:bg-slate-950 text-slate-100 border border-slate-800/80 shadow-lg shadow-slate-950/20',
                arrowColor: {
                    'right': 'border-r-slate-900 dark:border-r-slate-950',
                    'top': 'border-t-slate-900 dark:border-t-slate-950'
                }
            },
            'light': {
                tooltip: 'bg-white text-slate-800 border border-slate-200 shadow-md',
                arrowColor: {
                    'right': 'border-r-white',
                    'top': 'border-t-white'
                }
            },
            'info': {
                tooltip: 'bg-gradient-to-br from-blue-600 to-indigo-700 text-white border border-blue-500/30 shadow-lg shadow-indigo-500/10',
                arrowColor: {
                    'right': 'border-r-indigo-700',
                    'top': 'border-t-indigo-700'
                }
            },
            'warning': {
                tooltip: 'bg-gradient-to-br from-amber-500 to-orange-600 text-white border border-amber-500/30 shadow-lg shadow-amber-500/10',
                arrowColor: {
                    'right': 'border-r-orange-600',
                    'top': 'border-t-orange-600'
                }
            },
            'error': {
                tooltip: 'bg-gradient-to-br from-rose-600 to-red-700 text-white border border-rose-500/30 shadow-lg shadow-rose-500/10',
                arrowColor: {
                    'right': 'border-r-red-700',
                    'top': 'border-t-red-700'
                }
            }
        };

        $(document).on('mouseenter', '[data-tooltip]', function() {
            // Sidebar item handling: only show when sidebar is in icon-only mode
            if ($(this).hasClass('nav-link') && !$('#sidebar').hasClass('icon-only')) {
                return;
            }

            const text = $(this).attr('data-tooltip');
            if (!text) return;

            const position = $(this).attr('data-tooltip-position') || ($(this).hasClass('nav-link') ? 'right' : 'top');
            const theme = $(this).attr('data-tooltip-theme') || 'dark';
            const customWidth = $(this).attr('data-tooltip-width');
            const rect = this.getBoundingClientRect();

            // Set text and re-append arrow
            $tooltip.text(text).append($arrow);

            // Clean up arrow classes
            $arrow.attr('class', 'absolute border-[5px] border-transparent');

            // Apply theme styling
            const themeConfig = themes[theme] || themes['dark'];
            $tooltip.attr('class', 'fixed hidden text-[11px] font-medium leading-relaxed px-3 py-1.5 rounded-lg shadow-xl z-[10000] pointer-events-none transition-all duration-150 transform opacity-0 w-max whitespace-normal backdrop-blur-xs ' + themeConfig.tooltip);

            // Apply custom width
            if (customWidth) {
                const widths = {
                    'w-48': '192px',
                    'w-56': '224px',
                    'w-64': '256px',
                    'w-72': '288px',
                    'w-80': '320px',
                    'w-96': '384px'
                };
                $tooltip.css('max-width', widths[customWidth] || '240px');
            } else {
                $tooltip.css('max-width', '240px');
            }

            let top = 0;
            let left = 0;
            let startTransform = '';
            let endTransform = '';

            // Calculate position
            if (position === 'right') {
                $arrow.addClass('right-full top-1/2 -translate-y-1/2 ' + themeConfig.arrowColor['right']);
                top = rect.top + rect.height / 2;
                left = rect.right + 10;
                startTransform = 'translateY(-50%) translateX(-6px)';
                endTransform = 'translateY(-50%) translateX(0)';
            } else { // default to 'top'
                $arrow.addClass('top-full left-1/2 -translate-x-1/2 ' + themeConfig.arrowColor['top']);
                top = rect.top - 10;
                left = rect.left + rect.width / 2;
                startTransform = 'translateY(6px) translateX(-50%)';
                endTransform = 'translateY(0) translateX(-50%)';
            }

            $tooltip.removeClass('hidden');

            // Get actual dimensions (after text is set and hidden is removed)
            const tooltipWidth = $tooltip.outerWidth();
            const tooltipHeight = $tooltip.outerHeight();

            if (position === 'top') {
                top = rect.top - tooltipHeight - 8;
                // Boundaries prevention (stay within window)
                if (left - tooltipWidth / 2 < 8) {
                    left = tooltipWidth / 2 + 8;
                } else if (left + tooltipWidth / 2 > window.innerWidth - 8) {
                    left = window.innerWidth - tooltipWidth / 2 - 8;
                }
            } else if (position === 'right') {
                // Adjust if overflowing window bounds
                if (top - tooltipHeight / 2 < 8) {
                    top = tooltipHeight / 2 + 8;
                } else if (top + tooltipHeight / 2 > window.innerHeight - 8) {
                    top = window.innerHeight - tooltipHeight / 2 - 8;
                }
            }

            $tooltip.css({
                top: top + 'px',
                left: left + 'px',
                transform: startTransform
            });

            $tooltip.off('transitionend');
            requestAnimationFrame(() => {
                $tooltip.css({
                    opacity: 1,
                    transform: endTransform
                });
            });
        });

        $(document).on('mouseleave', '[data-tooltip]', function() {
            if ($(this).hasClass('nav-link') && !$('#sidebar').hasClass('icon-only')) {
                return;
            }

            const position = $(this).attr('data-tooltip-position') || ($(this).hasClass('nav-link') ? 'right' : 'top');
            let endTransform = '';
            if (position === 'right') {
                endTransform = 'translateY(-50%) translateX(-6px)';
            } else {
                endTransform = 'translateY(6px) translateX(-50%)';
            }

            $tooltip.css({
                opacity: 0,
                transform: endTransform
            });

            $tooltip.off('transitionend').on('transitionend', function() {
                if ($tooltip.css('opacity') == '0') {
                    $tooltip.addClass('hidden');
                }
            });
        });

        // Click outside dismiss handler for mobile touch screen support
        $(document).on('click touchstart', function(e) {
            if (!$(e.target).closest('[data-tooltip]').length) {
                $tooltip.css({ opacity: 0 }).addClass('hidden');
            }
        });
    });
</script>

<form action="{{ route('logout') }}" method="POST" id="logoutForm" class="hidden">
    @csrf
</form>

<!-- TOAST -->
<div class="toast-wrap" id="toastWrap"></div>
@stack('scripts')
</body>
</html>
