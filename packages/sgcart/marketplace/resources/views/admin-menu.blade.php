<p class="px-3 pt-4 pb-1 text-xs font-semibold uppercase tracking-wider text-slate-500 section-label">Marketplace Portal</p>

<a href="{{ route('admin.sellers.index') }}" class="nav-link {{ Request::is('admin/sellers*') ? 'active' : '' }}" data-tooltip="Sellers">
    <i class="fa-solid fa-users-gear"></i>
    <span class="sidebar-text flex-1">Manage Sellers</span>
    @if(($pendingSellersCount ?? 0) > 0)
        <span class="flex h-5 w-5 items-center justify-center text-[10px] font-bold rounded-full bg-amber-500 text-slate-950 ring-2 ring-slate-900 animate-pulse-subtle">
            {{ $pendingSellersCount }}
        </span>
    @endif
</a>

<a href="{{ route('admin.products.approvals') }}" class="nav-link {{ Request::is('admin/product-approvals*') ? 'active' : '' }}" data-tooltip="Approvals">
    <i class="fa-solid fa-clipboard-check"></i>
    <span class="sidebar-text flex-1">Product Approvals</span>
    @if(($pendingProductsCount ?? 0) > 0)
        <span class="flex h-5 w-5 items-center justify-center text-[10px] font-bold rounded-full bg-rose-500 text-white ring-2 ring-slate-900 animate-pulse-subtle">
            {{ $pendingProductsCount }}
        </span>
    @endif
</a>

<a href="{{ route('admin.commissions.index') }}" class="nav-link {{ Request::is('admin/commissions*') ? 'active' : '' }}" data-tooltip="Commissions">
    <i class="fa-solid fa-wallet"></i>
    <span class="sidebar-text">Platform Revenue</span>
</a>

<a href="{{ route('admin.payouts.index') }}" class="nav-link {{ Request::is('admin/payouts*') ? 'active' : '' }}" data-tooltip="Payouts">
    <i class="fa-solid fa-money-bill-transfer"></i>
    <span class="sidebar-text">Seller Payouts</span>
</a>

@pushOnce('styles')
<style>
    @keyframes pulse-subtle {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: .85; transform: scale(1.05); }
    }
    .animate-pulse-subtle {
        animation: pulse-subtle 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
</style>
@endpushOnce
