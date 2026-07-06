<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SGCart Seller Portal')</title>

    <link rel="icon" type="image/svg+xml" href='data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="%233b82f6" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>'>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Select2 Searchable Dropdown Styles -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Flatpickr Datepicker Styles -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" crossorigin="anonymous">

    <!-- Tailwind & Admin styles compiled by Vite -->
    @vite(['resources/css/app.css', 'resources/css/admin.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }
        .font-display { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }

        /* Flatpickr Theme Customizations to match SGCart (Light/Dark mode) */
        .flatpickr-calendar {
            background: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05) !important;
            font-family: inherit !important;
            border-radius: 8px !important;
            width: 236px !important;
            padding: 6px !important;
            height: auto !important;
            max-height: none !important;
        }
        .flatpickr-calendar.multiMonth {
            width: 460px !important;
        }
        .flatpickr-calendar.has-sidebar {
            width: 336px !important;
        }
        .flatpickr-calendar.has-sidebar.multiMonth {
            width: 560px !important;
        }
        .dark .flatpickr-calendar {
            background: #0f172a !important; /* slate-900 */
            border-color: #1e293b !important; /* slate-800 */
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.4), 0 10px 10px -5px rgba(0, 0, 0, 0.3) !important;
        }
        
        /* Compact Header */
        .flatpickr-months {
            padding: 4px 0 !important;
        }
        .flatpickr-calendar.has-sidebar .flatpickr-months {
            margin-left: 100px !important;
            width: 224px !important;
        }
        .flatpickr-calendar.has-sidebar.multiMonth .flatpickr-months {
            margin-left: 100px !important;
            width: 448px !important;
        }
        .flatpickr-months .flatpickr-month {
            height: 24px !important;
            color: #0f172a !important;
        }
        .dark .flatpickr-months .flatpickr-month {
            color: #f8fafc !important;
        }
        .flatpickr-current-month {
            font-size: 12px !important;
            font-weight: 600 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 4px !important;
            color: #1e293b !important;
            padding: 0 !important;
            height: auto !important;
        }
        .dark .flatpickr-current-month {
            color: #f8fafc !important;
        }
        .flatpickr-current-month .flatpickr-monthDropdown-months,
        .flatpickr-current-month .flatpickr-yearDropdown-years {
            font-weight: 700 !important;
            font-size: 11px !important;
            padding: 2px 6px !important;
            border-radius: 4px !important;
            border: 1px solid #cbd5e1 !important;
            background: #f1f5f9 !important;
            color: #1e293b !important;
            cursor: pointer !important;
            outline: none !important;
            transition: all 0.15s ease-in-out !important;
            height: 24px !important;
            line-height: normal !important;
            display: inline-block !important;
        }
        .dark .flatpickr-current-month .flatpickr-monthDropdown-months,
        .dark .flatpickr-current-month .flatpickr-yearDropdown-years {
            background: #1e293b !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }
        .flatpickr-current-month .flatpickr-monthDropdown-months:hover,
        .flatpickr-current-month .flatpickr-yearDropdown-years:hover {
            background: #e2e8f0 !important;
            border-color: #cbd5e1 !important;
        }
        .dark .flatpickr-current-month .flatpickr-monthDropdown-months:hover,
        .dark .flatpickr-current-month .flatpickr-yearDropdown-years:hover {
            background: #334155 !important;
            border-color: #475569 !important;
        }
        .flatpickr-monthDropdown-months .flatpickr-monthDropdown-month {
            background-color: #ffffff !important;
            color: #0f172a !important;
        }
        .dark .flatpickr-monthDropdown-months .flatpickr-monthDropdown-month {
            background-color: #0f172a !important;
            color: #f8fafc !important;
        }
        .flatpickr-current-month .numInputWrapper {
            width: 52px !important;
            border-radius: 6px !important;
            transition: all 0.15s ease-in-out !important;
        }
        .flatpickr-current-month .numInputWrapper:hover {
            background: #f1f5f9 !important;
        }
        .dark .flatpickr-current-month .numInputWrapper:hover {
            background: #1e293b !important;
        }
        .flatpickr-current-month input.numInput.cur-year {
            font-weight: 700 !important;
            font-size: 12px !important;
            color: inherit !important;
            background: transparent !important;
            border: none !important;
            padding: 2px 4px !important;
            border-radius: 6px !important;
            outline: none !important;
            text-align: center !important;
            width: 100% !important;
        }
        .flatpickr-current-month .numInputWrapper span {
            display: none !important;
        }
        .flatpickr-months .flatpickr-prev-month, 
        .flatpickr-months .flatpickr-next-month {
            height: 24px !important;
            padding: 2px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 6px !important;
            color: #475569 !important;
            fill: #475569 !important;
        }
        .dark .flatpickr-months .flatpickr-prev-month, 
        .dark .flatpickr-months .flatpickr-next-month {
            color: #cbd5e1 !important;
            fill: #cbd5e1 !important;
        }
        .flatpickr-months .flatpickr-prev-month:hover, 
        .flatpickr-months .flatpickr-next-month:hover {
            background: #f1f5f9 !important;
        }
        .dark .flatpickr-months .flatpickr-prev-month:hover, 
        .dark .flatpickr-months .flatpickr-next-month:hover {
            background: #1e293b !important;
        }

        /* Compact Weekdays */
        .flatpickr-weekdays {
            height: 20px !important;
        }
        span.flatpickr-weekday {
            font-size: 10px !important;
            font-weight: 600 !important;
            color: #94a3b8 !important; /* slate-400 */
        }
        .dark span.flatpickr-weekday {
            color: #64748b !important; /* slate-500 */
        }

        /* Days Grid and Sizing */
        .flatpickr-innerContainer {
            padding-top: 4px !important;
        }
        .flatpickr-days {
            width: 224px !important;
        }
        .multiMonth .flatpickr-days {
            width: 448px !important;
        }
        .dayContainer {
            width: 224px !important;
            min-width: 224px !important;
            max-width: 224px !important;
            gap: 1px 0 !important;
        }
        .flatpickr-day {
            height: 28px !important;
            line-height: 28px !important;
            max-width: 32px !important;
            flex-basis: 32px !important;
            font-size: 11px !important;
            font-weight: 500 !important;
            border-radius: 4px !important;
            margin: 0 !important;
            color: #334155 !important;
            border: none !important;
        }
        .dark .flatpickr-day {
            color: #cbd5e1 !important;
        }

        /* Selection states - range start, end, in-between */
        .flatpickr-day.selected, 
        .flatpickr-day.startRange, 
        .flatpickr-day.endRange {
            background: #2563eb !important; /* blue-600 */
            color: #ffffff !important;
            border-radius: 4px !important;
            box-shadow: none !important;
        }
        .dark .flatpickr-day.selected, 
        .dark .flatpickr-day.startRange, 
        .dark .flatpickr-day.endRange {
            background: #3b82f6 !important; /* blue-500 */
            color: #ffffff !important;
            box-shadow: none !important;
        }

        .flatpickr-day.startRange {
            border-radius: 4px 0 0 4px !important;
            box-shadow: 4px 0 0 #eff6ff !important;
        }
        .dark .flatpickr-day.startRange {
            box-shadow: 4px 0 0 rgba(30, 58, 138, 0.2) !important;
        }
        
        .flatpickr-day.endRange {
            border-radius: 0 4px 4px 0 !important;
            box-shadow: -4px 0 0 #eff6ff !important;
        }
        .dark .flatpickr-day.endRange {
            box-shadow: -4px 0 0 rgba(30, 58, 138, 0.2) !important;
        }

        /* Subtle in-range highlight background styling */
        .flatpickr-day.inRange {
            background: #eff6ff !important; /* blue-50 */
            box-shadow: -4px 0 0 #eff6ff, 4px 0 0 #eff6ff !important;
            color: #1e40af !important; /* blue-800 */
            border-radius: 0 !important;
        }
        .dark .flatpickr-day.inRange {
            background: rgba(30, 58, 138, 0.2) !important; /* blue-900 / 20% opacity */
            box-shadow: -4px 0 0 rgba(30, 58, 138, 0.2), 4px 0 0 rgba(30, 58, 138, 0.2) !important;
            color: #93c5fd !important; /* blue-300 */
            border-radius: 0 !important;
        }
        
        /* Hover states */
        .flatpickr-day:hover, 
        .flatpickr-day.prevMonthDay:hover, 
        .flatpickr-day.nextMonthDay:hover {
            background: #f1f5f9 !important;
            color: #0f172a !important;
        }
        .dark .flatpickr-day:hover, 
        .dark .flatpickr-day.prevMonthDay:hover, 
        .dark .flatpickr-day.nextMonthDay:hover {
            background: #1e293b !important;
            color: #f8fafc !important;
        }
        
        /* Disabled & Today */
        .flatpickr-day.today {
            border: 1px solid #2563eb !important;
        }
        .dark .flatpickr-day.today {
            border: 1px solid #3b82f6 !important;
        }
        .flatpickr-day.flatpickr-disabled, 
        .flatpickr-day.flatpickr-disabled:hover {
            color: #cbd5e1 !important;
            background: transparent !important;
        }
        .dark .flatpickr-day.flatpickr-disabled, 
        .dark .flatpickr-day.flatpickr-disabled:hover {
            color: #475569 !important;
            background: transparent !important;
        }
        .flatpickr-day.prevMonthDay, 
        .flatpickr-day.nextMonthDay {
            color: #94a3b8 !important;
            opacity: 0.5;
        }
        .dark .flatpickr-day.prevMonthDay, 
        .dark .flatpickr-day.nextMonthDay {
            color: #475569 !important;
        }

        /* Flatpickr Presets Sidebar styling */
        .flatpickr-calendar-sidebar {
            width: 100px;
            border-right: 1px solid #e2e8f0;
            padding: 6px 3px;
            display: flex;
            flex-direction: column;
            gap: 1px;
            background: #f8fafc;
            border-radius: 0 0 0 8px;
            box-sizing: border-box;
        }
        .dark .flatpickr-calendar-sidebar {
            border-color: #1e293b;
            background: #0f172a;
        }
        .flatpickr-sidebar-btn {
            background: transparent;
            border: none;
            text-align: left;
            padding: 5px 6px;
            font-size: 10px;
            font-weight: 600;
            color: #475569;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.15s ease-in-out;
            width: 100%;
        }
        .dark .flatpickr-sidebar-btn {
            color: #94a3b8;
        }
        .flatpickr-sidebar-btn:hover {
            background: #eff6ff;
            color: #2563eb;
        }
        .dark .flatpickr-sidebar-btn:hover {
            background: #1e3a8a33;
            color: #3b82f6;
        }
        .flatpickr-sidebar-btn.active {
            background: #2563eb;
            color: #ffffff;
        }
        .dark .flatpickr-sidebar-btn.active {
            background: #3b82f6;
            color: #ffffff;
        }

        /* Mobile Responsive Overrides */
        @media (max-width: 480px) {
            .flatpickr-calendar.has-sidebar {
                width: 236px !important;
            }
            .flatpickr-calendar.has-sidebar .flatpickr-innerContainer {
                flex-direction: column !important;
            }
            .flatpickr-calendar-sidebar {
                width: 100% !important;
                border-right: none !important;
                border-bottom: 1px solid #e2e8f0;
                flex-direction: row !important;
                overflow-x: auto !important;
                padding: 4px 6px !important;
                border-radius: 8px 8px 0 0 !important;
                gap: 4px !important;
            }
            .dark .flatpickr-calendar-sidebar {
                border-bottom-color: #1e293b;
            }
            .flatpickr-sidebar-btn {
                width: auto !important;
                white-space: nowrap !important;
                padding: 4px 8px !important;
            }
            .flatpickr-calendar.has-sidebar .flatpickr-months {
                margin-left: 0 !important;
                width: 224px !important;
            }
            .flatpickr-calendar-footer {
                flex-direction: column !important;
                align-items: center !important;
                gap: 6px !important;
                padding: 6px 8px !important;
            }
            .flatpickr-footer-range {
                text-align: center !important;
                width: 100% !important;
            }
            .flatpickr-footer-actions {
                width: 100% !important;
                justify-content: center !important;
            }
        }

        /* Flatpickr Footer Panel */
        .flatpickr-calendar-footer {
            border-top: 1px solid #e2e8f0;
            padding: 6px 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #ffffff;
            border-radius: 0 0 8px 8px;
            box-sizing: border-box;
            width: 100% !important;
        }
        .dark .flatpickr-calendar-footer {
            border-color: #1e293b;
            background: #0f172a;
        }
        .flatpickr-footer-range {
            font-size: 10px;
            font-weight: 600;
            color: #475569;
        }
        .dark .flatpickr-footer-range {
            color: #cbd5e1;
        }
        .flatpickr-footer-actions {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .flatpickr-footer-btn-cancel,
        .flatpickr-footer-btn-apply {
            border: none;
            padding: 4px 10px;
            font-size: 10px;
            font-weight: 650;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.15s ease-in-out;
        }
        .flatpickr-footer-btn-cancel {
            background: #f1f5f9;
            color: #475569;
        }
        .dark .flatpickr-footer-btn-cancel {
            background: #1e293b;
            color: #cbd5e1;
        }
        .flatpickr-footer-btn-cancel:hover {
            background: #e2e8f0;
        }
        .dark .flatpickr-footer-btn-cancel:hover {
            background: #334155;
        }
        .flatpickr-footer-btn-apply {
            background: #2563eb;
            color: #ffffff;
        }
        .dark .flatpickr-footer-btn-apply {
            background: #3b82f6;
            color: #ffffff;
        }
        .flatpickr-footer-btn-apply:hover {
            background: #1d4ed8;
        }
        .dark .flatpickr-footer-btn-apply:hover {
            background: #2563eb;
        }

        /* Custom Time Row containing Start & End Pickers */
        .flatpickr-custom-time-row {
            border-top: 1px solid #e2e8f0;
            padding: 6px 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #ffffff;
            box-sizing: border-box;
            width: 100% !important;
        }
        .dark .flatpickr-custom-time-row {
            border-color: #1e293b;
            background: #0f172a;
        }
        .flatpickr-calendar.has-sidebar .flatpickr-custom-time-row {
            margin-left: 100px !important;
            width: 448px !important;
        }
        .flatpickr-calendar.has-sidebar:not(.multiMonth) .flatpickr-custom-time-row {
            margin-left: 100px !important;
            width: 224px !important;
        }
        @media (max-width: 480px) {
            .flatpickr-calendar.has-sidebar .flatpickr-custom-time-row,
            .flatpickr-calendar.has-sidebar:not(.multiMonth) .flatpickr-custom-time-row {
                margin-left: 0 !important;
                width: 224px !important;
                flex-direction: column !important;
                align-items: center !important;
                gap: 6px !important;
                padding: 8px 6px !important;
            }
        }

        .flatpickr-custom-time-picker {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .flatpickr-custom-time-label {
            font-size: 10px;
            font-weight: 700;
            color: #475569;
            display: flex;
            align-items: center;
            margin-right: 4px;
        }
        .dark .flatpickr-custom-time-label {
            color: #94a3b8;
        }

        .flatpickr-custom-time-picker select {
            font-size: 11px !important;
            font-weight: 700 !important;
            color: #1e293b !important;
            background: #f1f5f9 !important;
            border-radius: 4px !important;
            border: 1px solid #cbd5e1 !important;
            padding: 2px 4px !important;
            cursor: pointer !important;
            outline: none !important;
            height: 24px !important;
            line-height: normal !important;
            -webkit-appearance: select !important;
            -moz-appearance: select !important;
            appearance: select !important;
        }
        .dark .flatpickr-custom-time-picker select {
            color: #f8fafc !important;
            background: #1e293b !important;
            border-color: #334155 !important;
        }
        .flatpickr-custom-time-picker select:focus {
            border-color: #2563eb !important;
            background: #ffffff !important;
        }
        .dark .flatpickr-custom-time-picker select:focus {
            border-color: #3b82f6 !important;
            background: #0f172a !important;
        }
        .flatpickr-custom-separator {
            font-size: 12px !important;
            font-weight: 700 !important;
            color: #64748b !important;
            padding: 0 2px !important;
        }
        .dark .flatpickr-custom-separator {
            color: #94a3b8 !important;
        }

        /* Hide Flatpickr's native time picker container since we use our custom row */
        .flatpickr-time {
            display: none !important;
        }

        /* Dynamic width for the date picker wrapper to save header space when empty */
        .date-picker-wrapper {
            width: 175px !important;
            transition: width 0.2s ease-in-out !important;
        }
        .date-picker-wrapper:has(input:not(:placeholder-shown)) {
            width: 285px !important;
        }
        @media (max-width: 480px) {
            .date-picker-wrapper {
                width: 100% !important;
            }
        }
    </style>
    
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

    <!-- Sidebar Wrapper -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-64 bg-slate-900 flex flex-col -translate-x-full lg:translate-x-0 transition-all duration-200 border-r border-white/10">
        <!-- Sidebar Brand -->
        <div class="h-16 flex items-center justify-between px-5 border-b border-white/10 flex-shrink-0 logo-container-admin">
            <a class="flex items-center gap-2.5 group no-underline" href="{{ auth('seller')->user()->status->value === 'pending_onboarding' ? route('seller.onboarding') : route('seller.dashboard') }}">
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-tr from-blue-500 to-indigo-600 shadow-md shadow-blue-500/20 group-hover:scale-105 transition-transform duration-200">
                    <i class="fa-solid fa-cart-shopping text-white text-[14px]"></i>
                </div>
                <span class="font-display text-xl font-bold tracking-tight text-white flex items-baseline leading-none sidebar-text">sg<span class="text-blue-400 font-extrabold">cart</span><span class="ml-1.5 text-[9px] uppercase font-bold tracking-wider text-blue-200 bg-blue-500/10 px-1.5 py-0.5 rounded-md border border-blue-500/20">Seller</span></span>
            </a>
            <!-- Close toggle button for mobile -->
            <button id="sidebarCloseBtn" class="lg:hidden w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-white hover:bg-white/10 border-none bg-transparent cursor-pointer transition-colors" aria-label="Close sidebar">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Sidebar Navigation -->
        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1" style="scroll-padding-block: 40px;">
            <p class="px-3 pt-1 pb-1 text-xs font-semibold uppercase tracking-wider text-slate-500 section-label">Main</p>
            
            @if(auth('seller')->user()->status->value === 'pending_onboarding')
            <a href="{{ route('seller.onboarding') }}" class="nav-link {{ Request::is('seller/onboarding*') ? 'active' : '' }}" data-tooltip="Onboarding">
                <i class="fa-solid fa-user-gear"></i>
                <span class="sidebar-text">Onboarding</span>
            </a>
            @else
            <a href="{{ route('seller.dashboard') }}" class="nav-link {{ Request::is('seller/dashboard*') ? 'active' : '' }}" data-tooltip="Dashboard">
                <i class="fa-solid fa-gauge-high"></i>
                <span class="sidebar-text">Dashboard</span>
            </a>
            @endif

            @if(auth('seller')->user()->status->value === 'approved')
            <p class="px-3 pt-4 pb-1 text-xs font-semibold uppercase tracking-wider text-slate-500 section-label">Sales</p>

            <a href="{{ route('seller.orders.index') }}" class="nav-link {{ Request::is('seller/orders*') ? 'active' : '' }}" data-tooltip="Orders">
                <i class="fa-solid fa-receipt"></i>
                <span class="sidebar-text">Orders</span>
            </a>

            <a href="{{ route('seller.commissions') }}" class="nav-link {{ Request::is('seller/commissions*') ? 'active' : '' }}" data-tooltip="Earnings">
                <i class="fa-solid fa-wallet"></i>
                <span class="sidebar-text">My Earnings</span>
            </a>

            <a href="{{ route('seller.payouts') }}" class="nav-link {{ Request::is('seller/payouts*') ? 'active' : '' }}" data-tooltip="Payouts">
                <i class="fa-solid fa-money-bill-transfer"></i>
                <span class="sidebar-text">Payout History</span>
            </a>

            <a href="{{ route('seller.account-details') }}" class="nav-link {{ Request::is('seller/account-details*') ? 'active' : '' }}" data-tooltip="Payment Account">
                <i class="fa-solid fa-building-columns"></i>
                <span class="sidebar-text flex items-center justify-between w-full">
                    <span>Payment Account</span>
                    @if(auth('seller')->user() && auth('seller')->user()->account_verification_status === 'unsubmitted')
                        <span class="flex h-2 w-2 relative ml-1.5 flex-shrink-0">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-500" title="Setup Required"></span>
                        </span>
                    @endif
                </span>
            </a>

            <p class="px-3 pt-4 pb-1 text-xs font-semibold uppercase tracking-wider text-slate-500 section-label">Catalogue</p>

            <a href="{{ route('seller.products.index') }}" class="nav-link {{ Request::is('seller/products*') ? 'active' : '' }}" data-tooltip="Products">
                <i class="fa-solid fa-box-open"></i>
                <span class="sidebar-text">My Products</span>
            </a>

            @if(class_exists(\SGCart\ProductVariants\Http\Controllers\VariantController::class))
            <a href="{{ route('seller.colors.index') }}" class="nav-link {{ Request::is('seller/colors*') ? 'active' : '' }}" data-tooltip="Colors">
                <i class="fa-solid fa-palette"></i>
                <span class="sidebar-text">Colors</span>
            </a>

            <a href="{{ route('seller.sizes.index') }}" class="nav-link {{ Request::is('seller/sizes*') ? 'active' : '' }}" data-tooltip="Sizes">
                <i class="fa-solid fa-ruler-combined"></i>
                <span class="sidebar-text">Sizes</span>
            </a>
            @endif
            @endif
        </nav>
    </aside>

    <!-- Sidebar Backdrop for Mobile view -->
    <div id="sidebarBackdrop" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-30 hidden transition-all duration-200"></div>

    <!-- ============ Main wrap ============ -->
    <div id="mainWrap" class="flex-1 min-w-0 transition-all duration-200 lg:ml-64">
        
        <!-- Topbar -->
        <header id="topbar" class="fixed top-0 right-0 left-0 lg:left-64 h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center gap-3 px-4 lg:px-6 z-20 transition-all duration-200">
            <button id="sidebarToggleBtn" class="w-10 h-10 rounded-lg flex items-center justify-center text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 flex-shrink-0 border-none outline-none focus:outline-none focus:ring-0">
                <i class="fa-solid fa-bars"></i>
            </button>
            
            <div class="flex items-center gap-2 ml-auto">
                <!-- Theme Toggle -->
                <button id="themeToggleBtn" class="w-10 h-10 rounded-lg flex items-center justify-center text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 border-none outline-none focus:outline-none focus:ring-0">
                    <i class="fa-solid fa-moon" id="themeIcon"></i>
                </button>

                <!-- Dynamic Notification Dropdown -->
                <x-notification-dropdown />

                <!-- User Dropdown Menu -->
                <div class="relative">
                    <button id="userMenuBtn" class="flex items-center gap-2 pl-1 pr-2 py-1 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 border-none outline-none focus:outline-none focus:ring-0">
                        <div class="w-8 h-8 rounded-full bg-blue-500/10 text-blue-400 flex items-center justify-center font-bold text-xs uppercase">
                            {{ substr(auth('seller')->user()->name, 0, 2) }}
                        </div>
                        <div class="hidden sm:block text-left text-xs font-semibold">
                            <p class="leading-tight truncate max-w-28 text-slate-800 dark:text-slate-100">{{ auth('seller')->user()->name }}</p>
                            <p class="text-[10px] text-slate-400 mt-0.5 truncate max-w-28">{{ auth('seller')->user()->shop_name }}</p>
                        </div>
                        <i class="fa-solid fa-chevron-down text-xs text-slate-400"></i>
                    </button>
                    <div id="userMenu" class="hidden absolute right-0 mt-2 w-48 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg shadow-lg py-1 text-sm z-30">
                        @if(auth('seller')->user()->status->value === 'pending_onboarding')
                        <a href="{{ route('seller.onboarding') }}" class="block px-3 py-2 text-slate-700 dark:text-slate-350 hover:bg-slate-50 dark:hover:bg-slate-700"><i class="fa-solid fa-user-gear mr-2 w-4"></i>Onboarding</a>
                        @else
                        <a href="{{ route('seller.dashboard') }}" class="block px-3 py-2 text-slate-700 dark:text-slate-350 hover:bg-slate-55 dark:hover:bg-slate-700"><i class="fa-solid fa-chart-pie mr-2 w-4"></i>Dashboard</a>
                        @endif
                        @if(auth('seller')->user()->status->value === 'approved')
                        <a href="{{ route('seller.account-details') }}" class="block px-3 py-2 text-slate-700 dark:text-slate-350 hover:bg-slate-55 dark:hover:bg-slate-700"><i class="fa-solid fa-building-columns mr-2 w-4"></i>Payment Account</a>
                        <a href="{{ route('seller.products.index') }}" class="block px-3 py-2 text-slate-700 dark:text-slate-350 hover:bg-slate-55 dark:hover:bg-slate-700"><i class="fa-solid fa-box-open mr-2 w-4"></i>My Products</a>
                        @endif
                        <hr class="my-1 border-slate-200 dark:border-slate-700">
                        <form action="{{ route('seller.logout') }}" method="POST" id="logoutForm">
                            @csrf
                            <button type="submit" class="w-full text-left block px-3 py-2 hover:bg-slate-50 dark:hover:bg-slate-700 text-rose-500 cursor-pointer border-none bg-transparent font-medium">
                                <i class="fa-solid fa-right-from-bracket mr-2 w-4"></i>Sign out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Dynamic Page Content -->
        <main class="pt-20 px-4 lg:px-6 pb-10 w-full">


            @yield('content')
        </main>
    </div>

</div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Flatpickr Datepicker Script -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr" crossorigin="anonymous"></script>
    <script>
        // Collapsible Sidebar & Mobile Actions
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

        // Theme toggle logic aligned with admin
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

        // Global Confirmation Modal
        window.showConfirm = function(text, callback, title='Confirm Action') {
            const titleLower = title.toLowerCase();
            const isDelete = titleLower.includes('delete');
            const isRemove = titleLower.includes('remove');
            const isDangerous = isDelete || isRemove;

            const confirmBtnClass = isDangerous
                ? 'bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white shadow-lg shadow-rose-600/10 border-none'
                : 'btn btn-primary';
            const confirmText = isDelete ? 'Delete' : 'Confirm';

            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm';
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

        // Global Toast Handler
        window.showToast = function(text, type='success') {
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

        // User dropdown trigger
        const userMenuBtn = document.getElementById('userMenuBtn');
        const userMenu = document.getElementById('userMenu');

        userMenuBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            userMenu.classList.toggle('hidden');
        });

        document.addEventListener('click', () => {
            userMenu.classList.add('hidden');
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
                'success': {
                    tooltip: 'bg-gradient-to-br from-emerald-600 to-teal-700 text-white border border-emerald-500/30 shadow-lg shadow-emerald-500/10',
                    arrowColor: {
                        'right': 'border-r-teal-700',
                        'top': 'border-t-teal-700'
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
            @if($errors->any())
                showToast("{!! addslashes($errors->first()) !!}", 'error');
            @endif
        });
    </script>
    <div class="toast-wrap" id="toastWrap"></div>
    <x-full-page-loader :text="$__env->yieldContent('loader_text', 'Updating...')" />
    @stack('scripts')
</body>
</html>
